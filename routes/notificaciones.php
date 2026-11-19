<?php

use App\Controllers\NotificacionController;
use Src\Core\Router;

// TAREA E08-1: Notificaciones web del header
Router::get("/notificaciones", [NotificacionController::class, "index"]);
Router::get("/notificaciones/contador", [NotificacionController::class, "contador"]);
Router::post("/notificaciones/leer", [NotificacionController::class, "marcarLeida"]);
Router::post("/notificaciones/leer-todas", [NotificacionController::class, "marcarTodas"]);
