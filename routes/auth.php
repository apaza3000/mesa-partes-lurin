<?php

use App\Controllers\AuthController;
use Src\Core\Router;

Router::get('/login', [AuthController::class, 'showLogin']);
Router::get('/register', [AuthController::class, 'register']);
Router::post('/register', [AuthController::class, 'postRegister']);
Router::post('/login', [AuthController::class, 'login']);
// Recuperación de contraseña
Router::get('/recuperar', [AuthController::class, 'showRecuperar']);
Router::post('/recuperar', [AuthController::class, 'enviarRecuperacion']);
Router::get('/restablecer', [AuthController::class, 'showRestablecer']);
Router::post('/restablecer', [AuthController::class, 'restablecer']);
// SEC: se retiro el `GET /logout`; un enlace simple permitía cerrar la sesión
// desde cualquier página externa (login CSRF). El cierre ahora es POST + token.
Router::post('/logout', [AuthController::class, 'logout']);
