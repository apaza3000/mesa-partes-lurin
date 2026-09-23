<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Config\Database;
use App\Controllers\TipoDocumentoController;

$db = Database::getConnection();
$controller = new TipoDocumentoController($db);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($requestUri === '/' || $requestUri === '/tipos-documento') {
    if ($method === 'GET') $controller->index();
    if ($method === 'POST') $controller->store();
} elseif (preg_match('/^\/tipos-documento\/(\d+)$/', $requestUri, $matches)) {
    $id = $matches[1];
    if ($method === 'GET') $controller->show($id);
    if ($method === 'POST') $controller->update($id);
} elseif (preg_match('/^\/tipos-documento\/(\d+)\/estado$/', $requestUri, $matches)) {
    if ($method === 'POST') $controller->toggleState($matches[1]);
} elseif (preg_match('/^\/tipos-documento\/(\d+)\/eliminar$/', $requestUri, $matches)) {
    if ($method === 'POST') $controller->delete($matches[1]);
} else {
    http_response_code(404);
    echo "404 - Ruta no encontrada";
}
