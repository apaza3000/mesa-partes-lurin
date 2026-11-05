<?php

use App\Controllers\ObservacionController;
use Src\Core\Router;

// TAREA E05-1: Registro de Observaciones por el Área
Router::post("/area/observar", [ObservacionController::class, "crear"]);
