<?php

namespace App\Controllers;

use App\Models\Area;
use Src\Core\Controller;

class AreaController extends Controller
{
private Area  $area;
public function __construct() {
    $this->area=new Area();
}

    public function index()
    {
        $areas=$this->area->getAll();
        return $this->view("inicio",['areas'=>$areas],"plantilla");
    }
}
