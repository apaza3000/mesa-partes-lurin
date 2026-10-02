<?php

namespace App\Controllers;
//use App\Services\UsuarioServices;
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
    //store
    //guardar datos de formulario (New User)
    public function store(): void
    {
        $data = (new Request())->all();
        $validator = new Validator();

        // 1. Validar inputs del formulario
        if (!$this->validate($validator, $data)) {
            $this->view('usuarios.crear', [
                'errors' => $validator->getErrors(),
                'datos_viejos' => $data,
                'areas' => $this->user->getAllArea(), // Corregido: se usa $this->user
                'roles' => $this->user->getAllRol()
            ], 'app');
            return;
        }

        $normalized = $this->normalize($data);

        // 2. Intentar guardar pasando los 3 arreglos esperados por el Modelo
        if (!$this->user->crearUsuarioCompleto($normalized['persona'], $normalized['usuario'], $normalized['id_rol'])) {
            $this->view('usuarios.crear', [
                'errors' => ['db' => 'No se pudo guardar el usuario en la base de datos.'],
                'datos_viejos' => $data,
                'areas' => $this->user->getAllArea(), // Corregido: se usa $this->user
                'roles' => $this->user->getAllRol()
            ], 'app');
            return;
        }

        $_SESSION['usuario_mensaje'] = 'Usuario creado correctamente.';
        $this->redirect('/usuarios');
    }

    private function validate(Validator $validator, array $data): bool
    {
        $rules = [
            'tipo_documento' => ['required', 'string', 'in' => ['DNI', 'CE', 'Pasaporte']],
            'numero_documento' => ['required', 'string', 'maxLength' => 20],
            'nombres' => ['required', 'string', 'maxLength' => 100],
            'apellido_paterno' => ['required', 'string', 'maxLength' => 100],
            'apellido_materno' => ['nullable', 'string', 'maxLength' => 100],
            'email' => ['required', 'email', 'maxLength' => 150],
            'username' => ['required', 'string', 'maxLength' => 50],
            'password' => ['required', 'string', 'minLength' => 8],
            'id_area' => ['required'],
            'id_rol' => ['required'],
        ];

        return $validator->validate($rules, $data);
    }

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
                'id_area' => (int) ($data['id_area'] ?? 0),
                'username' => trim($data['username'] ?? ''),
                'password' => $data['password'] ?? '' // Se deja texto plano aquí, el modelo hace el hash
            ],
            'id_rol' => (int) ($data['id_rol'] ?? 0)
        ];
    }
}
