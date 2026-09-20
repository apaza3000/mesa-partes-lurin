<?php


// Manejo de la solicitud actual

use App\Controllers\AreaController;
use src\Core\Router;


Router::get("/area", [AreaController::class, "index"]);



$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$requestUri = explode("?", $uri)[0];
$requestMethod = $_SERVER['REQUEST_METHOD'];






// Verificar si la URL contiene "/api-instituto"
if (strpos($requestUri, '/backend') === 0) {
    // Extraer la parte después de "/api-instituto"
    $requestUri = substr($requestUri, strlen('/backend'));
}

$callbac = function () {
    header("Location: /error/404", true, 404);
};



Router::dispatch($requestUri, $requestMethod, $callbac);
