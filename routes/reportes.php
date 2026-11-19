<?php

use App\Controllers\ReporteController;
use Src\Core\Router;

// TAREA E09-1: Reportes documentarios y exportaciones
Router::get("/reportes", [ReporteController::class, "index"]);
Router::get("/reportes/exportar/csv", [ReporteController::class, "exportarCsv"]);
Router::get("/reportes/exportar/pdf", [ReporteController::class, "exportarPdf"]);
