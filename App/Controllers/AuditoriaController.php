<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Auditoria;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;

/**
 * TAREA E10-1: visor de auditoría del sistema.
 *
 * Solo el ADMINISTRADOR accede. Por defecto se consultan los últimos
 * 30 días; todos los filtros se combinan con AND.
 */
class AuditoriaController extends Controller
{
    use AuthorizesRequest;

    private const RolesPermitidos = Roles::Administracion;
    private const POR_PAGINA = 25;

    private Auditoria $model;

    public function __construct()
    {
        $this->model = new Auditoria();
    }

    public function index(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $request = new Request();

        $fechaHasta = trim((string) ($request->get('fecha_hasta') ?? ''));
        if (!$this->fechaValida($fechaHasta)) {
            $fechaHasta = date('Y-m-d');
        }

        $fechaDesde = trim((string) ($request->get('fecha_desde') ?? ''));
        if (!$this->fechaValida($fechaDesde)) {
            $fechaDesde = date('Y-m-d', strtotime($fechaHasta . ' -30 days'));
        }

        $filtros = [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'id_usuario'  => max(0, (int) ($request->get('id_usuario') ?? 0)),
            'modulo'      => trim((string) ($request->get('modulo') ?? '')),
            'accion'      => trim((string) ($request->get('accion') ?? '')),
            'texto'       => trim((string) ($request->get('texto') ?? '')),
        ];

        $pagina = max(1, (int) ($request->get('pagina') ?? 1));
        $total = $this->model->contar($filtros);
        $paginas = max(1, (int) ceil($total / self::POR_PAGINA));
        if ($pagina > $paginas) {
            $pagina = $paginas;
        }

        $this->view('auditoria.index', [
            'titulo'    => 'Auditoría del sistema',
            'filtros'   => $filtros,
            'registros' => $this->model->listar($filtros, $pagina, self::POR_PAGINA),
            'total'     => $total,
            'pagina'    => $pagina,
            'paginas'   => $paginas,
            'catalogos' => $this->model->catalogos(),
            'usuarios'  => $this->model->usuarios(),
        ], 'app');
    }

    public function detalle(string $id): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $registro = $this->model->obtener((int) $id);
        if ($registro === []) {
            $this->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            return;
        }

        $this->json(['success' => true, 'registro' => $registro]);
    }

    private function fechaValida(string $fecha): bool
    {
        $partes = explode('-', $fecha);

        return count($partes) === 3 && checkdate((int) $partes[1], (int) $partes[2], (int) $partes[0]);
    }
}
