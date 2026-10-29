<?php
require dirname(__DIR__).'/app/bootstrap.php';require_login();require dirname(__DIR__).'/app/reports.php';$module=$_GET['module']??'';
if($module==='report'){require_permission('reports.export');[$start,$end]=period();$list=report_data((string)($_GET['report']??'summary'),$start,$end);}
elseif(isset(modules()[$module])){require_permission($module.'.view');require_permission($module.'.export');$def=modules()[$module];$cols=implode(',',array_keys($def['fields']));$list=rows("SELECT id,$cols,status,created_at FROM $module WHERE business_id=? ORDER BY id",[bid()]);}
elseif(in_array($module,['invoices','quotations','payments'],true)){require_permission($module.'.view');require_permission($module.'.export');require_permission('reports.view');[$start,$end]=isset($_GET['period'])?period():['2000-01-01','2100-12-31'];$list=report_data($module,$start,$end);}
else {http_response_code(404);exit;}
audit('data.exported',$module,null,['rows'=>count($list)]);header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="ybt-'.$module.'-'.date('Y-m-d').'.csv"');$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");if($list){fputcsv($out,array_keys($list[0]),',','"','');foreach($list as $r)fputcsv($out,array_map('csv_cell',array_values($r)),',','"','');}fclose($out);
