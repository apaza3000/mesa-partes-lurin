<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Auditoria;
use App\Models\Usuario;
use App\Support\Catalogos;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;
use Src\Core\Validator;
use App\Validators\UsuarioValidator;

class UsuarioController extends Controller
{
    use AuthorizesRequest;

    /** SEC-04: todo el módulo de usuarios es exclusivo de ADMINISTRADOR. */
    private const RolesPermitidos = Roles::Administracion;

    private Usuario $user;
    //private UsuarioServices $UsuarioServices;
    public function __construct()
    {
        $this->user = new Usuario();
    }

    public function index()
    {
        $this->requerirRol(self::RolesPermitidos);

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

    public function buscarPersonaAjax()
    {
        // SEC-04: endpoint de lookup de personas también exige sesión y rol.
        $this->requerirRol(self::RolesPermitidos);

        header('Content-Type: application/json');
        $tipo = $_GET['tipo'] ?? '';
        $numero = $_GET['numero'] ?? '';

        if (empty($tipo) || empty($numero)) {
            echo json_encode(['encontrado' => false]);
            return;
        }

        // Buscar en el modelo
        $persona = $this->user->obtenerPersonaPorDocumento($tipo, $numero);

        if ($persona) {
            // Verificar si ya tiene usuario
            $tieneUsuario = $this->user->tieneUsuarioAsignado($persona['id_persona']);
            echo json_encode([
                'encontrado' => true,
                'tiene_usuario' => $tieneUsuario,
                'persona' => $persona
            ]);
        } else {
            echo json_encode(['encontrado' => false]);
        }
        exit;


    }

    /*
    ==================================================
    FORMULARIO CREAR USUARIO
    ==================================================
    */
    //renderizar vista (New User)
    public function create(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        // Obtener áreas activas y roles para los combos del formulario
        $areas = $this->user->getAllArea();
        $roles = $this->user->getAllRol();

        // Renderizar la vista de creación
        $this->view('usuarios.crear', [
            'areas' => $areas,
            'roles' => $roles,
            'tiposDocumento' => Catalogos::TipoDocumento,
            'estados' => Catalogos::EstadoUsuario,
        ], 'app');
    }
    //guardar datos de formulario (New User)
    public function store()
    {
        $this->requerirRol(self::RolesPermitidos);

        $data = $this->sanearEntrada((new Request())->all());
        $this->requerirCsrf($data, '/usuarios/crear');

        $validator = new Validator();
        // Usando la clase de forma limpia gracias al 'use'
        $resultadoValidacion = UsuarioValidator::validar($data);
        if (!$resultadoValidacion['esValido']) {
            $this->view('usuarios.crear', [
                'errors' => $resultadoValidacion['errores'],
                'datos_viejos' => $data,
                'areas' => $this->user->getAllArea(),
                'roles' => $this->user->getAllRol()
            ], 'app');
            return;
        }

        $rules = [
            //datos de personas
            'tipo_documento' => ['required', 'in' => Catalogos::TipoDocumento],
            'numero_documento' => ['required', /*'unique' => ['personas', 'numero_documento'], */ 'maxLength' => Catalogos::limite('personas.numero_documento')],
            'nombres' => ['required', 'maxLength' => Catalogos::limite('personas.nombres')],
            'apellido_paterno' => ['required', 'maxLength' => Catalogos::limite('personas.apellido_paterno')],
            'apellido_materno' => ['required', 'maxLength' => Catalogos::limite('personas.apellido_materno')],
            'email' => ['nullable', 'email', 'maxLength' => Catalogos::limite('personas.email')],
            'telefono' => ['nullable', 'maxLength' => Catalogos::limite('personas.telefono')],
            'direccion' => ['nullable', 'string', 'maxLength' => Catalogos::limite('personas.direccion')],
            //datos de usuarios
            'id_area' => ['nullable', 'exists' => ['areas', 'id_area']],
            'username' => ['required', 'unique' => ['usuarios', 'username'], 'maxLength' => Catalogos::limite('usuarios.username')],
            'password' => ['required', 'minLength' => 6],
            //datos de rol
            'id_rol' => ['required', 'exists' => ['roles', 'id_rol']]
        ];
        // Validar inputs del formulario con Validator
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
            $this->view('usuarios.crear', [
                'errors' => ['db' => 'No se pudo guardar el usuario en la base de datos: ' . $res['error']],
                'datos_viejos' => $data,
                'areas' => $this->user->getAllArea(),
                'roles' => $this->user->getAllRol()
            ], 'app');
            return;
        }

        // 4. Auditoría y redirección exitosa
        $this->auditar(
            'crear',
            (int) $res['id'],
            'Creó el usuario "' . $normalized['usuario']['username'] . '".'
        );

        Flash::exito('Usuario creado correctamente.');
        $this->redirect('/usuarios');
    }



