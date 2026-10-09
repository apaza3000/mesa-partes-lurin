<?php

namespace App\Controllers;
//use App\Services\UsuarioServices;

use App\Models\Auditoria;
use App\Models\Usuario;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;
use Src\Core\Validator;

class UsuarioController extends Controller
{
    private Usuario $user;
    //private UsuarioServices $UsuarioServices;
    public function __construct()
    {
        $this->user = new Usuario();
    }

    public function index()
    {
        if (!Session::isAuthenticated()) {
            return $this->redirect("/login");
        }

        // 1. Paginación
        $porPagina = 10;
        $paginaActual = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($paginaActual < 1) {
            $paginaActual = 1;
        }

        // 2. Cálculos de páginas
        $totalUsuarios = $this->user->getTotalUsuarios();
        $totalPaginas = (int) ceil($totalUsuarios / $porPagina);

        if ($paginaActual > $totalPaginas && $totalPaginas > 0) {
            $paginaActual = $totalPaginas;
        }

        // 3. Obtener los datos (se ejecutan una sola vez)
        $usuarios = $this->user->getUsuariosPaginados($paginaActual, $porPagina);
        $roles = $this->user->getAllRol();
        $areas = $this->user->getAllArea();

        // 4. Enviar las variables previamente calculadas a la vista
        $this->view('usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'areas' => $areas,
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas
        ], 'app');
    }
    /*
    ==================================================
    FORMULARIO CREAR USUARIO
    ==================================================
    */
    //renderizar vista (New User)
    public function create(): void
    {
        // Obtener áreas activas y roles para los combos del formulario
        $areas = $this->user->getAllArea();
        $roles = $this->user->getAllRol();

        // Renderizar la vista de creación
        $this->view('usuarios.crear', [
            'areas' => $areas,
            'roles' => $roles
        ], 'app');
    }
    //guardar datos de formulario (New User)
    public function store()
    {
        if (!Session::isAuthenticated()) {
            return $this->redirect("/login");
        }
        $data = (new Request())->all();
        $validator = new Validator();

        $rules = [
            //datos de personas
            'tipo_documento' => ['required', 'in' => ['DNI', 'CE', 'RUC', 'Pasaporte', 'Otro']],
            'numero_documento' => ['required', 'unique' => ['personas', 'numero_documento'], 'maxLength' => 20],
            'nombres' => ['required', 'maxLength' => 100],
            'apellido_paterno' => ['required', 'maxLength' => 100],
            'apellido_materno' => ['required', 'maxLength' => 100],
            'email' => ['nullable', 'email'],
            'telefono' => ['nullable', 'maxLength' => 30],
            'direccion' => ['nullable', 'string', 'maxLength' => 255],
            //datos de usuarios
            'id_area' => ['nullable', 'exists' => ['areas', 'id_area']],
            'username' => ['required', 'unique' => ['usuarios', 'username'], 'maxlength' => 50],
            'password' => ['required', 'minLength' => 6],
            //datos de rol
            'id_rol' => ['required', 'exists' => ['roles', 'id_rol']]
        ];
        //Validar inputs del formulario
        if (!$validator->validate($rules, $data)) {
            $this->view('usuarios.crear', [
                'errors' => $validator->getErrors(),
                'datos_viejos' => $data,
                'areas' => $this->user->getAllArea(),
                'roles' => $this->user->getAllRol()
            ], 'app');
            return;
        }

        $normalized = $this->normalize($data);
        $res = $this->user->crearUsuarioCompleto($normalized['persona'], $normalized['usuario'], $normalized['id_rol']);
        if (!$res['estatus']) {
            echo '<pre>';
            print_r($normalized);
            print_r($res);
            return;
            $this->view('usuarios.crear', [
                'errors' => ['db' => 'No se pudo guardar el usuario en la base de datos con mensaje: ' . $res['error']],
                'datos_viejos' => $data,
                'areas' => $this->user->getAllArea(),
                'roles' => $this->user->getAllRol()
            ], 'app');
            return;
        }

        $user = Session::user();
        $auditoria = new Auditoria();
        $auditoria->insertar($user['id_usuario'], 'usuarios', 'crear', 'usuarios', $res['id'], 'registro completo de usuarios', $_SERVER['REMOTE_ADDR'] ?? null);

        $_SESSION['usuario_mensaje'] = 'Usuario creado correctamente.';
        $this->redirect('/usuarios');
    }



    /*
    ==================================================
    EDITAR Y ACTUALIZAR USUARIO
    ==================================================
    */
    public function editar(int $id)
    {
        $usuario = $this->user->obtenerPorId($id);

        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado';
            return $this->redirect('/usuarios');
        }

        $areas = $this->user->getAllArea();
        $roles = $this->user->getAllRol();

        // Renderizar usando el método del framework
        $this->view('usuarios.editar', [
            'usuario' => $usuario,
            'areas' => $areas,
            'roles' => $roles
        ], 'app');
    }

    public function actualizar(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if ($id <= 0) {
                $_SESSION['error'] = 'ID de usuario no válido';
                return $this->redirect('/usuarios');
            }

            $datosPersona = [
                'tipo_documento' => trim($_POST['tipo_documento'] ?? ''),
                'numero_documento' => trim($_POST['numero_documento'] ?? ''),
                'nombres' => trim($_POST['nombres'] ?? ''),
                'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
            ];

            $datosUsuario = [
                'username' => trim($_POST['username'] ?? ''),
                'id_area' => !empty($_POST['id_area']) ? (int) $_POST['id_area'] : null,
                'estado' => $_POST['estado'] ?? 'Activo',
            ];

            $idRol = isset($_POST['id_rol']) ? (int) $_POST['id_rol'] : 0;

            $exito = $this->user->actualizarCompleto($id, $datosPersona, $datosUsuario, $idRol);

            if ($exito) {
                $_SESSION['usuario_mensaje'] = 'Usuario actualizado correctamente.';
                return $this->redirect('/usuarios');
            } else {
                $this->view('usuarios.editar', [
                    'errors' => ['db' => 'Ocurrió un error al intentar actualizar el usuario.'],
                    'usuario' => array_merge(['id_usuario' => $id], $datosPersona, $datosUsuario, ['id_rol' => $idRol]),
                    'roles' => $this->user->getAllRol(),
                    'areas' => $this->user->getAllArea()
                ], 'app');
            }
        }
    }

    /*
    ==================================================
    ELIMINAR USUARIO
    ==================================================
    */
    public function delete(int $id)
    {
        if ($id > 0) {
            $eliminado = $this->user->eliminar($id);

            if ($eliminado) {
                $_SESSION['usuario_mensaje'] = 'Usuario eliminado correctamente.';
                return $this->redirect('/usuarios');
            }
        }

        $_SESSION['error'] = 'No se pudo eliminar el usuario.';
        return $this->redirect('/usuarios');
    }
    /*
    ==================================================
    VISTA (VER MAS DETALLES DE USUARIO)
    ==================================================
    */
    public function show(int $id)
    {
        $usuario = $this->user->obtenerAllPorId($id);

        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado';
            return $this->redirect('/usuarios');
        }

        // Renderizar usando el método del framework
        $this->view('usuarios.show', [
            'usuario' => $usuario,
        ], 'app');
    }
    /*
    ==================================================
    METODOS PRIVADOS VALIDATE
    ==================================================
    */


    private function normalize(array $data): array
    {
        return [
            'persona' => [
                'tipo_documento' => trim($data['tipo_documento'] ?? 'DNI'),
                'numero_documento' => trim($data['numero_documento'] ?? ''),
                'nombres' => trim($data['nombres'] ?? ''),
                'apellido_paterno' => trim($data['apellido_paterno'] ?? ''),
                'apellido_materno' => trim($data['apellido_materno'] ?? ''),
                'email' => trim($data['email'] ?? '')
            ],
            'usuario' => [
                'id_area' => (isset($data['id_area']) && $data['id_area'] != '') ? $data['id_area'] : null,
                'username' => trim($data['username'] ?? ''),
                'password' => $data['password'] ?? ''
            ],
            'id_rol' => (int) ($data['id_rol'] ?? 0)
        ];
    }
}
