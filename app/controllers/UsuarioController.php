<?php
namespace App\Controllers;

use App\Services\UsuarioServices;
use Src\Core\Controller;

class UsuarioController extends Controller
{
    private UsuarioServices $UsuarioServices;

    public function __construct()
    {
        $this->UsuarioServices = new UsuarioServices();
    }

    public function index()
    {
        $usuarios = $this->UsuarioServices->listarUsuarios();
        $this->view('usuarios.index', ['usuarios' => $usuarios], 'app');
    }

    public function create()
    {
        $this->view('usuarios.crear', [], 'app');
    }

    public function edit(int $id)
    {
        $usuario = $this->UsuarioServices->obtenerPorId($id);
        $this->view('usuarios.editar', ['usuario' => $usuario], 'app');
    }

    // Aquí irían store(), update(), toggleEstado(), etc.
}
/*
namespace App\Controllers;

use App\Models\Usuario;
use App\Validators\UsuarioValidator;

class UsuarioController {
    private Usuario $usuarioModel;
    private UsuarioValidator $validator;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->validator = new UsuarioValidator();
    }

    // --- AUTENTICACIÓN ---

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Por favor complete todos los campos.';
                header('Location: index.php?page=login');
                exit;
            }

            $usuario = $this->usuarioModel->obtenerPorEmail($email);

            if ($usuario && password_verify($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido_P'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['usuario_area'] = $usuario['id_area'];

                header('Location: index.php?page=dashboard');
                exit;
            } else {
                $_SESSION['error'] = 'Credenciales incorrectas o usuario inactivo.';
                header('Location: index.php?page=login');
                exit;
            }
        }
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }

    // --- GESTIÓN DE USUARIOS (CRUD) ---

    // 1. Listar usuarios
    public function index(): void
    {
        $usuarios = $this->usuarioModel->obtenerTodos();
        // Aquí se incluye la vista de listado de usuarios
        require_once __DIR__ . '/../../views/usuarios/index.php';
    }

    // 2. Guardar un nuevo usuario
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar datos con el UsuarioValidator (Fase 2)
            $errores = $this->validator->validar($_POST, null, false);

            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                $_SESSION['old'] = $_POST;
                header('Location: index.php?page=usuarios_crear');
                exit;
            }

            // Preparar datos para persona
            $datosPersona = [
                'dni' => trim($_POST['dni']),
                'nombre' => trim($_POST['nombre']),
                'apellido_P' => trim($_POST['apellido_P']),
                'apellido_M' => trim($_POST['apellido_M']),
                'email' => trim($_POST['email'])
            ];

            // Encriptar contraseña y preparar datos para usuario
            $datosUsuario = [
                'id_rol' => (int) $_POST['id_rol'],
                'id_area' => (int) $_POST['id_area'],
                'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
                'estado' => $_POST['estado'] ?? 'Activo'
            ];

            try {
                $this->usuarioModel->registrarTransaccion($datosPersona, $datosUsuario);
                $_SESSION['exito'] = 'Usuario registrado correctamente.';
                header('Location: index.php?page=usuarios');
                exit;
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Ocurrió un error al registrar el usuario: ' . $e->getMessage();
                header('Location: index.php?page=usuarios_crear');
                exit;
            }
        }
    }

    // 3. Actualizar un usuario existente
    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
            $idPersona = (int) ($_POST['id_persona'] ?? 0);

            // Validar datos omitiendo el chequeo del propio ID
            $errores = $this->validator->validar($_POST, $idPersona, true);

            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                header("Location: index.php?page=usuarios_editar&id={$idUsuario}");
                exit;
            }

            $datosPersona = [
                'dni' => trim($_POST['dni']),
                'nombre' => trim($_POST['nombre']),
                'apellido_P' => trim($_POST['apellido_P']),
                'apellido_M' => trim($_POST['apellido_M']),
                'email' => trim($_POST['email'])
            ];

            $datosUsuario = [
                'id_rol' => (int) $_POST['id_rol'],
                'id_area' => (int) $_POST['id_area']
            ];

            try {
                $this->usuarioModel->actualizarTransaccion($idUsuario, $idPersona, $datosPersona, $datosUsuario);
                $_SESSION['exito'] = 'Usuario actualizado correctamente.';
                header('Location: index.php?page=usuarios');
                exit;
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error al actualizar el usuario: ' . $e->getMessage();
                header("Location: index.php?page=usuarios_editar&id={$idUsuario}");
                exit;
            }
        }
    }

    // 4. Cambiar estado (Activar / Desactivar / Suspender)
    public function cambiarEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
            $nuevoEstado = $_POST['estado'] ?? 'Inactivo';

            if ($idUsuario > 0) {
                $this->usuarioModel->cambiarEstado($idUsuario, $nuevoEstado);
                $_SESSION['exito'] = 'El estado del usuario ha sido actualizado.';
            }

            header('Location: index.php?page=usuarios');
            exit;
        }
    }
}*/