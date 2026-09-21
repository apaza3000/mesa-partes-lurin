<?php

use App\Controllers\ErrorController;
use Src\Core\Router;

Router::get("/error/404",[ErrorController::class,"err404"]);