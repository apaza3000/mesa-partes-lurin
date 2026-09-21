<?php

namespace App\Controllers;

use Src\Core\Controller;

class ErrorController extends Controller
{


    function err404()
    {
        return $this->view("errors.404");
    }
}
