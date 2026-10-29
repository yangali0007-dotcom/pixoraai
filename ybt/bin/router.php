<?php
// Strict router for the PHP development server. Production uses public/ as its document root.
$public=realpath(dirname(__DIR__).'/public');$path=rawurldecode(parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)??'/');
if(str_contains($path,"\0")||preg_match('~(?:^|[/\\\\])\.\.(?:[/\\\\]|$)~',$path)){http_response_code(404);exit('Page not found.');}
if($path==='/')return false;
$file=realpath($public.$path);
if(!$file||!str_starts_with($file,$public.DIRECTORY_SEPARATOR)||!is_file($file)||str_starts_with(basename($file),'.')){http_response_code(404);exit('Page not found.');}
return false;
