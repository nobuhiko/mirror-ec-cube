<?php
define('ECCUBE_INSTALL', 'ON');
$postgres = getenv("DATABASE_URL");
if ($postgres) {
    $url = parse_url($postgres);
    define('HTTP_URL', 'http://ancient-peak-1910.herokuapp.com/html/');
    define('HTTPS_URL', 'http://ancient-peak-1910.herokuapp.com/html/');
    define('ROOT_URLPATH', '/html/');
    define('DOMAIN_NAME', '');
    define('DB_TYPE', 'pgsql');
    define('DB_USER', $url['user']);
    define('DB_PASSWORD', $url["pass"]);
    define('DB_SERVER', $url["host"]);
    define('DB_NAME', substr($url["path"], 1));
    define('DB_PORT', $url['port']);
} else {
    define('HTTP_URL', 'http://test:8888/eccube/html/');
    define('HTTPS_URL', 'http://test:8888/eccube/html/');
    define('ROOT_URLPATH', '/eccube/html/');
    define('DOMAIN_NAME', '');
    define('DB_TYPE', 'mysql');
    define('DB_USER', 'root');
    define('DB_PASSWORD', 'root');
    define('DB_SERVER', 'localhost');
    define('DB_NAME', 'eccube');
    define('DB_PORT', '');
}

define('ADMIN_DIR', 'admin/');
define('ADMIN_FORCE_SSL', FALSE);
define('ADMIN_ALLOW_HOSTS', 'a:0:{}');
define('AUTH_MAGIC', 'drageslopifrioliojoufriociostesabebadras');
define('PASSWORD_HASH_ALGOS', 'sha256');
define('MAIL_BACKEND', 'mail');
define('SMTP_HOST', '');
define('SMTP_PORT', '');
define('SMTP_USER', '');
define('SMTP_PASSWORD', '');
