<?php

use App\Controllers\RolController;
use Src\Core\Router;

Router::get('/roles', [RolController::class, 'index']);
Router::get('/roles/nuevo', [RolController::class, 'create']);
Router::get('/roles/{id}/editar', [RolController::class, 'edit']);
Router::post('/roles/store', [RolController::class, 'store']);
Router::post('/roles/{id}/update', [RolController::class, 'update']);
Router::post('/roles/{id}/delete', [RolController::class, 'delete']);
