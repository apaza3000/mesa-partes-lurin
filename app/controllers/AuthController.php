<?php

namespace App\Controllers;

use App\Models\Usuario;
use PDOException;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;
use Src\Core\Validator;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Session::isAuthenticated()) {
            $this->redirect("/");
        }
        return $this->view("login");
    }

    public function login()
    {
        $requst = new Request();

        $data = $requst->all();

        $rules = [
            "username" => ["required"],
            "password" => ["required"]
        ];

        $validator = new Validator();

        if (!$validator->validate($rules, $data)) {

            return $this->view('login', [
                'errors' => $validator->getErrors(),
                'datos_viejos' => $data,
            ]);
        }

        $username = $data["username"];
        $password = $data['password'];

        $usuarioModel = new Usuario();

        $usuario = $usuarioModel->login($username, $password);

      

        if ($usuario) {
            if (password_verify($password, $usuario['password'])) {
                unset($usuario['password']);



                try {
                    $usuarioModel->registrarLogin((int) $usuario['id_usuario']);
                } catch (PDOException $e) {
                }

                $nombre = trim(implode(' ', array_filter([
                    $usuario['nombres'] ?? '',
                    $usuario['apellido_paterno'] ?? '',
                    $usuario['apellido_materno'] ?? '',
                ])));
                $roles = array_values(array_filter(array_map(
                    'trim',
                    explode(',', $usuario['roles'] ?? '')
                )));

                Session::set("usuario_id", (int) $usuario['id_usuario']);
                Session::set("usuario_nombre", $nombre !== '' ? $nombre : $usuario['username']);
                Session::set("usuario_rol", $roles[0] ?? null);
                Session::set("usuario_roles", $roles);
                Session::set("usuario_area", $usuario['id_area'] ?? null);

                Session::login($usuario);
                return   $this->redirect("/");
            }
        }
        return $this->view('login', [
            'error_message' => "Usuario o contraseña incorrectos",
            'datos_viejos' => $data,
        ]);
    }

    public function logout(): void
    {
        Session::logout();

        $this->redirect("/login");
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
