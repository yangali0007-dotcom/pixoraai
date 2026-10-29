<?php
declare(strict_types=1);
$local = is_file(__DIR__.'/local.php') ? require __DIR__.'/local.php' : [];
return array_replace([
    'dsn' => getenv('YBT_DB_DSN') ?: 'mysql:host=127.0.0.1;port=3306;dbname=ybt;charset=utf8mb4',
    'db_user' => getenv('YBT_DB_USER') ?: 'ybt',
    'db_password' => getenv('YBT_DB_PASSWORD') ?: '',
    'storage' => getenv('YBT_STORAGE') ?: dirname(__DIR__, 3).'/ybt-private-storage',
    'key' => getenv('YBT_APP_KEY') ?: '',
    'environment' => getenv('YBT_ENV') ?: 'local',
    'timezone' => 'Asia/Kolkata',
], $local);
