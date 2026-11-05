<?php

use App\Controllers\BandejaAreaController;
use Src\Core\Router;

// Bandeja de Entrada y Gestión Documental del Área
Router::get("/area/bandeja-entrada", [BandejaAreaController::class, "index"]);
Router::get("/area/documentos-derivados", [BandejaAreaController::class, "index"]);

// Acciones operativas sobre derivaciones
Router::post("/area/recepcionar", [BandejaAreaController::class, "recepcionar"]);
Router::post("/area/devolver", [BandejaAreaController::class, "devolver"]);
Router::post("/area/derivar", [BandejaAreaController::class, "derivar"]);
Router::get("/area/derivacion/{id}", [BandejaAreaController::class, "detalle"]);

// TAREA E04-1: Atención de expediente con documento de respuesta
Router::get("/area/atender/{id}", [BandejaAreaController::class, "atender"]);
Router::post("/area/atender", [BandejaAreaController::class, "storeAtencion"]);

