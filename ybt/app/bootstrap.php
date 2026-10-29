<?php
declare(strict_types=1);
$config = require dirname(__DIR__).'/config/config.php';
date_default_timezone_set($config['timezone']);
if (!is_dir($config['storage'])) mkdir($config['storage'], 0700, true);
ini_set('log_errors', '1');
ini_set('error_log', $config['storage'].'/application.log');
ini_set('display_errors', '0');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
header('Cache-Control: no-store');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('ybt_session');
    session_set_cookie_params(['httponly'=>true, 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off', 'samesite'=>'Lax', 'path'=>'/']);
    ini_set('session.use_strict_mode', '1');
    session_start();
}
require_once __DIR__.'/functions.php';
require_once __DIR__.'/finance.php';
require_once __DIR__.'/auth.php';
require_once __DIR__.'/records.php';
set_exception_handler(function(Throwable $error): void {
    error_log((string)$error);
    http_response_code(500);
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>YBT · Something went wrong</title><h1>We couldn’t complete that request.</h1><p>Your changes were not confirmed. Please return and try again.</p><a href="dashboard.php">Return to your workspace</a></html>';
});
