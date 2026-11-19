<?php

use App\Controllers\TipoDocumentoController;
use Src\Core\Router;

// TAREA E07-1: CRUD del catálogo de tipos de documento
Router::get("/tipos-documento", [TipoDocumentoController::class, "index"]);
Router::get("/tipos-documento/nuevo", [TipoDocumentoController::class, "crear"]);
Router::post("/tipos-documento", [TipoDocumentoController::class, "store"]);
Router::get("/tipos-documento/{id}/editar", [TipoDocumentoController::class, "editar"]);
Router::post("/tipos-documento/{id}", [TipoDocumentoController::class, "update"]);
Router::post("/tipos-documento/{id}/estado", [TipoDocumentoController::class, "cambiarEstado"]);
