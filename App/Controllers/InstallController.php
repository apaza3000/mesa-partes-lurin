<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Support\Csrf;
use Src\Core\Controller;
use Src\Core\Path;
use Src\Core\Request;
use Src\Core\Session;

class InstallController extends Controller
{
    private string $baseDir;

    public function __construct()
    {
        $this->baseDir = dirname(dirname(__DIR__));
    }

    private function estaInstalado(): bool
    {
        return file_exists($this->baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'installed.lock')
            && file_exists($this->baseDir . DIRECTORY_SEPARATOR . '.env');
    }

    private function bloquearSiInstalado(): void
    {
        if ($this->estaInstalado()) {
            $this->redirect('/');
        }
    }

    public function index()
    {
        $this->bloquearSiInstalado();

        $reqs = [
            ['PHP >= 8.0', version_compare(PHP_VERSION, '8.0.0', '>=')],
            ['Extensión PDO', extension_loaded('pdo')],
            ['Extensión PDO MySQL', extension_loaded('pdo_mysql')],
            ['Extensión mysqli', extension_loaded('mysqli')],
            ['Extensión GD', extension_loaded('gd')],
            ['Extensión mbstring', extension_loaded('mbstring')],
            ['Extensión openssl', extension_loaded('openssl')],
            ['storage/ escribible', is_writable($this->baseDir . DIRECTORY_SEPARATOR . 'storage')],
            ['raíz escribible (para .env)', is_writable($this->baseDir)],
        ];

        $todoOk = true;
        foreach ($reqs as $r) {
            if (!$r[1]) { $todoOk = false; break; }
        }

        $this->view('install/index', [
            'reqs' => $reqs,
            'todoOk' => $todoOk,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public function db()
    {
        $this->bloquearSiInstalado();
        $datos = $_SESSION['install']['db'] ?? [
            'db_host' => 'localhost',
            'db_port' => '3306',
            'db_name' => 'mesa_partes_lurin',
            'db_user' => 'root',
            'db_pass' => '',
        ];
        $this->view('install/db', [
            'datos' => $datos,
            'error' => $_SESSION['install']['db_error'] ?? null,
            'csrf_token' => Csrf::token(),
        ]);
        unset($_SESSION['install']['db_error']);
    }

    public function dbPost()
    {
        $this->bloquearSiInstalado();
        $request = new Request();
        $datos = $request->all();

        if (!Csrf::checkRequest($datos)) {
            $_SESSION['install']['db_error'] = 'Token CSRF inválido. Recargue la página.';
            $this->redirect('/install/db');
            return;
        }

        $host = trim((string) ($datos['db_host'] ?? 'localhost'));
        $port = (int) ($datos['db_port'] ?? 3306);
        $name = trim((string) ($datos['db_name'] ?? ''));
        $user = trim((string) ($datos['db_user'] ?? 'root'));
        $pass = (string) ($datos['db_pass'] ?? '');

        if ($host === '' || $name === '' || $user === '') {
            $_SESSION['install']['db_error'] = 'Host, nombre de base de datos y usuario son obligatorios.';
            $this->redirect('/install/db');
            return;
        }

        try {
            $mysqli = @new \mysqli($host, $user, $pass, '', $port);
            if ($mysqli->connect_errno) {
                throw new \RuntimeException('No se pudo conectar al servidor MySQL: ' . $mysqli->connect_error);
            }
            $mysqli->set_charset('utf8mb4');
            $safeName = str_replace('`', '``', $name);
            if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$safeName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                throw new \RuntimeException('No se pudo crear la base de datos: ' . $mysqli->error);
            }
            if (!$mysqli->select_db($name)) {
                throw new \RuntimeException('No se pudo seleccionar la base de datos: ' . $mysqli->error);
            }
            $mysqli->close();
        } catch (\Throwable $e) {
            $_SESSION['install']['db_error'] = $e->getMessage();
            $this->redirect('/install/db');
            return;
        }

        $_SESSION['install']['db'] = [
            'db_host' => $host,
            'db_port' => (string) $port,
            'db_name' => $name,
            'db_user' => $user,
            'db_pass' => $pass,
        ];
        $this->redirect('/install/env');
    }

    public function env()
    {
        $this->bloquearSiInstalado();
        if (empty($_SESSION['install']['db'])) {
            $this->redirect('/install/db');
            return;
        }
        $datos = $_SESSION['install']['env'] ?? [
            'app_env' => 'local',
            'app_timezone' => 'America/Lima',
            'app_url' => $this->detectarUrl(),
            'mail_habilitado' => 'false',
            'mail_host' => '',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => '',
            'mail_from_name' => 'Mesa de Partes Lurín',
            'mail_mesa_partes' => '',
            'api_decolecta_token' => '',
        ];
        $this->view('install/env', [
            'datos' => $datos,
            'error' => $_SESSION['install']['env_error'] ?? null,
            'csrf_token' => Csrf::token(),
        ]);
        unset($_SESSION['install']['env_error']);
    }

    public function envPost()
    {
        $this->bloquearSiInstalado();
        $request = new Request();
        $datos = $request->all();

        if (!Csrf::checkRequest($datos)) {
            $_SESSION['install']['env_error'] = 'Token CSRF inválido. Recargue la página.';
            $this->redirect('/install/env');
            return;
        }

        $_SESSION['install']['env'] = [
            'app_env' => trim((string) ($datos['app_env'] ?? 'local')),
            'app_timezone' => trim((string) ($datos['app_timezone'] ?? 'America/Lima')),
            'app_url' => rtrim(trim((string) ($datos['app_url'] ?? $this->detectarUrl())), '/'),
            'mail_habilitado' => !empty($datos['mail_habilitado']) ? 'true' : 'false',
            'mail_host' => trim((string) ($datos['mail_host'] ?? '')),
            'mail_port' => trim((string) ($datos['mail_port'] ?? '587')),
            'mail_username' => trim((string) ($datos['mail_username'] ?? '')),
            'mail_password' => (string) ($datos['mail_password'] ?? ''),
            'mail_encryption' => trim((string) ($datos['mail_encryption'] ?? 'tls')),
            'mail_from_address' => trim((string) ($datos['mail_from_address'] ?? '')),
            'mail_from_name' => trim((string) ($datos['mail_from_name'] ?? 'Mesa de Partes Lurín')),
            'mail_mesa_partes' => trim((string) ($datos['mail_mesa_partes'] ?? '')),
            'api_decolecta_token' => trim((string) ($datos['api_decolecta_token'] ?? '')),
        ];

        $this->redirect('/install/admin');
    }

    public function admin()
    {
        $this->bloquearSiInstalado();
        if (empty($_SESSION['install']['db']) || empty($_SESSION['install']['env'])) {
            $this->redirect('/install/db');
            return;
        }
        $datos = $_SESSION['install']['admin'] ?? [];
        $this->view('install/admin', [
            'datos' => $datos,
            'error' => $_SESSION['install']['admin_error'] ?? null,
            'csrf_token' => Csrf::token(),
            'tiposDocumento' => ['DNI','CE','RUC','Pasaporte','Otro'],
        ]);
        unset($_SESSION['install']['admin_error']);
    }

    public function adminPost()
    {
        $this->bloquearSiInstalado();
        $request = new Request();
        $datos = $request->all();

        if (!Csrf::checkRequest($datos)) {
            $_SESSION['install']['admin_error'] = 'Token CSRF inválido. Recargue la página.';
            $this->redirect('/install/admin');
            return;
        }

        // Validaciones básicas
        $tipoDoc = (string) ($datos['tipo_documento'] ?? 'DNI');
        $numDoc = trim((string) ($datos['numero_documento'] ?? ''));
        $nombres = trim((string) ($datos['nombres'] ?? ''));
        $paterno = trim((string) ($datos['apellido_paterno'] ?? ''));
        $materno = trim((string) ($datos['apellido_materno'] ?? ''));
        $email = trim((string) ($datos['email'] ?? ''));
        $telefono = trim((string) ($datos['telefono'] ?? ''));
        $direccion = trim((string) ($datos['direccion'] ?? ''));
        $username = trim((string) ($datos['username'] ?? ''));
        $password = (string) ($datos['password'] ?? '');
        $passwordConfirm = (string) ($datos['password_confirm'] ?? '');

        $errores = [];
        if ($numDoc === '') $errores[] = 'Número de documento obligatorio.';
        if ($nombres === '') $errores[] = 'Nombres obligatorios.';
        if ($paterno === '') $errores[] = 'Apellido paterno obligatorio.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Correo electrónico inválido.';
        if (strlen($username) < 4) $errores[] = 'El usuario debe tener al menos 4 caracteres.';
        if ($password !== $passwordConfirm) $errores[] = 'Las contraseñas no coinciden.';
        if (strlen($password) < 8) $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        if (!preg_match('/[A-Z]/', $password)) $errores[] = 'La contraseña debe incluir una mayúscula.';
        if (!preg_match('/[0-9]/', $password)) $errores[] = 'La contraseña debe incluir un número.';
        if (!preg_match('/[^A-Za-z0-9]/', $password)) $errores[] = 'La contraseña debe incluir un carácter especial.';

        if (!empty($errores)) {
            $_SESSION['install']['admin'] = $datos;
            $_SESSION['install']['admin_error'] = implode(' ', $errores);
            $this->redirect('/install/admin');
            return;
        }

        $db = $_SESSION['install']['db'];
        $env = $_SESSION['install']['env'];

        // 1. Escribir .env
        $this->escribirEnv($db, $env);

        // Cargar las nuevas variables en el entorno actual para que Conexion
        // (que lee config/database.php vía env()) apunte a la BD recién creada.
        try {
            $dotenv = \Src\Core\Dotenv::createMutable($this->baseDir);
            $dotenv->load();
        } catch (\Throwable $e) {
        }

        // 2. Importar esquema si no existe
        try {
            $mysqli = new \mysqli($db['db_host'], $db['db_user'], $db['db_pass'], $db['db_name'], (int) $db['db_port']);
            if ($mysqli->connect_errno) {
                throw new \RuntimeException('No se pudo conectar: ' . $mysqli->connect_error);
            }
            $mysqli->set_charset('utf8mb4');

            $check = $mysqli->query("SHOW TABLES LIKE 'roles'");
            $existe = $check && $check->num_rows > 0;
            if ($check) $check->free();

            if (!$existe) {
                $sql = file_get_contents($this->baseDir . DIRECTORY_SEPARATOR . 'db.sql');
                if ($sql === false) {
                    throw new \RuntimeException('No se pudo leer db.sql');
                }
                if (!$mysqli->multi_query($sql)) {
                    throw new \RuntimeException('Error al importar esquema: ' . $mysqli->error);
                }
                do {
                    if ($res = $mysqli->store_result()) {
                        $res->free();
                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
                if ($mysqli->errno) {
                    throw new \RuntimeException('Error al importar esquema: ' . $mysqli->error);
                }
            }

            // 3. Crear usuario administrador
            // Reiniciar conexión PDO (Conexion cachea estático). Forzar nueva instancia no posible;
            // pero Conexion usa estático $pdo; si ya se conectó antes, podría ser null. Aquí null hasta ahora.
            $personaDatos = [
                'tipo_documento' => $tipoDoc,
                'numero_documento' => $numDoc,
                'nombres' => $nombres,
                'apellido_paterno' => $paterno,
                'apellido_materno' => $materno,
                'email' => $email,
                'telefono' => $telefono,
                'direccion' => $direccion,
            ];
            $usuarioDatos = [
                'username' => $username,
                'password' => $password,
                'id_area' => null,
            ];

            // Obtener id_rol ADMINISTRADOR
            $stmt = $mysqli->prepare("SELECT id_rol FROM roles WHERE nombre = 'ADMINISTRADOR' LIMIT 1");
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();
            if (!$row) {
                throw new \RuntimeException('No se encontró el rol ADMINISTRADOR en la base de datos.');
            }
            $idRol = (int) $row['id_rol'];

            $mysqli->close();

            // Usar modelo (PDO) para crear el usuario
            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->crearUsuarioCompleto($personaDatos, $usuarioDatos, $idRol);
            if (empty($resultado['estatus'])) {
                throw new \RuntimeException('No se pudo crear el usuario: ' . ($resultado['error'] ?? 'error desconocido'));
            }
        } catch (\Throwable $e) {
            $_SESSION['install']['admin'] = $datos;
            $_SESSION['install']['admin_error'] = $e->getMessage();
            $this->redirect('/install/admin');
            return;
        }

        // 4. Marcar instalado
        file_put_contents(
            $this->baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'installed.lock',
            date('c') . "\n"
        );

        unset($_SESSION['install']);
        \App\Support\Flash::exito('Instalación completada. Ya puede iniciar sesión con el usuario creado.');
        $this->redirect('/login');
    }

    public function completado()
    {
        if (!$this->estaInstalado()) {
            $this->redirect('/install');
            return;
        }
        $this->view('install/completado', []);
    }

    private function detectarUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }

    private function escribirEnv(array $db, array $env): void
    {
        $contenido = "# Configuración de la base de datos\n";
        $contenido .= "DB_HOST=" . $db['db_host'] . "\n";
        $contenido .= "DB_NAME=" . $db['db_name'] . "\n";
        $contenido .= "DB_USER=" . $db['db_user'] . "\n";
        $contenido .= "DB_PASS=" . $db['db_pass'] . "\n";
        $contenido .= "DB_PORT=" . $db['db_port'] . "\n";
        $contenido .= "DB_CHARSET=utf8mb4\n";
        $contenido .= "API_DECOLECTA_SERVER=https://api.decolecta.com/v1/\n";
        $contenido .= "API_DECOLECTA_TOKEN=" . $env['api_decolecta_token'] . "\n";
        $contenido .= "\n# Entorno de la aplicación\n";
        $contenido .= "APP_ENV=" . $env['app_env'] . "\n";
        $contenido .= "APP_TIMEZONE=" . $env['app_timezone'] . "\n";
        $contenido .= "APP_URL=" . $env['app_url'] . "\n";
        $contenido .= "\n# Correo (E13)\n";
        $contenido .= "MAIL_HABILITADO=" . $env['mail_habilitado'] . "\n";
        $contenido .= "MAIL_HOST=" . $env['mail_host'] . "\n";
        $contenido .= "MAIL_PORT=" . $env['mail_port'] . "\n";
        $contenido .= "MAIL_USERNAME=" . $env['mail_username'] . "\n";
        $contenido .= "MAIL_PASSWORD=" . $env['mail_password'] . "\n";
        $contenido .= "MAIL_ENCRYPTION=" . $env['mail_encryption'] . "\n";
        $contenido .= "MAIL_FROM_ADDRESS=" . $env['mail_from_address'] . "\n";
        $contenido .= 'MAIL_FROM_NAME="' . $env['mail_from_name'] . "\"\n";
        $contenido .= "MAIL_MESA_PARTES=" . $env['mail_mesa_partes'] . "\n";

        file_put_contents($this->baseDir . DIRECTORY_SEPARATOR . '.env', $contenido);
    }
}
