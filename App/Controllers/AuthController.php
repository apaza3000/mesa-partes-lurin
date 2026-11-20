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
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // SEC-01: rate limiting por usuario + IP (5 fallos / 15 min).
        $bloqueo = $this->loginBloqueado($username, $ip);
        if ($bloqueo !== null) {
            return $this->view('login', [
                'error_message' => "Demasiados intentos fallidos. Intente nuevamente en {$bloqueo} minuto(s).",
                'datos_viejos' => $data,
                'csrf_token' => Csrf::token(),
            ]);
        }

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

                // E11-2: guardar la persona del usuario autenticado para precargar
                // formularios (envío de documentos) sin consultar la BD en cada vista.
                $personaDatos = [];
                if (!empty($usuario['id_persona'])) {
                    try {
                        $personaModel = new \App\Models\Persona();
                        $personaDatos = $personaModel->getById((int) $usuario['id_persona']);
                    } catch (\Throwable $e) {
                        $personaDatos = [];
                    }
                }
                if (empty($personaDatos)) {
                    $personaDatos = [
                        'id_persona'       => $usuario['id_persona'] ?? null,
                        'tipo_documento'   => null,
                        'numero_documento' => null,
                        'nombres'          => $usuario['nombres'] ?? null,
                        'apellido_paterno' => $usuario['apellido_paterno'] ?? null,
                        'apellido_materno' => $usuario['apellido_materno'] ?? null,
                        'email'            => $usuario['email'] ?? null,
                        'telefono'         => null,
                        'direccion'        => null,
                        'razon_social'     => null,
                    ];
                }
                Session::set("usuario_persona", $personaDatos);

                Session::login($usuario);

                // SEC-01: token nuevo por sesión autenticada.
                Csrf::rotar();

                // SEC-01: limpiar intentos fallidos y registrar éxito.
                $this->limpiarIntentosLogin($username, $ip);
                $this->registrarIntentoLogin($username, $ip, true);

                return $this->redirect("/dashboard");
            }
        }
        // SEC-01: registrar intento fallido.
        $this->registrarIntentoLogin($username, $ip, false);

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

    // SEC-01: helpers de rate limiting
    private function loginBloqueado(string $username, string $ip): ?int
    {
        try {
            $db = \Src\Core\Conexion::getConexion();
            $stmt = $db->prepare(
                "SELECT COUNT(*) AS c, MIN(creado_en) AS primero
                 FROM intentos_login
                 WHERE identificador = :u AND ip = :ip AND exitoso = 0
                   AND creado_en >= (NOW() - INTERVAL 15 MINUTE)"
            );
            $stmt->execute([':u' => $username, ':ip' => $ip]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $c = (int) ($row['c'] ?? 0);
            if ($c < 5) {
                return null;
            }
            $primero = strtotime((string) ($row['primero'] ?? ''));
            $desbloqueo = $primero + (15 * 60);
            $restante = (int) ceil(($desbloqueo - time()) / 60);
            return max(1, $restante);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function registrarIntentoLogin(string $username, string $ip, bool $exitoso): void
    {
        try {
            $db = \Src\Core\Conexion::getConexion();
            $stmt = $db->prepare(
                "INSERT INTO intentos_login (identificador, ip, exitoso) VALUES (:u, :ip, :e)"
            );
            $stmt->execute([':u' => $username, ':ip' => $ip, ':e' => $exitoso ? 1 : 0]);
        } catch (\Throwable $e) {
        }
    }

    private function limpiarIntentosLogin(string $username, string $ip): void
    {
        try {
            $db = \Src\Core\Conexion::getConexion();
            $stmt = $db->prepare(
                "DELETE FROM intentos_login WHERE identificador = :u AND ip = :ip AND exitoso = 0"
            );
            $stmt->execute([':u' => $username, ':ip' => $ip]);
        } catch (\Throwable $e) {
        }
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
            'password' => ['required', 'string', 'minLength' => 8, 'maxLength' => 100,],
            'password_confirm' => ['required', 'string', 'minLength' => 8, 'maxLength' => 100,],
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

        // SEC-02: política de contraseñas robustas.
        $pwd = (string) $datos['password'];
        $pwdErrores = [];
        if (strlen($pwd) < 8) $pwdErrores[] = 'La contraseña debe tener al menos 8 caracteres.';
        if (!preg_match('/[A-Z]/', $pwd)) $pwdErrores[] = 'Debe incluir al menos una mayúscula.';
        if (!preg_match('/[0-9]/', $pwd)) $pwdErrores[] = 'Debe incluir al menos un número.';
        if (!preg_match('/[^A-Za-z0-9]/', $pwd)) $pwdErrores[] = 'Debe incluir al menos un carácter especial.';
        if (!empty($pwdErrores)) {
            return $this->view("register", [
                'errors' => $pwdErrores,
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

    // ---- Recuperación de contraseña ----

    public function showRecuperar()
    {
        if (Session::isAuthenticated()) {
            $this->redirect("/");
        }
        return $this->view("recuperar", [
            'csrf_token' => Csrf::token(),
            'flash' => Flash::pull(),
        ]);
    }

    public function enviarRecuperacion()
    {
        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos, '/recuperar');

        $email = trim((string) ($datos['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->view('recuperar', [
                'error_message' => 'Ingrese un correo electrónico válido.',
                'datos_viejos' => $datos,
                'csrf_token' => Csrf::token(),
            ]);
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->obtenerPorEmail($email);

        // Respuesta genérica para no revelar si el correo existe.
        $mensajeGenerico = 'Si el correo está registrado, te enviaremos un enlace para restablecer tu contraseña.';

        if ($usuario) {
            try {
                $token = bin2hex(random_bytes(32));
                $hash = hash('sha256', $token);
                $expira = new \DateTime('+1 hour');
                $usuarioModel->crearTokenRecuperacion((int) $usuario['id_usuario'], $hash, $expira);

                $url = $this->appUrl() . '/restablecer?token=' . urlencode($token);

                ob_start();
                $nombre = trim(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellido_paterno'] ?? ''));
                $expira = '1 hora';
                include \Src\Core\Path::base('view') . DIRECTORY_SEPARATOR . 'emails' . DIRECTORY_SEPARATOR . 'recuperar-password.php';
                $html = ob_get_clean();

                $mail = new \App\Services\MailService();
                $mail->enviar($email, 'Recuperación de contraseña - Mesa de Partes Lurín', $html);
            } catch (\Throwable $e) {
                error_log('enviarRecuperacion: ' . $e->getMessage());
            }
        }

        Flash::exito($mensajeGenerico);
        $this->redirect('/login');
    }

    public function showRestablecer()
    {
        if (Session::isAuthenticated()) {
            $this->redirect("/");
        }
        $token = trim((string) ($_GET['token'] ?? ''));
        $usuarioModel = new Usuario();
        $idUsuario = $usuarioModel->validarTokenRecuperacion($token);
        if (!$token || !$idUsuario) {
            Flash::error('El enlace de recuperación es inválido o ha expirado.');
            $this->redirect('/login');
            return;
        }
        return $this->view('restablecer', [
            'token' => $token,
            'csrf_token' => Csrf::token(),
            'flash' => Flash::pull(),
        ]);
    }

    public function restablecer()
    {
        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos, '/restablecer');

        $token = trim((string) ($datos['token'] ?? ''));
        $password = (string) ($datos['password'] ?? '');
        $confirm = (string) ($datos['password_confirm'] ?? '');

        $usuarioModel = new Usuario();
        $idUsuario = $usuarioModel->validarTokenRecuperacion($token);
        if (!$token || !$idUsuario) {
            Flash::error('El enlace de recuperación es inválido o ha expirado.');
            $this->redirect('/login');
            return;
        }

        if ($password !== $confirm) {
            return $this->view('restablecer', [
                'token' => $token,
                'error_message' => 'Las contraseñas no coinciden.',
                'csrf_token' => Csrf::token(),
            ]);
        }
        // Política SEC-02
        $pwdErrores = [];
        if (strlen($password) < 8) $pwdErrores[] = 'La contraseña debe tener al menos 8 caracteres.';
        if (!preg_match('/[A-Z]/', $password)) $pwdErrores[] = 'Debe incluir al menos una mayúscula.';
        if (!preg_match('/[0-9]/', $password)) $pwdErrores[] = 'Debe incluir al menos un número.';
        if (!preg_match('/[^A-Za-z0-9]/', $password)) $pwdErrores[] = 'Debe incluir al menos un carácter especial.';
        if (!empty($pwdErrores)) {
            return $this->view('restablecer', [
                'token' => $token,
                'error_message' => implode(' ', $pwdErrores),
                'csrf_token' => Csrf::token(),
            ]);
        }

        $ok = $usuarioModel->usarTokenRecuperacion($token, password_hash($password, PASSWORD_DEFAULT));
        if (!$ok) {
            Flash::error('No se pudo restablecer la contraseña. El enlace puede haber expirado.');
            $this->redirect('/login');
            return;
        }

        Flash::exito('Tu contraseña fue restablecida. Ya puedes iniciar sesión.');
        $this->redirect('/login');
    }

    private function appUrl(): string
    {
        $cfg = require \Src\Core\Path::base('config') . DIRECTORY_SEPARATOR . 'mail.php';
        $url = trim((string) ($cfg['app_url'] ?? ''));
        if ($url !== '') {
            return rtrim($url, '/');
        }
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}
