<?php

use App\Controllers\AuditoriaController;
use Src\Core\Router;

// TAREA E10-1: Visor de auditoría (solo ADMINISTRADOR)
Router::get("/auditoria", [AuditoriaController::class, "index"]);
Router::get("/auditoria/{id}", [AuditoriaController::class, "detalle"]);