    /*
    ==================================================
    EDITAR Y ACTUALIZAR USUARIO
    ==================================================
    */
    public function editar(int $id)
    {
        $this->requerirRol(self::RolesPermitidos);

        $usuario = $this->user->obtenerPorId($id);

        if (!$usuario) {
            Flash::error('Usuario no encontrado');
            return $this->redirect('/usuarios');
        }

        $areas = $this->user->getAllArea();
        $roles = $this->user->getAllRol();

        // Renderizar usando el método del framework
        $this->view('usuarios.editar', [
            'usuario' => $usuario,
            'areas' => $areas,
            'roles' => $roles,
            'tiposDocumento' => Catalogos::TipoDocumento,
            'estados' => Catalogos::EstadoUsuario,
        ], 'app');
    }

    public function actualizar(int $id)
    {
        $this->requerirRol(self::RolesPermitidos);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $this->redirect('/usuarios');
        }

        $datos = $this->sanearEntrada((new Request())->all());
        $this->requerirCsrf($datos, '/usuarios/' . $id . '/editar');

        if ($id <= 0) {
            Flash::error('ID de usuario no válido');
            return $this->redirect('/usuarios');
        }

        $validator = new Validator();
        $rules = [
            'tipo_documento' => ['required', 'in' => Catalogos::TipoDocumento],
            'numero_documento' => ['required', 'maxLength' => Catalogos::limite('personas.numero_documento')],
            'nombres' => ['required', 'maxLength' => Catalogos::limite('personas.nombres')],
            'apellido_paterno' => ['required', 'maxLength' => Catalogos::limite('personas.apellido_paterno')],
            'apellido_materno' => ['required', 'maxLength' => Catalogos::limite('personas.apellido_materno')],
            'email' => ['nullable', 'email', 'maxLength' => Catalogos::limite('personas.email')],
            'username' => ['required', 'maxLength' => Catalogos::limite('usuarios.username')],
            'id_area' => ['nullable', 'exists' => ['areas', 'id_area']],
            'estado' => ['required', 'in' => Catalogos::EstadoUsuario],
            'id_rol' => ['required', 'exists' => ['roles', 'id_rol']],
        ];

        if (!$validator->validate($rules, $datos)) {
            $this->view('usuarios.editar', [
                'errors' => $validator->getErrors(),
                'usuario' => array_merge($this->user->obtenerPorId($id) ?? [], $datos),
                'roles' => $this->user->getAllRol(),
                'areas' => $this->user->getAllArea(),
                'tiposDocumento' => Catalogos::TipoDocumento,
                'estados' => Catalogos::EstadoUsuario,
            ], 'app');
            return;
        }

        $datosPersona = [
            'tipo_documento' => $datos['tipo_documento'],
            'numero_documento' => $datos['numero_documento'],
            'nombres' => $datos['nombres'],
            'apellido_paterno' => $datos['apellido_paterno'],
            'apellido_materno' => $datos['apellido_materno'],
            'email' => $datos['email'],
        ];

        $datosUsuario = [
            'username' => $datos['username'],
            'id_area' => !empty($datos['id_area']) ? (int) $datos['id_area'] : null,
            'estado' => $datos['estado'],
        ];

        $idRol = (int) $datos['id_rol'];

        $exito = $this->user->actualizarCompleto($id, $datosPersona, $datosUsuario, $idRol);

        if ($exito) {
            $this->auditar('editar', $id, 'Actualizó el usuario "' . $datosUsuario['username'] . '".');
            Flash::exito('Usuario actualizado correctamente.');
            return $this->redirect('/usuarios');
        }

