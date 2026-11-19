<?php

use App\Controllers\ObservacionController;
use App\Controllers\SubsanacionController;
use Src\Core\Router;

// TAREA E05-1: Registro de Observaciones por el Área
Router::post("/area/observar", [ObservacionController::class, "crear"]);

// TAREA E05-2: Subsanación pública y revisión por el área
Router::get("/consulta/subsanar/{codigo}", [SubsanacionController::class, "formulario"]);
Router::post("/consulta/subsanar", [SubsanacionController::class, "store"]);
Router::get("/area/subsanaciones", [SubsanacionController::class, "bandeja"]);
Router::post("/area/subsanaciones/levantar", [SubsanacionController::class, "levantar"]);
