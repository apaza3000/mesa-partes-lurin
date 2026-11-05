<?php

use App\Controllers\ConsultaExpedienteController;
use App\Controllers\EntregaController;
use Src\Core\Router;

Router::get("/entregas", [EntregaController::class, "index"]);
Router::get("/entregas/exitoso", [EntregaController::class, "exitoso"]);
Router::post("/entregas/store", [EntregaController::class, "store"]);



Router::get("/consulta", [ConsultaExpedienteController::class, "index"]);
Router::post("/consulta/buscar", [ConsultaExpedienteController::class, "buscar"]);

// EPICA 3 — Cargo de Recepción en PDF (descarga pública por código, enlazada desde el QR)
Router::get("/consulta/cargo/{codigo}", [ConsultaExpedienteController::class, "imprimirCargo"]);

Router::get("/consulta/descargar/{idArchivo}", [ConsultaExpedienteController::class, "descargar"]);
