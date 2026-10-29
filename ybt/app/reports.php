<?php
declare(strict_types=1);
function report_definitions(): array { return [
 'revenue'=>['Revenue','Issued invoice value, grouped by month.','trending-up'],
 'invoices'=>['Invoices','Your invoice register and current payment balances.','file-invoice'],
 'payments'=>['Payments','Recorded receipts and their reversal status.','credit-card'],
 'outstanding'=>['Outstanding receivables','Issued invoices with a remaining balance.','wallet'],
 'aging'=>['Overdue aging','Receivables grouped by days past their due date.','clock'],
 'expenses'=>['Expenses','Recorded expenses grouped by category.','receipt'],
 'customers'=>['Customers','Customer totals for issued invoices in this period.','users'],
 'products'=>['Product & service sales','Issued line-item sales by product or service.','package'],
 'leads'=>['Lead conversion','Opportunities, values and outcomes by stage.','target-arrow'],
 'quotations'=>['Quotation conversion','Quotation status and conversion into invoices.','file-description'],
 'activity'=>['Manager activity','Audited business operations by team member.','shield-check'],
 'summary'=>['Business summary','Authoritative invoiced revenue, receipts and cash flow.','chart-bar'],
 ]; }
function report_data(string $type,string $start,string $end): array {
    require_permission('reports.view');$b=bid();$args=[$b,$start,$end];
    switch($type){
    case 'revenue':require_permission('invoices.view');return rows("SELECT DATE_FORMAT(issue_date,'%Y-%m') month,COUNT(*) invoices,SUM(total) invoiced_revenue FROM documents WHERE business_id=? AND issue_date BETWEEN ? AND ? AND type='invoice' AND status='Issued' GROUP BY month ORDER BY month",$args);
    case 'invoices':case 'outstanding':case 'aging':
        require_permission('invoices.view');require_permission('payments.view');$docs=rows("SELECT d.number,c.name customer,d.issue_date,d.due_date,d.status,d.total,COALESCE(p.paid,0) paid,d.total-COALESCE(p.paid,0) outstanding FROM documents d JOIN customers c ON c.id=d.customer_id AND c.business_id=d.business_id LEFT JOIN (SELECT document_id,SUM(amount) paid FROM payments WHERE business_id=? AND reversed_at IS NULL GROUP BY document_id) p ON p.document_id=d.id WHERE d.business_id=? AND d.issue_date BETWEEN ? AND ? AND d.type='invoice' ORDER BY d.issue_date DESC",[$b,...$args]);if($type==='invoices'){foreach($docs as &$doc)$doc['status']=document_status($doc+['type'=>'invoice']);unset($doc);return $docs;}$docs=array_values(array_filter($docs,fn($d)=>$d['status']==='Issued'&&scaled($d['outstanding'])>0));if($type==='outstanding')return $docs;$buckets=['Not due'=>0,'1–30 days'=>0,'31–60 days'=>0,'61–90 days'=>0,'90+ days'=>0];foreach($docs as $d){$days=(int)floor((strtotime(date('Y-m-d'))-strtotime($d['due_date']))/86400);$bucket=$days<=0?'Not due':($days<=30?'1–30 days':($days<=60?'31–60 days':($days<=90?'61–90 days':'90+ days')));$buckets[$bucket]+=scaled($d['outstanding']);}return array_map(fn($k,$v)=>['aging_bucket'=>$k,'outstanding'=>decimal($v)],array_keys($buckets),$buckets);
    case 'payments':require_permission('payments.view');return rows('SELECT d.number invoice,c.name customer,p.payment_date,p.amount,p.method,p.reference,IF(p.reversed_at IS NULL,\'Recorded\',\'Reversed\') status FROM payments p JOIN documents d ON d.id=p.document_id AND d.business_id=p.business_id JOIN customers c ON c.id=p.customer_id AND c.business_id=p.business_id WHERE p.business_id=? AND p.payment_date BETWEEN ? AND ? ORDER BY p.payment_date DESC',$args);
    case 'expenses':require_permission('expenses.view');return rows("SELECT category,COUNT(*) entries,SUM(amount) recorded_expenses FROM expenses WHERE business_id=? AND expense_date BETWEEN ? AND ? AND status='Active' GROUP BY category ORDER BY recorded_expenses DESC",$args);
    case 'customers':require_permission('customers.view');require_permission('invoices.view');return rows("SELECT c.name,c.company,COUNT(d.id) invoices,COALESCE(SUM(d.total),0) billed FROM customers c LEFT JOIN documents d ON d.customer_id=c.id AND d.business_id=c.business_id AND d.type='invoice' AND d.status='Issued' AND d.issue_date BETWEEN ? AND ? WHERE c.business_id=? GROUP BY c.id ORDER BY billed DESC",[$start,$end,$b]);
    case 'products':require_permission('products.view');require_permission('invoices.view');return rows("SELECT COALESCE(p.name,i.description) item,SUM(i.quantity) quantity,SUM(i.total) billed FROM document_items i JOIN documents d ON d.id=i.document_id LEFT JOIN products p ON p.id=i.product_id AND p.business_id=d.business_id WHERE d.business_id=? AND d.issue_date BETWEEN ? AND ? AND d.type='invoice' AND d.status='Issued' GROUP BY item ORDER BY billed DESC",$args);
    case 'leads':require_permission('leads.view');return rows("SELECT stage,COUNT(*) leads,SUM(value) deal_value FROM leads WHERE business_id=? AND DATE(created_at) BETWEEN ? AND ? AND status='Active' GROUP BY stage",$args);
    case 'quotations':require_permission('quotations.view');return rows("SELECT q.status,COUNT(*) quotations,COALESCE(SUM(q.total),0) quoted_value,SUM(CASE WHEN i.id IS NOT NULL THEN 1 ELSE 0 END) converted FROM documents q LEFT JOIN documents i ON i.source_id=q.id AND i.business_id=q.business_id WHERE q.business_id=? AND q.issue_date BETWEEN ? AND ? AND q.type='quotation' GROUP BY q.status",$args);
    case 'activity':require_permission('managers.manage');return rows("SELECT COALESCE(u.name,'System') user,l.action,COUNT(*) events FROM activity_logs l LEFT JOIN users u ON u.id=l.user_id AND u.business_id=l.business_id WHERE l.business_id=? AND DATE(l.created_at) BETWEEN ? AND ? GROUP BY user,l.action ORDER BY events DESC",$args);
    case 'summary':foreach(['invoices.view','payments.view','expenses.view'] as $p)require_permission($p);$s=financial_summary($start,$end);return [['metric'=>'Invoiced revenue','amount'=>$s['revenue']],['metric'=>'Cash received','amount'=>$s['cash']],['metric'=>'Recorded expenses','amount'=>$s['expenses']],['metric'=>'Net cash flow','amount'=>number_format($s['net'],2,'.','')],['metric'=>'Outstanding for selected invoices','amount'=>$s['outstanding']]];
    default:throw new DomainException('Unknown report.');
    }
}
function csv_cell(mixed $v): string { $s=(string)($v??'');return preg_match('/^[\s]*[=+\-@\t\r]/u',$s)?"'".$s:$s; }
