<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit('CLI only.');
$options=getopt('',['email:','name:','demo']);
$email=strtolower(trim($options['email']??''));$name=trim($options['name']??'Business Owner');
if(!filter_var($email,FILTER_VALIDATE_EMAIL)){fwrite(STDERR,"Usage: php bin/install.php --email=you@example.com --name='Your Name' [--demo]\nPassword is read from standard input.\n");exit(1);}
fwrite(STDERR,"Enter an Owner password (12+ characters) via standard input: ");$password=trim(fgets(STDIN)?:'');if(strlen($password)<12){fwrite(STDERR,"Password must be at least 12 characters.\n");exit(1);}
$config=require dirname(__DIR__).'/config/config.php';
if(strlen($config['key'])!==64||!ctype_xdigit($config['key'])){fwrite(STDERR,"Configure a random 32-byte hex YBT_APP_KEY before installation.\n");exit(1);}
$pdo=new PDO($config['dsn'],$config['db_user'],$config['db_password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->exec(file_get_contents(dirname(__DIR__).'/database/schema.sql'));
if($pdo->query('SELECT COUNT(*) FROM businesses')->fetchColumn()>0){fwrite(STDERR,"Already installed. Refusing to create another Owner or reseed demo records.\n");exit(1);}
$pdo->beginTransaction();try{
$profile=['name'=>'YBT Creative Studio','address'=>"24, MG Road, Indiranagar\nBengaluru, Karnataka 560038",'email'=>'hello@example.test','phone'=>'+91 90000 00000','terms'=>'Payment is due within 30 days. Thank you for your business.','payment_instructions'=>'Please include the invoice number with your payment.','currency'=>'INR','timezone'=>'Asia/Kolkata'];
$pdo->prepare('INSERT INTO businesses(name,profile,demo_mode) VALUES(?,?,?)')->execute([isset($options['demo'])?'YBT Creative Studio':'My Business',json_encode($profile),isset($options['demo'])?1:0]);$businessId=(int)$pdo->lastInsertId();
$pdo->prepare("INSERT INTO users(business_id,name,email,password_hash,role,permissions) VALUES(?,?,?,?,'owner','[]')")->execute([$businessId,$name,$email,password_hash($password,PASSWORD_DEFAULT)]);$ownerId=(int)$pdo->lastInsertId();
if(isset($options['demo']))require __DIR__.'/seed.php';
$pdo->commit();fwrite(STDOUT,"Installation complete. Owner provisioned; demo data ".(isset($options['demo'])?'loaded':'disabled').". Sign in using the supplied details.\n");
}catch(Throwable $e){$pdo->rollBack();throw $e;}
