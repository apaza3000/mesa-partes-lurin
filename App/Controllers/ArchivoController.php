<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Archivo;
use App\Models\Area;
use App\Support\Catalogos;
use App\Support\Flash;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

/**
 * TAREA E06-1: Archivo Central.
 *
 * Los expedientes atendidos se archivan con una ubicación física obligatoria
 * (caja, archivador y estante) y quedan consultables por año, área y código.
 */
class ArchivoController extends Controller
{
    use AuthorizesRequest;

    private const RolesPermitidos = Roles::MesaDePartes;

    private Archivo $model;
    private Area $areaModel;

    public function __construct()
    {
        $this->model = new Archivo();
        $this->areaModel = new Area();
    }

    public function index(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $request = new Request();

        $filtros = [
            'anio'    => max(0, (int) ($request->get('anio') ?? 0)),
            'id_area' => max(0, (int) ($request->get('id_area') ?? $request->get('area_id') ?? 0)),
            'codigo'  => trim((string) ($request->get('codigo') ?? '')),
            'estado'  => in_array((string) ($request->get('estado') ?? ''), ['Atendido', 'Archivado'], true)
                ? (string) $request->get('estado')
                : '',
        ];

        $mensajes = Flash::pull();

        $this->view('archivo.index', [
            'titulo'        => 'Archivo Central',
            'expedientes'   => $this->model->listarAtendidos($filtros),
            'filtros'       => $filtros,
            'areas'         => $this->areaModel->getAll(),
            'anios'         => $this->model->anios(),
            'csrf_token'    => \App\Support\Csrf::token(),
            'flash_success' => $this->primerMensaje($mensajes, Flash::Exito),
            'flash_error'   => $this->primerMensaje($mensajes, Flash::Error),
        ], 'app');
    }

    public function archivar(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $request = new Request();
        $datos = $request->all();
        $this->requerirCsrf($datos, '/archivo');

        $idExpediente = (int) ($datos['id_expediente'] ?? 0);
        if ($idExpediente <= 0) {
            Flash::error('Identificador de expediente inválido.');
            $this->redirect('/archivo');
            return;
        }

        $ubicacion = [
            'caja'       => Catalogos::sanear($datos['caja'] ?? '', 'expedientes.caja'),
            'archivador' => Catalogos::sanear($datos['archivador'] ?? '', 'expedientes.archivador'),
            'estante'    => Catalogos::sanear($datos['estante'] ?? '', 'expedientes.estante'),
        ];

        if ($ubicacion['caja'] === '' || $ubicacion['estante'] === '') {
            Flash::error('La caja y el estante son obligatorios para ubicar el expediente físico.');
            $this->redirect('/archivo');
            return;
        }

        $user = Session::user() ?? [];
        $idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);

        $resultado = $this->model->archivar($idExpediente, $ubicacion, $idUsuario);

        if ($resultado['success']) {
            Flash::exito($resultado['message']);
        } else {
            Flash::error($resultado['message']);
        }

        $this->redirect('/archivo');
    }

    /**
     * Reimpresión del cargo: reutiliza el PDF ya implementado en E03-1.
     */
    public function reimprimirCargo(string $id): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $this->redirect('/mesa-partes/cargo/' . (int) $id . '/pdf');
    }
}
