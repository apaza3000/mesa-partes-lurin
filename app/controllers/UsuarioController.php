<?php

require_once __DIR__ . '/../models/usuario.php';

class UsuarioController {
    private Usuario $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    // Iniciar sesión
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

            // Verificar la contraseña (suponiendo hash o verificación directa)
            if ($usuario && password_verify($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
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

    // Cerrar sesión
    public function logout(): void
    {
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
}