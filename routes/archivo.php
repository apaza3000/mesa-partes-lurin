<?php

use App\Controllers\ArchivoController;
use Src\Core\Router;

// TAREA E06-1: Archivo Central
Router::get("/archivo", [ArchivoController::class, "index"]);
Router::post("/archivo/archivar", [ArchivoController::class, "archivar"]);
Router::get("/archivo/{id}/cargo", [ArchivoController::class, "reimprimirCargo"]);
