<?php

namespace App\Controllers;

use App\Models\Usuario;
use PDOException;

class AuthController
{
    public function showLogin(): void
    {
        if (!empty($_SESSION['usuario_id'])) {
            header('Location: /');
            exit;
        }

        require __DIR__ . '/../../view/pages/login.php';
    }

    public function login(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $identificador = $_POST['identificador']
            ?? $_POST['email']
            ?? $_POST['username']
            ?? '';
        $password = $_POST['password'] ?? '';

        if (!is_string($identificador) || !is_string($password)) {
            http_response_code(400);
            return;
        }

        $identificador = trim($identificador);

        if ($identificador === '' || $password === '') {
            $_SESSION['email_value'] = $identificador;
            $_SESSION['error_email'] = $identificador === ''
                ? 'Ingrese su usuario o correo electrónico.'
                : '';
            $_SESSION['error_password'] = $password === ''
                ? 'Ingrese su contraseña.'
                : '';
            header('Location: /login');
            exit;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->login($identificador, $password);

        if ($usuario === null) {
            $_SESSION['email_value'] = $identificador;
            $_SESSION['error_password'] = 'Usuario o contraseña incorrectos.';
            header('Location: /login');
            exit;
        }

        try {
            $usuarioModel->registrarLogin((int) $usuario['id_usuario']);
        } catch (PDOException $e) {
            error_log('No se pudo registrar el inicio de sesión en auditoria: ' . $e->getMessage());
            $_SESSION['email_value'] = $identificador;
            $_SESSION['error_password'] = 'No se pudo registrar el acceso. Intente nuevamente.';
            header('Location: /login');
            exit;
        }

        session_regenerate_id(true);

        $nombre = trim(implode(' ', array_filter([
            $usuario['nombres'] ?? '',
            $usuario['apellido_paterno'] ?? '',
            $usuario['apellido_materno'] ?? '',
        ])));
        $roles = array_values(array_filter(array_map(
            'trim',
            explode(',', $usuario['roles'] ?? '')
        )));

        $_SESSION['usuario_id'] = (int) $usuario['id_usuario'];
        $_SESSION['usuario_nombre'] = $nombre !== '' ? $nombre : $usuario['username'];
        $_SESSION['usuario_rol'] = $roles[0] ?? null;
        $_SESSION['usuario_roles'] = $roles;
        $_SESSION['usuario_area'] = $usuario['id_area'] ?? null;

        unset($_SESSION['email_value'], $_SESSION['error_email'], $_SESSION['error_password']);

        header('Location: /');
        exit;
    }

    public function logout(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }

        session_destroy();

        header('Location: /login');
        exit;
    }

    public function roles(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No has iniciado sesión.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode(
            ['roles' => $_SESSION['usuario_roles'] ?? []],
            JSON_UNESCAPED_UNICODE
        );
    }
}
