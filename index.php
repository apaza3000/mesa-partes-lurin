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


// Cargar variables de entorno desde .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

Path::setBasePath(__DIR__);

// SEC-09: los errores de aplicación se registran en storage/php-error.log y se
// responde una página 500 genérica; nunca rutas absolutas ni trazas al navegador.
App\Support\ManejadorErrores::registrar();


require_once __DIR__ . DIRECTORY_SEPARATOR . "routes" . DIRECTORY_SEPARATOR . "Router.php";
