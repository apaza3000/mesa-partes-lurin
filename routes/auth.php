<?php

use App\Controllers\AuthController;
use Src\Core\Router;

Router::get('/login', [AuthController::class, 'showLogin']);
Router::get('/register', [AuthController::class, 'register']);
Router::post('/register', [AuthController::class, 'postRegister']);
Router::post('/login', [AuthController::class, 'login']);
Router::post('/logout', [AuthController::class, 'logout']);
Router::get('/logout', [AuthController::class, 'logout']);
