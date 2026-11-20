<?php

use App\Controllers\InstallController;
use Src\Core\Router;

Router::get('/install', [InstallController::class, 'index']);
Router::get('/instalar', [InstallController::class, 'index']);
Router::get('/install/db', [InstallController::class, 'db']);
Router::post('/install/db', [InstallController::class, 'dbPost']);
Router::get('/install/env', [InstallController::class, 'env']);
Router::post('/install/env', [InstallController::class, 'envPost']);
Router::get('/install/admin', [InstallController::class, 'admin']);
Router::post('/install/admin', [InstallController::class, 'adminPost']);
Router::get('/install/completado', [InstallController::class, 'completado']);