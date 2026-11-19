<?php
use App\Controllers\PerfilController;
use Src\Core\Router;

Router::get('/perfil', [PerfilController::class, 'index']);
Router::post('/perfil', [PerfilController::class, 'actualizar']);