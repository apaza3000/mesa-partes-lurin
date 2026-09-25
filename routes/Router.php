<?php


// Manejo de la solicitud actual

use App\Controllers\DashboardController;
use src\Core\Router;

require_once __DIR__ . "/areas.php";
require_once __DIR__ . "/roles.php";
require_once __DIR__ . "/usuarios.php";
require_once __DIR__ . "/error.php";

Router::get("/",[DashboardController::class,"index"]);


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$requestUri = explode("?", $uri)[0];
$requestMethod = $_SERVER['REQUEST_METHOD'];


// Verificar si la URL contiene "/api-instituto"
if (strpos($requestUri, '/backend') === 0) {
    // Extraer la parte después de "/api-instituto"
    $requestUri = substr($requestUri, strlen('/backend'));
}

function error404()
{
    
    header("Location: /error/404");
};

$callbac = "error404";

Router::dispatch($requestUri, $requestMethod, $callbac);
