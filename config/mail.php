<?php
// Configuración de correo (E13). Lee únicamente variables de entorno.
return [
    'habilitado'  => filter_var(env('MAIL_HABILITADO', 'false'), FILTER_VALIDATE_BOOLEAN),
    'host'        => env('MAIL_HOST', ''),
    'port'        => (int) env('MAIL_PORT', 587),
    'username'    => env('MAIL_USERNAME', ''),
    'password'    => env('MAIL_PASSWORD', ''),
    'encryption'  => env('MAIL_ENCRYPTION', 'tls'),
    'from_address' => env('MAIL_FROM_ADDRESS', ''),
    'from_name'   => env('MAIL_FROM_NAME', 'Mesa de Partes Lurín'),
    'mesa_partes' => env('MAIL_MESA_PARTES', ''),
    'app_url'     => env('APP_URL', ''),
];