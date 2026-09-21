<?php

use App\Controllers\AreaController;
use Src\Core\Router;

Router::get("/areas", [AreaController::class, "index"]);
Router::get("/areas/{id}/editar", [AreaController::class, "edit"]);
Router::get("/areas/nuevo", [AreaController::class, "create"]);

Router::post("/areas/store", [AreaController::class, "store"]);
Router::post("/areas/{id}/update", [AreaController::class, "update"]);
Router::post("/areas/{id}/delete", [AreaController::class, "delete"]);
