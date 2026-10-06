<?php

use App\Controllers\ConsultaExpedienteController;
use App\Controllers\EntregaController;
use Src\Core\Router;

Router::get("/entregas", [EntregaController::class, "index"]);
Router::get("/entregas/exitoso", [EntregaController::class, "exitoso"]);
Router::post("/entregas/store", [EntregaController::class, "store"]);



Router::get("/consulta", [ConsultaExpedienteController::class, "index"]);
Router::post("/consulta/buscar", [ConsultaExpedienteController::class, "buscar"]);

Router::get("/consulta/descargar/{idArchivo}", [ConsultaExpedienteController::class, "descargar"]);
