<?php

namespace App\Controllers;

use Src\Core\Controller;
use Src\Core\Session;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Validar autenticación
        if (!Session::isAuthenticated()) {
            return $this->redirect("/login");
        }
        /*
                // 2. Obtener datos de la sesión del usuario actual
                $usuario = Session::user();

                // 3. Obtener el rol del usuario (por defecto 'consultor' si no existe)
                $rolActual = $usuario['rol'] ?? 'consultor';
        */
        // 4. Retornar la vista usando el estándar del proyecto (notación con puntos)
        // Esto buscará en: view/pages/dashboard/index.php
        $this->view('dashboard.index', [
            /*
            'usuario' => $usuario,
            'rolActual' => $rolActual
            */
        ], 'app');
    }
}