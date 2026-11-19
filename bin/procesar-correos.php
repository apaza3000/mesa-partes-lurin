<?php
require __DIR__ . '/../autoload.php';
require __DIR__ . '/../vendor/autoload.php';

// Cargar .env
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Definir env() helper (como en index.php) para CLI.
if (!function_exists('env')) {
    function env($key, $default = null) {
        if (isset($_ENV[$key])) return $_ENV[$key];
        if (isset($_SERVER[$key])) return $_SERVER[$key];
        return $default;
    }
}

// Inicializar rutas base (Path) para que Conexion/Config funcionen en CLI.
\Src\Core\Path::setBasePath(__DIR__ . '/..');

use App\Models\CorreoCola;
use App\Services\MailService;

$cola = new CorreoCola();
$mail = new MailService();

$pendientes = $cola->pendientes(20);
$procesados = 0;
foreach ($pendientes as $correo) {
    $adjuntos = [];
    if (!empty($correo['adjunto_contenido'])) {
        $adjuntos[] = [
            'contenido' => $correo['adjunto_contenido'],
            'nombre'    => $correo['adjunto_nombre'] ?? 'Cargo.pdf',
            'mime'      => 'application/pdf',
        ];
    }
    $ok = $mail->enviar(
        $correo['destinatario'],
        $correo['asunto'],
        $correo['cuerpo_html'],
        $adjuntos,
        $correo['cc']
    );
    if ($ok) {
        $cola->marcarEnviado((int) $correo['id_correo']);
        $procesados++;
    } else {
        $cola->marcarError((int) $correo['id_correo'], 'Fallo de envío SMTP');
    }
}
echo "Procesados: $procesados / " . count($pendientes) . "\n";