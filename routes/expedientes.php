<?php

use App\Controllers\ExpedienteController;
use Src\Core\Router;

Router::get('/expedientes', [ExpedienteController::class, 'index']);
Router::get('/expedientes/crear', [ExpedienteController::class, 'create']);
Router::post('/expedientes', [ExpedienteController::class, 'store']);
Router::get('/expedientes/{id}/seguimiento', [ExpedienteController::class, 'seguimiento']);
Router::get('/expedientes/{id}', [ExpedienteController::class, 'show']);
