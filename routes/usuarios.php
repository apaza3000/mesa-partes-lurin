<?php

use App\Controllers\UsuarioController;
use Src\Core\Router;

// Rutas actualizadas a /usuarios
Router::get('/usuarios', [UsuarioController::class, 'index']);
Router::get('/usuarios/nuevo', [UsuarioController::class, 'create']);
Router::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit']);
Router::get('/usuarios/{id}/ver', [UsuarioController::class, 'show']);

Router::post('/usuarios/store', [UsuarioController::class, 'store']);
Router::post('/usuarios/{id}/update', [UsuarioController::class, 'update']);
Router::post('/usuarios/{id}/estado', [UsuarioController::class, 'toggleEstado']);
Router::post('/usuarios/{id}/delete', [UsuarioController::class, 'delete']);