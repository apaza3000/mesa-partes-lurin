<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Auditoria;
use App\Models\Rol;
use App\Support\Catalogos;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;
use Src\Core\Validator;

class RolController extends Controller
{
    use AuthorizesRequest;

    /** SEC-02: la gestión de roles es exclusiva de ADMINISTRADOR. */
    private const RolesPermitidos = Roles::Administracion;

    private Rol $rol;

    public function __construct()
    {
        $this->rol = new Rol();
    }

    public function index()
    {
        $this->requerirRol(self::RolesPermitidos);
        $this->view('roles.index', ['roles' => $this->rol->getAll()], 'app');
    }

    public function create()
    {
        $this->requerirRol(self::RolesPermitidos);
        $this->view('roles.crear', [], 'app');
    }

    public function edit(int|string $id)
    {
        $this->requerirRol(self::RolesPermitidos);
        $this->view('roles.editar', ['rol' => $this->rol->getById($id)], 'app');
    }

    public function store()
    {
        $this->requerirRol(self::RolesPermitidos);

        $data = $this->sanearEntrada((new Request())->all());
        $this->requerirCsrf($data, '/roles/create');

        $validator = new Validator();

        if (!$this->validate($validator, $data)) {
            $this->view('roles.crear', [
                'errors' => $validator->getErrors(),
                'datos_viejos' => $data,
            ], 'app');
            return;
        }

        if (!$this->rol->create($this->normalize($data))) {
            $this->view('roles.crear', [
                'errors' => ['db' => 'No se pudo guardar el rol.'],
                'datos_viejos' => $data,
            ], 'app');
            return;
        }

        Flash::exito('Rol creado correctamente.');
        $this->auditar('crear', 'roles', $this->rol->ultimoId(), 'Creó el rol "' . $data['nombre'] . '".');
        $this->redirect('/roles');
    }

    public function update(int|string $id)
    {
        $this->requerirRol(self::RolesPermitidos);

        $id = (int) $id;
        $data = $this->sanearEntrada((new Request())->all());
        $this->requerirCsrf($data, '/roles/' . $id . '/editar');

        $rolActual = $this->rol->getById($id);

        if (!$rolActual) {
            Flash::error('El rol no existe.');
            $this->redirect('/roles');
        }

        $validator = new Validator();
        if (!$this->validate($validator, $data)) {
            $this->view('roles.editar', [
                'rol' => array_merge($rolActual, $data),
                'errors' => $validator->getErrors(),
            ], 'app');
            return;
        }

        if (!$this->rol->update($this->normalize($data), $id)) {
            $this->view('roles.editar', [
                'rol' => array_merge($rolActual, $data),
                'errors' => ['db' => 'No se pudo actualizar el rol.'],
            ], 'app');
            return;
        }

        Flash::exito('Rol actualizado correctamente.');
        $this->auditar('editar', 'roles', $id, 'Actualizó el rol "' . $data['nombre'] . '".');
        $this->redirect('/roles');
    }

    public function delete(int|string $id)
    {
        $this->requerirRol(self::RolesPermitidos);

        $data = (new Request())->all();
        $this->requerirCsrf($data, '/roles');

        $id = (int) $id;
        $rolActual = $this->rol->getById($id);

        if (!$rolActual) {
            Flash::error('El rol no existe.');
        } elseif (in_array($rolActual['nombre'], Roles::Todos, true)) {
            Flash::error('Los roles base del sistema no pueden eliminarse.');
        } elseif ($this->rol->delete($id)) {
            Flash::exito('Rol eliminado correctamente.');
            $this->auditar('eliminar', 'roles', $id, 'Eliminó el rol "' . $rolActual['nombre'] . '".');
        } else {
            Flash::error('No se pudo eliminar el rol. Puede tener usuarios asociados.');
        }

        $this->redirect('/roles');
    }

    private function validate(Validator $validator, array $data): bool
    {
        $rules = [
            'nombre' => ['required', 'string', 'maxLength' => Catalogos::limite('roles.nombre')],
            'descripcion' => ['nullable', 'string', 'maxLength' => Catalogos::limite('roles.descripcion')],
            'estado' => ['required', 'string', 'in' => Catalogos::EstadoSimple],
        ];

        $ok = $validator->validate($rules, $data);

        // SEC-02: whitelist de caracteres del nombre (el rol es la clave de autorización).
        // Se comprueba aparte porque el validador del core no ofrece reglas regex.
        $nombre = (string) ($data['nombre'] ?? '');
        if ($nombre !== '' && !preg_match('/^[A-Z][A-Z0-9_]{2,49}$/', $nombre)) {
            Flash::error('El nombre del rol debe ir en mayúsculas, sin espacios ni acentos (mínimo 3 caracteres).');
            return false;
        }

        return $ok;
    }

    private function normalize(array $data): array
    {
        return [
            'nombre' => mb_strtoupper(trim($data['nombre'] ?? '')),
            'descripcion' => trim($data['descripcion'] ?? ''),
            'estado' => $data['estado'] ?? 'Activo',
        ];
    }

    /**
     * SEC-07 / SEC-02: el nombre del rol se imprime en cabecera y sidebar, por lo
     * que se sanea a la entrada (sin HTML, sin 4 bytes, en mayúsculas) además de
     * escaparse en la vista.
     */
    private function sanearEntrada(array $data): array
    {
        if (isset($data['nombre'])) {
            $data['nombre'] = mb_strtoupper(Catalogos::sanear($data['nombre'], 'roles.nombre'));
        }
        if (isset($data['descripcion'])) {
            $data['descripcion'] = Catalogos::sanear($data['descripcion'], 'roles.descripcion');
        }

        return $data;
    }

    private function auditar(string $accion, string $tabla, ?int $idRegistro, string $descripcion): void
    {
        try {
            $usuario = Session::user() ?? [];
            (new Auditoria())->insertar(
                $usuario['id_usuario'] ?? null,
                'roles',
                $accion,
                $tabla,
                $idRegistro,
                $descripcion,
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (\Throwable $e) {
            error_log('Auditoría de roles falló: ' . $e->getMessage());
        }
    }
}
