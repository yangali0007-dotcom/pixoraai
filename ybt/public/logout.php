<?php
require dirname(__DIR__).'/app/bootstrap.php';
require_login();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Use the sign out button in your profile.');}
verify_csrf();audit('logout','user',(int)current_user()['id']);$_SESSION=[];session_destroy();setcookie(session_name(),'',time()-3600,'/');redirect('login.php');
