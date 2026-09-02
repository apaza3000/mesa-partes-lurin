<?php

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false || $value === null) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        }

        return $value !== null && $value !== '' ? $value : $default;
    }
}

return [
    'host' => env('DB_HOST', 'localhost'),
    'database' => env('DB_NAME', 'mesa_partes_lurin'),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASS', ''),
    'port' => env('DB_PORT', '3306'),
    'charset' => env('DB_CHARSET', 'utf8'),
    'collation' => 'utf8_unicode_ci',
    'driver' => env('DB_DRIVER', 'mysql'),
];
