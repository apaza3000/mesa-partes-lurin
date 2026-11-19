<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Auditoria;
use App\Models\TipoDocumento;
use App\Support\Catalogos;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

/**
 * TAREA E07-1: CRUD del catálogo de tipos de documento.
 *
 * Los tipos inactivos dejan de aparecer en ventanilla y en el portal público
 * porque ambos consumen `TipoDocumento::obtenerActivos()`.
 */
class TipoDocumentoController extends Controller
{
    use AuthorizesRequest;

    private const RolesPermitidos = Roles::Administracion;

    private TipoDocumento $model;

    public function __construct()
    {
        $this->model = new TipoDocumento();
    }

    public function index(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $mensajes = Flash::pull();

        $this->view('tipo-documento.index', [
            'titulo'        => 'Tipos de documento',
            'tipos'         => $this->model->getAll(),
            'flash_success' => $this->primerMensaje($mensajes, Flash::Exito),
            'flash_error'   => $this->primerMensaje($mensajes, Flash::Error),
            'csrf_token'    => \App\Support\Csrf::token(),
        ], 'app');
    }

    public function crear(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $this->view('tipo-documento.crear', [
            'titulo'       => 'Nuevo tipo de documento',
            'errors'       => [],
            'datos_viejos' => [],
            'csrf_token'   => \App\Support\Csrf::token(),
        ], 'app');
    }

    public function store(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos, '/tipos-documento/nuevo');

        $datos = $this->normalizar($datos);
        $errors = $this->validar($datos);

        if ($errors === []) {
            $datos['estado'] = 'Activo';
            if ($this->model->create($datos)) {
                $this->auditar('crear', $this->model->ultimoId(), 'Creó el tipo de documento "' . $datos['nombre'] . '".');
                Flash::exito('Tipo de documento creado correctamente.');
                $this->redirect('/tipos-documento');
                return;
            }
            $errors['general'][] = 'No se pudo guardar el tipo de documento.';
        }

        $this->view('tipo-documento.crear', [
            'titulo'       => 'Nuevo tipo de documento',
            'errors'       => $errors,
            'datos_viejos' => $datos,
            'csrf_token'   => \App\Support\Csrf::token(),
        ], 'app');
    }

    public function editar(string $id): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $tipo = $this->model->getById((int) $id);
        if ($tipo === []) {
            Flash::error('El tipo de documento no existe.');
            $this->redirect('/tipos-documento');
            return;
        }

        $this->view('tipo-documento.editar', [
            'titulo'       => 'Editar tipo de documento',
            'tipo'         => $tipo,
            'errors'       => [],
            'datos_viejos' => $tipo,
            'csrf_token'   => \App\Support\Csrf::token(),
        ], 'app');
    }

    public function update(string $id): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $id = (int) $id;
        $actual = $this->model->getById($id);
        if ($actual === []) {
            Flash::error('El tipo de documento no existe.');
            $this->redirect('/tipos-documento');
            return;
        }

        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos, '/tipos-documento/' . $id . '/editar');

        $datos = $this->normalizar($datos);
        $errors = $this->validar($datos, $id);

        if ($errors === []) {
            $datos['estado'] = in_array($datos['estado'], Catalogos::EstadoSimple, true)
                ? $datos['estado']
                : $actual['estado'];

            if ($this->model->update($datos, $id)) {
                $this->auditar('editar', $id, 'Actualizó el tipo de documento "' . $datos['nombre'] . '".');
                Flash::exito('Tipo de documento actualizado correctamente.');
                $this->redirect('/tipos-documento');
                return;
            }
            $errors['general'][] = 'No se pudo actualizar el tipo de documento.';
        }

        $this->view('tipo-documento.editar', [
            'titulo'       => 'Editar tipo de documento',
            'tipo'         => array_merge($actual, $datos),
            'errors'       => $errors,
            'datos_viejos' => $datos,
            'csrf_token'   => \App\Support\Csrf::token(),
        ], 'app');
    }

    /**
     * Activa o inactiva un tipo. No se inactiva si ya tiene documentos.
     */
    public function cambiarEstado(string $id): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $id = (int) $id;
        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos, '/tipos-documento');

        $tipo = $this->model->getById($id);
        if ($tipo === []) {
            Flash::error('El tipo de documento no existe.');
            $this->redirect('/tipos-documento');
            return;
        }

        $nuevo = (string) ($datos['estado'] ?? '');
        if (!in_array($nuevo, Catalogos::EstadoSimple, true)) {
            Flash::error('Estado no válido.');
            $this->redirect('/tipos-documento');
            return;
        }

        if ($nuevo === 'Inactivo') {
            $total = $this->model->contarDocumentos($id);
            if ($total > 0) {
                Flash::error('No se puede inactivar el tipo "' . $tipo['nombre'] . '": tiene ' . $total . ' documento(s) asociado(s).');
                $this->redirect('/tipos-documento');
                return;
            }
        }

        if ($this->model->cambiarEstado($id, $nuevo)) {
            $this->auditar('cambiar-estado', $id, 'Cambió el estado del tipo "' . $tipo['nombre'] . '" a ' . $nuevo . '.');
            Flash::exito('Estado actualizado correctamente.');
        } else {
            Flash::error('No se pudo cambiar el estado del tipo de documento.');
        }

        $this->redirect('/tipos-documento');
    }

    private function normalizar(array $datos): array
    {
        return [
            'nombre'      => Catalogos::limpiar($datos['nombre'] ?? ''),
            'descripcion' => Catalogos::limpiar($datos['descripcion'] ?? ''),
            'estado'      => (string) ($datos['estado'] ?? 'Activo'),
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function validar(array $datos, ?int $exceptoId = null): array
    {
        $errors = [];

        $nombre = (string) ($datos['nombre'] ?? '');
        if ($nombre === '') {
            $errors['nombre'][] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($nombre) > Catalogos::limite('tipos_documento.nombre')) {
            $errors['nombre'][] = 'El nombre no debe exceder 100 caracteres.';
        } elseif ($this->model->existeNombre($nombre, $exceptoId)) {
            $errors['nombre'][] = 'Ya existe un tipo de documento con ese nombre.';
        }

        if (mb_strlen((string) ($datos['descripcion'] ?? '')) > Catalogos::limite('tipos_documento.descripcion')) {
            $errors['descripcion'][] = 'La descripción no debe exceder 255 caracteres.';
        }

        return $errors;
    }

    private function auditar(string $accion, ?int $idRegistro, string $descripcion): void
    {
        try {
            $usuario = Session::user() ?? [];
            (new Auditoria())->insertar(
                $usuario['id_usuario'] ?? null,
                'tipos_documento',
                $accion,
                'tipos_documento',
                $idRegistro,
                $descripcion,
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (\Throwable $e) {
            error_log('Auditoría de tipos de documento falló: ' . $e->getMessage());
        }
    }
}
