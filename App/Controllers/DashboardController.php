<?php

namespace App\Controllers;

use Src\Core\Controller;
use Src\Core\Session;

class DashboardController extends Controller
{

    public function index()
    {

        return $this->view("index", []);
    }
    public function home()
    {
        if (!Session::isAuthenticated()) {
            return $this->redirect("/login");
        }
        return $this->view("inicio", [], "app");
    }
}
