<?php
return [
    'host' => env('DB_HOST', "localhost"),
    'database' => env('DB_NAME', 'mesa_partes_lurin'),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASS', ''),
    'port' => env('DB_PORT', '3306'),
    'charset' => env('DB_CHARSET', 'utf8'),
    'collation' => 'utf8_unicode_ci',
    'driver' => 'mysql',
];