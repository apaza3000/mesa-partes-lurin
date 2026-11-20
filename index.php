<?php

use Src\Core\Dotenv;
use Src\Core\Path;

session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "autoload.php";

/**
 * Obtener una variable de entorno con un valor predeterminado.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env($key, $default = null)
{
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    } elseif (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }

    return $default;
}


$lockFile = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'installed.lock';
$envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';

// Detectar si ya está instalado: lock file, o bien .env + BD con tablas.
$instalado = file_exists($lockFile) && file_exists($envFile);
if (!$instalado && file_exists($envFile)) {
    try {
        $dotenvTmp = Dotenv::createImmutable(__DIR__);
        $dotenvTmp->load();
        $cfg = require __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
        $dsn = $cfg['driver'] . ':host=' . $cfg['host'] . ';port=' . $cfg['port']
            . ';dbname=' . $cfg['database'] . ';charset=' . $cfg['charset'];
        $pdo = new PDO($dsn, $cfg['username'], $cfg['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("SHOW TABLES LIKE 'roles'");
        if ($stmt && $stmt->rowCount() > 0) {
            $instalado = true;
            if (!file_exists($lockFile)) {
                @file_put_contents($lockFile, date('c') . "\n");
            }
        }
    } catch (\Throwable $e) {
        // BD no disponible: se asume no instalado.
    }
}

Path::setBasePath(__DIR__);

if (!$instalado) {
    // Modo instalación: no exigir .env; solo rutas de instalación.
    if (file_exists($envFile)) {
        try {
            $dotenv = Dotenv::createImmutable(__DIR__);
            $dotenv->load();
        } catch (\Throwable $e) {
        }
    }
    App\Support\ManejadorErrores::registrar();

    require_once __DIR__ . DIRECTORY_SEPARATOR . "routes" . DIRECTORY_SEPARATOR . "install.php";

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestUri = explode("?", $uri)[0];
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    function error404()
    {
        header("Location: /install");
    };
    $callback = "error404";
    Src\Core\Router::dispatch($requestUri, $requestMethod, $callback);
    exit;
}

// Modo normal
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuración de la zona horaria institucional (Perú / America/Lima por defecto)
date_default_timezone_set(env('APP_TIMEZONE', 'America/Lima'));

// SEC-09: los errores de aplicación se registran en storage/php-error.log y se
// responde una página 500 genérica; nunca rutas absolutas ni trazas al navegador.
App\Support\ManejadorErrores::registrar();


require_once __DIR__ . DIRECTORY_SEPARATOR . "routes" . DIRECTORY_SEPARATOR . "Router.php";