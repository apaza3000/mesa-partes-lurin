<?php
<<<<<<< Updated upstream
// 1. Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
=======
session_start();
$_SESSION["login"] = true;
if ($_SESSION["login"]) {
    require_once("view/plantilla.php");
} else {
    require_once("view/pages/login.php");
>>>>>>> Stashed changes
}

// 2. Cargar el Autoload de Composer o un Autoloader personalizado
require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\UsuarioController;

// 3. Capturar la página/acción solicitada (Por defecto va al login)
$page = $_GET['page'] ?? 'login';

// 4. Instanciar el controlador de usuarios
$usuarioController = new UsuarioController();

// 5. Enrutamiento dinámico
switch ($page) {
    // --- AUTENTICACIÓN ---
    case 'login':
        // Muestra la vista de login o procesa el POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuarioController->login();
        } else {
            require_once __DIR__ . '/views/login.php';
        }
        break;

    case 'logout':
        $usuarioController->logout();
        break;

    // --- MÓDULO DE USUARIOS (CRUD) ---
    case 'usuarios':
        // Carga la vista con la lista de usuarios
        $usuarioController->index();
        break;

    case 'usuarios_crear':
        // Muestra el formulario para crear un usuario
        require_once __DIR__ . '/views/usuarios/crear.php';
        break;

    case 'usuarios_guardar':
        // Procesa el formulario POST para crear
        $usuarioController->guardar();
        break;

    case 'usuarios_editar':
        // Muestra el formulario para editar un usuario
        require_once __DIR__ . '/views/usuarios/editar.php';
        break;

    case 'usuarios_actualizar':
        // Procesa el formulario POST para actualizar
        $usuarioController->actualizar();
        break;

    case 'usuarios_estado':
        // Procesa el cambio de estado (Activo/Inactivo)
        $usuarioController->cambiarEstado();
        break;

    default:
        // Página de error 404
        http_response_code(404);
        echo "Página no encontrada";
        break;
}