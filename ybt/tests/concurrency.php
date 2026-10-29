<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/backup.php';
restore_exception_handler();

// A second connection commits between snapshot reads. Only this synthetic business is changed.
$writer=new PDO($config['dsn'],$config['db_user'],$config['db_password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$passed=0;$businessId=null;
function assert_concurrency(bool $ok,string $name): void {
    if(!$ok)throw new RuntimeException($name);
    global $passed;$passed++;echo "PASS $name\n";
}
class BackupReadStatement extends PDOStatement {
    protected function __construct(private Closure $afterRead) {}
    public function execute(?array $params=null): bool {
        $result=parent::execute($params);($this->afterRead)($this->queryString);return $result;
    }
}
try {
    query("INSERT INTO businesses(name,profile,demo_mode) VALUES('Snapshot test','{}',0)");$businessId=(int)db()->lastInsertId();
    query("INSERT INTO users(business_id,name,email,password_hash,role,permissions) VALUES(?,'Test Owner',?,?,'owner','[]')",[$businessId,'concurrency-'.bin2hex(random_bytes(8)).'@example.test',password_hash(bin2hex(random_bytes(20)),PASSWORD_DEFAULT)]);
    $_SESSION['user_id']=(int)db()->lastInsertId();$_SESSION['last_activity']=time();$_SESSION['auth_version']=hash('sha256',current_user()['password_hash']);
    $customer=save_record('customers',['name'=>'Snapshot customer','address'=>'','notes'=>'']);
    save_setting('invoice_sequence',1000);
    // MariaDB 11.8 snapshot-isolation mode rejects stale locking reads outright.
    // Exercise conventional MySQL/InnoDB current-read semantics for this regression.
    $snapshotMode=one("SHOW VARIABLES LIKE 'innodb_snapshot_isolation'");
    if($snapshotMode)db()->exec('SET SESSION innodb_snapshot_isolation=OFF');
    db()->beginTransaction();
    assert_concurrency(setting('invoice_sequence')===1000,'Establish the original repeatable-read snapshot');
    $writer->prepare("UPDATE business_settings SET value='1001' WHERE business_id=? AND setting_key='invoice_sequence'")->execute([$businessId]);
    $id=create_document('invoice',['customer_id'=>$customer,'issue_date'=>date('Y-m-d'),'due_date'=>date('Y-m-d')],[['description'=>'Snapshot item','quantity'=>'1','rate'=>'10']]);
    assert_concurrency(document_record($id)['number']==='INV-1002','Sequence allocation sees the latest committed value despite an older snapshot');
    db()->commit();
    if($snapshotMode)db()->exec('SET SESSION innodb_snapshot_isolation='.($snapshotMode['Value']==='ON'?'ON':'OFF'));
    assert_concurrency(setting('invoice_sequence')===1002,'Sequence increment persisted');

    $changed=false;
    db()->setAttribute(PDO::ATTR_STATEMENT_CLASS,[BackupReadStatement::class,[function(string $sql)use(&$changed,$writer,$businessId,$id):void {
        if($changed||$sql!=='SELECT * FROM documents WHERE business_id=?')return;
        $changed=true;$writer->beginTransaction();
        $writer->prepare('UPDATE documents SET subtotal=20,total=20 WHERE id=?')->execute([$id]);
        $writer->prepare('UPDATE document_items SET rate=20,subtotal=20,total=20 WHERE document_id=?')->execute([$id]);
        $writer->prepare("UPDATE businesses SET name='After snapshot' WHERE id=?")->execute([$businessId]);
        $writer->commit();
    }]]);
    $data=backup_data();
    assert_concurrency($changed,'Concurrent document and line-item update committed during backup');
    assert_concurrency($data['tables']['documents'][0]['total']==='10.00'&&$data['tables']['document_items'][0]['total']==='10.00','Backup document and item totals come from one snapshot');
    assert_concurrency($data['business']['name']==='Snapshot test','Business profile is read in the same snapshot');
    assert_concurrency(!db()->inTransaction(),'Standalone backup commits its transaction');
    db()->setAttribute(PDO::ATTR_STATEMENT_CLASS,[PDOStatement::class]);
    assert_concurrency(document_record($id)['total']==='20.00','Subsequent reads see the concurrent commit');
    // The business() cache may predate a profile edit; backups must read the database snapshot.
    business();query("UPDATE businesses SET name='Fresh profile' WHERE id=?",[$businessId]);
    assert_concurrency(backup_data()['business']['name']==='Fresh profile','Backup does not reuse a stale cached profile');

    db()->setAttribute(PDO::ATTR_STATEMENT_CLASS,[BackupReadStatement::class,[function(string $sql):void {
        if($sql==='SELECT * FROM documents WHERE business_id=?')throw new RuntimeException('Simulated backup read failure');
    }]]);
    try {backup_data();throw new LogicException('Expected the read failure');}
    catch(RuntimeException $e){assert_concurrency($e->getMessage()==='Simulated backup read failure','Backup read failure is propagated');}
    assert_concurrency(!db()->inTransaction(),'Failed standalone backup rolls back its transaction');
    echo "$passed concurrency checks passed.\n";
} finally {
    db()->setAttribute(PDO::ATTR_STATEMENT_CLASS,[PDOStatement::class]);
    if(db()->inTransaction())db()->rollBack();if($writer->inTransaction())$writer->rollBack();
    if($businessId!==null){
        query('DELETE i FROM document_items i JOIN documents d ON d.id=i.document_id WHERE d.business_id=?',[$businessId]);
        foreach(['documents','business_settings','activity_logs','customers','users','businesses'] as $table){
            query("DELETE FROM $table WHERE ".($table==='businesses'?'id':'business_id').'=?',[$businessId]);
        }
    }
}
