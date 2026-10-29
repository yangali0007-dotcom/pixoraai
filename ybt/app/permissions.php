<?php
declare(strict_types=1);
function permission_options(): array {return [
'dashboard'=>['view'], 'customers'=>['view','create','edit','archive','export'], 'leads'=>['view','create','edit','archive','export'], 'products'=>['view','create','edit','archive','export'], 'tasks'=>['view','create','edit','complete','archive','export'], 'quotations'=>['view','create','issue','approve','print','export'], 'invoices'=>['view','create','issue','void','print','export'], 'payments'=>['view','record','reverse','export'], 'expenses'=>['view','create','edit','archive','export'], 'reports'=>['view','export'], 'ai'=>['access','financial','propose'], 'settings'=>['view'], 'backup'=>['create','download']
];}
function valid_permissions(array $input): array { $all=[];foreach(permission_options() as $m=>$actions)foreach($actions as $a)$all[]=$m.'.'.$a;foreach($input as $p)if(!in_array($p,$all,true))throw new DomainException('Unknown permission.');return array_values(array_unique($input)); }
