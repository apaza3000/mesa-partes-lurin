<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Usuario;
use App\Support\Catalogos;
use App\Support\Csrf;
use App\Support\Flash;
use App\Support\Roles;
use PDOException;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;
use Src\Core\Validator;

class AuthController extends Controller
{
    use AuthorizesRequest;

    public function showLogin()
    {
        if (Session::isAuthenticated()) {
            $this->redirect("/");
        }
        // Csrf::token() materializa el token de sesión que consumen las vistas.
        return $this->view("login", [
            'csrf_token' => Csrf::token(),
            'flash' => Flash::pull(),
        ]);
    }

    public function login()
    {
        $requst = new Request();

        $data = $requst->all();

        // SEC-01: el login también exige token CSRF (previene fuerza de sesión).
        $this->requerirCsrf($data, '/login');

        $rules = [
            "username" => ["required"],
            "password" => ["required"]
        ];

        $validator = new Validator();

        if (!$validator->validate($rules, $data)) {

            return $this->view('login', [
                'errors' => $validator->getErrors(),
                'datos_viejos' => $data,
                'csrf_token' => Csrf::token(),
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

                // MEN-03: Usuario::login() ya devuelve `roles` como array.
                $roles = Roles::normalize($usuario['roles'] ?? []);

                Session::set("usuario_id", (int) $usuario['id_usuario']);
                Session::set("usuario_nombre", $nombre !== '' ? $nombre : $usuario['username']);
                Session::set("usuario_rol", Roles::prioritario($roles));
                Session::set("usuario_roles", $roles);
                Session::set("usuario_area", $usuario['id_area'] ?? null);
                Session::set("usuario_login_ts", time());

                Session::login($usuario);

                // SEC-01: token nuevo por sesión autenticada.
                Csrf::rotar();

                return $this->redirect("/dashboard");
            }
        }
        return $this->view('login', [
            'error_message' => "Usuario o contraseña incorrectos",
            'datos_viejos' => $data,
            'csrf_token' => Csrf::token(),
        ]);
    }


    public function register()
    {
        return $this->view("register", [
            'tiposDocumento' => Catalogos::TipoDocumento,
            'csrf_token' => Csrf::token(),
            'flash' => Flash::pull(),
        ]);
    }


    public function logout(): void
    {
        // El cierre se ejecuta solo con token: evita el logout forzado por GET.
        $datos = (new Request())->all();
        $this->requerirCsrf($datos, '/dashboard');

        Csrf::borrar();
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

        // SEC-01
        $this->requerirCsrf($datos, '/register');

        $rules = [
            // VAL-05: catálogo completo del ENUM (faltaba RUC).
            'tipo_documento' => ['required', 'in' => Catalogos::TipoDocumento],
            'numero_documento' => ['required', 'string', 'minLength' => 6, 'maxLength' => Catalogos::limite('personas.numero_documento'), 'unique' => ['personas', 'numero_documento']],
            'nombres' => ['required', 'string', 'minLength' => 3, 'maxLength' => Catalogos::limite('personas.nombres'),],
            'apellido_paterno' => ['required', 'string', 'minLength' => 3, 'maxLength' => Catalogos::limite('personas.apellido_paterno'),],
            'apellido_materno' => ['nullable', 'string', 'maxLength' => Catalogos::limite('personas.apellido_materno'),],
            'email' => ['required', 'email', 'maxLength' => Catalogos::limite('personas.email'), 'unique' => ['personas', 'email'],],
            'telefono' => ['nullable', 'string', 'maxLength' => Catalogos::limite('personas.telefono'),],
            'direccion' => ['nullable', 'string', 'maxLength' => Catalogos::limite('personas.direccion'),],
            'username' => ['required', 'string', 'minLength' => 4, 'maxLength' => Catalogos::limite('usuarios.username'), 'unique' => ['usuarios', 'username'],],
            'password' => ['required', 'string', 'minLength' => 6, 'maxLength' => 100,],
            'password_confirm' => ['required', 'string', 'minLength' => 6, 'maxLength' => 100,],
        ];

        if (!$validator->validate($rules, $datos)) {
            return $this->view("register", [
                'errors' => $validator->getErrors(),
                'datos_viejos'   => $datos,
                'tiposDocumento' => Catalogos::TipoDocumento,
                'csrf_token' => Csrf::token(),
            ]);
        }

        if ($datos['password'] !== $datos['password_confirm']) {
            return $this->view("register", [
                'errors' => ['Las contraseñas no coinciden.'],
                'datos_viejos'   => $datos,
                'tiposDocumento' => Catalogos::TipoDocumento,
                'csrf_token' => Csrf::token(),
            ]);
        }

        $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);

        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->registrarInvitado($datos);

        if (!$resultado['ok']) {
            return $this->view("register", [
                'errors' => ['Error al registrar: ' . $resultado['mensaje']],
                'datos_viejos'   => $datos,
                'tiposDocumento' => Catalogos::TipoDocumento,
                'csrf_token' => Csrf::token(),
            ]);
        }

        // SEC-08: no se inventa una sesión a mano (eso dejaba al usuario sin
        // `authenticated` y con un rol inexistente en BD) y no se apunta a /home.
        // El alta pública queda pendiente de que un administrador le asigne rol.
        Flash::exito('Registro enviado. Un administrador debe asignarle un rol para que pueda ingresar.');
        $this->redirect("/login");
    }
}
