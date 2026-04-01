<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Корочки.есть');
define('BASE_PATH', dirname(__DIR__));

date_default_timezone_set('Europe/Moscow');


define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost'); 
define('DB_PORT', '3306');
define('DB_NAME', 'korochki_est');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
