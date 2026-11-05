<?php

use App\Controllers\MesaPartesController;
use Src\Core\Router;

// ============================================================
// EPICA 2 — Recepción Presencial e Interna en Ventanilla
// ============================================================

// TAREA E02-1: Registro en ventanilla
Router::get('/mesa-partes/registrar', [MesaPartesController::class, 'register']);
Router::post('/mesa-partes/registrar', [MesaPartesController::class, 'store']);
Router::get('/mesa-partes/exitoso', [MesaPartesController::class, 'exitoso']);

// TAREA E02-2: Bandeja de Mesa de Partes
Router::get('/mesa-partes/bandeja', [MesaPartesController::class, 'bandeja']);
Router::get('/mesa-partes/cargo/{id}', [MesaPartesController::class, 'imprimirCargo']);

// EPICA 3 — Documentos formales en PDF (operador de ventanilla)
Router::get('/mesa-partes/cargo/{id}/pdf', [MesaPartesController::class, 'cargoPdf']);
Router::get('/mesa-partes/hoja-ruta/{id}', [MesaPartesController::class, 'hojaRuta']);

// Endpoints AJAX (búsqueda en vivo de remitente y detalle)
Router::get('/mesa-partes/personas/buscar', [MesaPartesController::class, 'buscarPersona']);
Router::get('/mesa-partes/personas/consultar', [MesaPartesController::class, 'consultarDocumento']);
Router::get('/mesa-partes/expediente/{id}', [MesaPartesController::class, 'detalle']);
