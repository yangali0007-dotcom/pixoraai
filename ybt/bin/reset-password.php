<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
require dirname(__DIR__).'/app/bootstrap.php';$email=$argv[1]??'';$user=one('SELECT id FROM users WHERE email=? AND active=1',[$email]);if(!$user){fwrite(STDERR,"Active account not found.\n");exit(1);}$token=bin2hex(random_bytes(32));transaction(function()use($user,$token){query('UPDATE password_resets SET used_at=NOW() WHERE user_id=? AND used_at IS NULL',[$user['id']]);query('INSERT INTO password_resets(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 15 MINUTE))',[$user['id'],hash('sha256',$token)]);});fwrite(STDOUT,"Give this single-use token to the account holder through a private channel. It expires in 15 minutes.\nreset-password.php?token=".$token."\n");
