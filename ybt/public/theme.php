<?php
require dirname(__DIR__).'/app/bootstrap.php';require_login();header('Content-Type: text/css; charset=utf-8');$p=json_decode(scalar('SELECT value FROM user_preferences WHERE user_id=?',[current_user()['id']])?:'{}',true);$css=[];
foreach(['primary'=>'--blue','accent'=>'--blue-dark'] as $key=>$var)if(isset($p[$key])&&preg_match('/^#[0-9a-f]{6}$/iD',$p[$key]))$css[]=$var.':'.$p[$key];
foreach(['font_size'=>['font-size',12,18],'radius'=>['--radius',0,20],'sidebar_width'=>['--sidebar',210,300]] as $key=>[$var,$min,$max])if(isset($p[$key]))$css[]=$var.':'.max($min,min($max,(int)$p[$key])).'px';
echo ':root{'.implode(';',$css).'}';
