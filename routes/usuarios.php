<?php

use App\Controllers\UsuarioController;
use Src\Core\Router;

// VISTAS / MUESTRA DE DATOS (GET) 
Router::get('/usuarios', [UsuarioController::class, 'index']);             // Listado principal
Router::get('/usuarios/crear', [UsuarioController::class, 'create']);        // Formulario de alta
Router::get('/usuarios/{id}', [UsuarioController::class, 'show']);         // Ver detalle
Router::get('/usuarios/{id}/editar', [UsuarioController::class, 'editar']);  // Formulario de edición

// ACCIONES Y PROCESAMIENTO (POST)
Router::post('/usuarios/store', [UsuarioController::class, 'store']);       // Procesar nuevo usuario
Router::post('/usuarios/{id}', [UsuarioController::class, 'actualizar']);  // Procesar edición
Router::post('/usuarios/{id}/estado', [UsuarioController::class, 'estado']);// Activar/Desactivar
Router::post('/usuarios/{id}/delete', [UsuarioController::class, 'delete']);// Borrado lógico / Físico