        $this->view('usuarios.editar', [
            'errors' => ['db' => 'Ocurrió un error al intentar actualizar el usuario.'],
            'usuario' => array_merge(['id_usuario' => $id], $datosPersona, $datosUsuario, ['id_rol' => $idRol]),
            'roles' => $this->user->getAllRol(),
            'areas' => $this->user->getAllArea(),
            'tiposDocumento' => Catalogos::TipoDocumento,
            'estados' => Catalogos::EstadoUsuario,
        ], 'app');
    }

    /*
    ==================================================
    ACTIVAR / DESACTIVAR USUARIO  (FAT-02)
    ==================================================
    */
    public function estado($id)
    {
        $id = (int) $id;
        $this->requerirRol(self::RolesPermitidos);

        $datos = (new Request())->all();
        $this->requerirCsrf($datos, '/usuarios/' . $id . '/editar');

        $nuevoEstado = trim((string) ($datos['estado'] ?? ''));

        if ($id <= 0 || !Catalogos::es(Catalogos::EstadoUsuario, $nuevoEstado)) {
            Flash::error('Estado no válido para el usuario.');
            return $this->redirect('/usuarios');
        }

        $usuario = $this->user->obtenerPorId($id);
        if (!$usuario) {
            Flash::error('Usuario no encontrado.');
            return $this->redirect('/usuarios');
        }

        $actual = Session::user() ?? [];
        if ($nuevoEstado !== 'Activo' && (int) ($actual['id_usuario'] ?? 0) === $id) {
            Flash::error('No puede desactivar su propia cuenta.');
            return $this->redirect('/usuarios/' . $id);
        }

        if (!$this->user->cambiarEstado($id, $nuevoEstado)) {
            Flash::error('No se pudo actualizar el estado del usuario.');
            return $this->redirect('/usuarios/' . $id);
        }

        $this->auditar('editar', $id, 'Cambió el estado del usuario "' . $usuario['username'] . '" a ' . $nuevoEstado . '.');
        Flash::exito('Estado del usuario actualizado a ' . $nuevoEstado . '.');
        return $this->redirect('/usuarios');
    }

    /*
    ==================================================
    ELIMINAR USUARIO
    ==================================================
    */
    public function delete(int $id)
    {
        $this->requerirRol(self::RolesPermitidos);

        $datos = (new Request())->all();
        $this->requerirCsrf($datos, '/usuarios');

        if ($id > 0) {
            $actual = Session::user() ?? [];
            if ((int) ($actual['id_usuario'] ?? 0) === $id) {
                Flash::error('No puede eliminar su propia cuenta.');
                return $this->redirect('/usuarios');
            }

            // SEC-04: borrado físico imposible si hay trazabilidad asociada
            // (FK RESTRICT en derivaciones/observaciones); se desactiva.
            if ($this->user->tieneRegistrosAsociados($id)) {
                if ($this->user->cambiarEstado($id, 'Inactivo')) {
                    $this->auditar('eliminar', $id, 'Desactivó el usuario #' . $id . ' (tiene registros asociados).');
                    Flash::exito('El usuario tiene expedientes asociados: se desactivó en lugar de eliminarse.');
                    return $this->redirect('/usuarios');
                }
            } elseif ($this->user->eliminar($id)) {
                $this->auditar('eliminar', $id, 'Eliminó físicamente el usuario #' . $id . '.');
                Flash::exito('Usuario eliminado correctamente.');
                return $this->redirect('/usuarios');
            }
        }

        Flash::error('No se pudo eliminar el usuario.');
        return $this->redirect('/usuarios');
    }
    /*
    ==================================================
    VISTA (VER MAS DETALLES DE USUARIO)
    ==================================================
    */
    public function show(int $id)
    {
        $this->requerirRol(self::RolesPermitidos);

        $usuario = $this->user->obtenerAllPorId($id);

        if (!$usuario) {
            Flash::error('Usuario no encontrado');
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
                'tipo_documento' => mb_strtoupper(trim($data['tipo_documento'] ?? 'DNI')),
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

    /** SEC-07 / VAL-02: entrada libre sin HTML ni caracteres rechazados por la BD. */
    private function sanearEntrada(array $data): array
    {
        $campos = [
            'nombres' => 'personas.nombres',
            'apellido_paterno' => 'personas.apellido_paterno',
            'apellido_materno' => 'personas.apellido_materno',
            'email' => 'personas.email',
            'telefono' => 'personas.telefono',
            'direccion' => 'personas.direccion',
            'numero_documento' => 'personas.numero_documento',
            'username' => 'usuarios.username',
        ];

        foreach ($campos as $campo => $columna) {
            if (array_key_exists($campo, $data)) {
                $data[$campo] = Catalogos::sanear($data[$campo], $columna);
            }
        }

        return $data;
    }

    /** OPT-03 / §3.5: toda escritura de usuarios queda auditada. */
    private function auditar(string $accion, ?int $idRegistro, string $descripcion): void
    {
        try {
            $usuario = Session::user() ?? [];
            (new Auditoria())->insertar(
                $usuario['id_usuario'] ?? null,
                'usuarios',
                $accion,
                'usuarios',
                $idRegistro,
                $descripcion,
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (\Throwable $e) {
            error_log('Auditoría de usuarios falló: ' . $e->getMessage());
        }
    }
}
