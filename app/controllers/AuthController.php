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


    public function register()
    {
        return $this->view("register");
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


    public function postRegister()
    {
        $request   = new Request();
        $validator = new Validator();


        $datos = $request->all();

        $rules = [
            'tipo_documento' => ['required', 'in' => ['DNI', 'CE', 'Pasaporte', 'Otro'],],
            'numero_documento' => ['required', 'string', 'minLength' => 6, 'maxLength' => 20, 'unique' => ['personas', 'numero_documento']],
            'nombres' => ['required', 'string', 'minLength' => 3, 'maxLength' => 100,],
            'apellido_paterno' => ['required', 'string', 'minLength' => 3, 'maxLength' => 100,],
            'apellido_materno' => ['nullable', 'string', 'maxLength' => 100,],
            'email' => ['required', 'email', 'maxLength' => 150, 'unique' => ['personas', 'email'],],
            'telefono' => ['nullable', 'string', 'maxLength' => 30,],
            'direccion' => ['nullable', 'string', 'maxLength' => 255,],
            'username' => ['required', 'string', 'minLength' => 4, 'maxLength' => 50, 'unique' => ['usuarios', 'username'],],
            'password' => ['required', 'string', 'minLength' => 6, 'maxLength' => 100,],
            'password_confirm' => ['required', 'string', 'minLength' => 6, 'maxLength' => 100,],
        ];

        if (!$validator->validate($rules, $datos)) {
            return $this->view("register", [
                'errors' => $validator->getErrors(),
                'datos_viejos'   => $datos,
            ]);
        }

        if ($datos['password'] !== $datos['password_confirm']) {
            return $this->view("register", [
                'errors' => ['Las contraseñas no coinciden.'],
                'datos_viejos'   => $datos,
            ]);
        }

        $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);

        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->registrarInvitado($datos);

        if (!$resultado['ok']) {
            return $this->view("register", [
                'errors' => ['Error al registrar: ' . $resultado['mensaje']],
                'datos_viejos'   => $datos,
                'trace' => $resultado['trace']
            ]);
        }

        // Guardar en sesión al usuario recién registrado
        $_SESSION['usuario'] = [
            'id_usuario' => $resultado['id_usuario'],
            'id_persona' => $resultado['id_persona'],
            'username'   => $datos['username'],
            'nombres'    => $datos['nombres'],
            'email'      => $datos['email'],
            'rol'        => 'INVITADO',
        ];

        $this->redirect("/home");
        exit;
    }
}
