<?php

namespace App\Controllers;

use Src\Core\Controller;

class DashboardController extends Controller
{

    public function index()
    {
        return $this->view("inicio", [], "plantilla");
    }
}
