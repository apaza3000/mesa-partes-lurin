<?php

namespace App\Controllers;
//use App\Services\UsuarioServices;
use App\Models\Usuario;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Validator;

class UsuarioController extends Controller
{
    private Usuario $user;
    //private UsuarioServices $UsuarioServices;
    public function __construct()
    {
        $this->user = new Usuario();
    }

    public function index(): void
    {
        // 1. Definir registros por página y obtener página actual de la URL
        $porPagina = 10;
        $paginaActual = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($paginaActual < 1) {
            $paginaActual = 1;
        }

        // 2. Obtener total de usuarios y calcular número total de páginas
        $totalUsuarios = $this->user->getTotalUsuarios();
        $totalPaginas = (int) ceil($totalUsuarios / $porPagina);

        // Evitar que pidan una página mayor al total disponible
        if ($paginaActual > $totalPaginas && $totalPaginas > 0) {
            $paginaActual = $totalPaginas;
        }

        // 3. Obtener los datos paginados
        $usuarios = $this->user->getUsuariosPaginados($paginaActual, $porPagina);
        $roles = $this->user->getAllRol();

        // 4. Enviar los datos a la vista
        $this->view('usuarios.index', [
            'usuarios' => $this->user->getAll(),
            'roles' => $this->user->getAllRol(),
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas
        ], 'app');
    }
    
}
