<?php

use App\Controllers\DashboardController;
use Src\Core\Router;

// Ruta principal del Dashboard por rol
Router::get('/dashboard', [DashboardController::class, 'index']);