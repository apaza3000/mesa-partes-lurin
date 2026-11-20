<?php

require __DIR__ . '/../autoload.php';
require __DIR__ . '/../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        if (isset($_ENV[$key])) return $_ENV[$key];
        if (isset($_SERVER[$key])) return $_SERVER[$key];
        return $default;
    }
}

\Src\Core\Path::setBasePath(__DIR__ . '/..');

use App\Models\CorreoCola;
use App\Services\MailService;

$cola = new CorreoCola();
$mail = new MailService();

$pendientes = $cola->pendientes(20);
$procesados = 0;

foreach ($pendientes as $correo) {

    $imagenes = [];

    if (!empty($correo['adjunto_contenido'])) {

        $base64 = $correo['adjunto_contenido'];

        // Quitar encabezado data:image/png;base64, si existe
        if (str_contains($base64, 'base64,')) {
            $base64 = explode('base64,', $base64, 2)[1];
        }

        $contenidoImagen = base64_decode($base64, true);

        if ($contenidoImagen !== false) {
            $imagenes[] = [
                'contenido' => $contenidoImagen,
                'cid' => 'qr_expediente',
                'nombre' => 'qr-expediente.png',
                'mime' => 'image/png'
            ];
        }
    }

    $ok = $mail->enviar(
        $correo['destinatario'],
        $correo['asunto'],
        $correo['cuerpo_html'],
        [], // Adjuntos
        $correo['cc'],
        $imagenes
    );

    if ($ok) {
        $cola->marcarEnviado((int) $correo['id_correo']);
        $procesados++;
    } else {
        $cola->marcarError(
            (int) $correo['id_correo'],
            'Fallo de envío SMTP'
        );
    }
}

echo "Procesados: $procesados / " . count($pendientes) . "\n";
