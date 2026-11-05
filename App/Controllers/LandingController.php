<?php

namespace App\Controllers;

use Src\Core\Controller;

/**
 * Landing page pública (punto de llegada del ciudadano).
 * view/pages/index.php se mostró sola ante el visitante: sin guard de sesión;
 * la propia vista ya adapta su cabecera (Iniciar sesión / Dashboard) según
 * Session::isAuthenticated().
 */
class LandingController extends Controller
{
    public function index(): void
    {
        $this->view('index');
    }
}
