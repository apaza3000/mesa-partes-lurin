<?php
use App\Controllers\TrazabilidadController;
use Src\Core\Router;

Router::get('/trazabilidad', [TrazabilidadController::class, 'index']);