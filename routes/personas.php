<?php

use App\Controllers\PersonaController;
use Src\Core\Router;

Router::get('/personas', [PersonaController::class, 'index']);
Router::get('/personas/crear', [PersonaController::class, 'create']);
Router::get('/personas/buscar', [PersonaController::class, 'buscar']);
Router::post('/personas', [PersonaController::class, 'store']);
Router::get('/personas/{id}/editar', [PersonaController::class, 'edit']);
Router::post('/personas/{id}', [PersonaController::class, 'update']);
Router::get('/personas/{id}', [PersonaController::class, 'show']);
