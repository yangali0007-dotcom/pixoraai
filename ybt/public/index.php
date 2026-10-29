<?php
require dirname(__DIR__).'/app/bootstrap.php';
redirect(current_user()?'dashboard.php':'login.php');
