<?php

namespace App\Controllers;

use App\Controllers\Concerns\AuthorizesRequest;
use App\Models\Area;
use App\Models\Auditoria;
use App\Models\Reporte;
use App\Models\TipoDocumento;
use App\Support\Roles;
use Src\Core\Controller;
use Src\Core\Request;
use Src\Core\Session;

/**
 * TAREA E09-1: reportes documentarios con filtros y exportación.
 *
 * ADMINISTRADOR ve todo el universo; RESPONSABLE_AREA queda limitado
 * a los expedientes cuya derivación más reciente apunta a su área.
 */
class ReporteController extends Controller
{
    use AuthorizesRequest;

    private const RolesPermitidos = [Roles::ADMINISTRADOR, Roles::RESPONSABLE_AREA];

    private Reporte $model;
    private Area $areaModel;
    private TipoDocumento $tipoModel;

    public function __construct()
    {
        $this->model = new Reporte();
        $this->areaModel = new Area();
        $this->tipoModel = new TipoDocumento();
    }

    public function index(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $filtros = $this->filtros();

        $this->view('reportes.index', [
            'titulo'       => 'Reportes documentarios',
            'filtros'      => $filtros,
            'resumen'      => $this->model->resumen($filtros),
            'porDia'       => $this->model->porDia($filtros),
            'porArea'      => $this->model->porArea($filtros),
            'porTipo'      => $this->model->porTipo($filtros),
            'porEstado'    => $this->model->porEstado($filtros),
            'porCanal'     => $this->model->porCanal($filtros),
            'porPrioridad' => $this->model->porPrioridad($filtros),
            'slaPorArea'   => $this->model->slaPorArea($filtros),
            'detalle'      => $this->model->detalle($filtros, 200),
            'areas'        => $this->areaModel->getAll(),
            'tipos'        => $this->tipoModel->getAll(),
            'esAdmin'      => $this->esAdmin(),
            'slaDias'      => Reporte::SLA_DIAS,
        ], 'app');
    }

    public function exportarCsv(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $filtros = $this->filtros();
        $filas = $this->model->detalle($filtros, 20000);

        $this->auditar('exportar-csv', $filtros, count($filas));

        $nombre = 'reportes-' . date('Ymd-His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Cache-Control: no-store');

        $salida = fopen('php://output', 'w');
        // BOM UTF-8 para que Excel respete las tildes.
        fwrite($salida, "\xEF\xBB\xBF");

        fputcsv($salida, [
            'Código', 'Fecha registro', 'Remitente', 'Documento', 'Asunto',
            'Área actual', 'Tipo', 'Canal', 'Prioridad', 'Estado',
            'Fecha atención', 'Días', 'SLA',
        ], ';');

        foreach ($filas as $f) {
            fputcsv($salida, [
                $f['codigo_expediente'] ?? '',
                $f['fecha_registro'] ?? '',
                $f['remitente_nombre'] ?? '',
                $f['remitente_documento'] ?? '',
                $f['asunto'] ?? '',
                $f['area_actual'] ?? 'Sin derivar',
                $f['tipo_documento'] ?? 'Sin tipo',
                $f['canal_ingreso'] ?? '',
                $f['prioridad'] ?? '',
                $f['estado_actual'] ?? '',
                $f['fecha_atencion'] ?? '',
                $f['dias_transcurridos'] ?? '',
                $f['sla'] ?? '',
            ], ';');
        }

        fclose($salida);
        exit;
    }

    public function exportarPdf(): void
    {
        $this->requerirRol(self::RolesPermitidos);

        $filtros = $this->filtros();
        $filas = $this->model->detalle($filtros, 2000);
        $resumen = $this->model->resumen($filtros);

        $this->auditar('exportar-pdf', $filtros, count($filas));

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Mesa de Partes Lurín');
        $pdf->SetAuthor('Municipalidad de Lurín');
        $pdf->SetTitle('Reporte documentario');
        $pdf->SetMargins(10, 12, 10);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        $html = '<h2 style="color:#1e3c72;margin:0 0 2mm 0;">Reporte documentario</h2>'
            . '<p style="font-size:9px;color:#555;margin:0 0 3mm 0;">'
            . 'Periodo: ' . htmlspecialchars($this->textoPeriodo($filtros), ENT_QUOTES, 'UTF-8')
            . ' &nbsp;|&nbsp; Generado: ' . date('d/m/Y H:i')
            . ' &nbsp;|&nbsp; SLA: ' . Reporte::SLA_DIAS . ' días'
            . '</p>'
            . '<table border="0" cellpadding="3" style="font-size:9px;margin-bottom:3mm;">'
            . '<tr>'
            . '<td><b>Total:</b> ' . (int) $resumen['total'] . '</td>'
            . '<td><b>Atendidos:</b> ' . (int) $resumen['atendidos'] . '</td>'
            . '<td><b>Pendientes:</b> ' . (int) $resumen['pendientes'] . '</td>'
            . '<td><b>Dentro de plazo:</b> ' . (int) $resumen['sla_cumplido'] . '</td>'
            . '<td><b>Fuera de plazo:</b> ' . (int) $resumen['sla_incumplido'] . '</td>'
            . '<td><b>Promedio atención:</b> ' . $resumen['promedio_atencion'] . ' días</td>'
            . '</tr></table>';

        $html .= '<table border="1" cellpadding="2" style="font-size:7px;border-color:#ccc;">'
            . '<thead><tr style="background-color:#e8eef7;font-weight:bold;">'
            . '<th width="8%">Código</th><th width="8%">Registro</th><th width="15%">Remitente</th>'
            . '<th width="20%">Asunto</th><th width="12%">Área</th><th width="10%">Tipo</th>'
            . '<th width="6%">Canal</th><th width="6%">Prior.</th><th width="7%">Estado</th>'
            . '<th width="8%">SLA</th>'
            . '</tr></thead><tbody>';

        foreach ($filas as $f) {
            $html .= '<tr>'
                . '<td>' . htmlspecialchars((string) ($f['codigo_expediente'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) ($f['fecha_registro'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) ($f['remitente_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) ($f['asunto'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) ($f['area_actual'] ?? 'Sin derivar'), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) ($f['tipo_documento'] ?? 'Sin tipo'), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) ($f['canal_ingreso'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) ($f['prioridad'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) ($f['estado_actual'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td>' . htmlspecialchars((string) ($f['sla'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                . '</tr>';
        }

        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('reportes-' . date('Ymd-His') . '.pdf', 'D');
        exit;
    }

    /**
     * @return array<string, mixed>
     */
    private function filtros(): array
    {
        $request = new Request();

        $desde = trim((string) ($request->get('fecha_desde') ?? ''));
        $hasta = trim((string) ($request->get('fecha_hasta') ?? ''));

        if (!$this->fechaValida($desde)) {
            $desde = date('Y-m-01');
        }
        if (!$this->fechaValida($hasta)) {
            $hasta = date('Y-m-d');
        }

        $user = Session::user() ?? [];
        $idAreaUsuario = (int) (Session::get('usuario_area') ?? $user['id_area'] ?? 0);

        $idArea = max(0, (int) ($request->get('id_area') ?? 0));
        if (!$this->esAdmin()) {
            $idArea = $idAreaUsuario;
        }

        $estado = (string) ($request->get('estado') ?? '');
        $canal = (string) ($request->get('canal') ?? '');
        $prioridad = (string) ($request->get('prioridad') ?? '');

        return [
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'id_area'     => $idArea,
            'id_tipo_doc' => max(0, (int) ($request->get('id_tipo_doc') ?? 0)),
            'estado'      => in_array($estado, ['Registrado', 'En_Proceso', 'Observado', 'Atendido', 'Archivado'], true) ? $estado : '',
            'canal'       => in_array($canal, ['Virtual', 'Presencial', 'Interno'], true) ? $canal : '',
            'prioridad'   => in_array($prioridad, ['Normal', 'Urgente'], true) ? $prioridad : '',
        ];
    }

    private function esAdmin(): bool
    {
        return $this->tieneRol(Roles::Administracion);
    }

    private function textoPeriodo(array $filtros): string
    {
        return ($filtros['fecha_desde'] ?? '') . ' al ' . ($filtros['fecha_hasta'] ?? '');
    }

    private function auditar(string $accion, array $filtros, int $filas): void
    {
        try {
            $user = Session::user() ?? [];
            (new Auditoria())->insertar(
                $user['id_usuario'] ?? null,
                'reportes',
                $accion,
                'expedientes',
                null,
                'Exportación de reporte (' . $filas . ' fila(s)). Filtros: ' . json_encode($filtros, JSON_UNESCAPED_UNICODE),
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        } catch (\Throwable $e) {
            error_log('Auditoría de reportes falló: ' . $e->getMessage());
        }
    }

    private function fechaValida(string $fecha): bool
    {
        $partes = explode('-', $fecha);

        return count($partes) === 3 && checkdate((int) $partes[1], (int) $partes[2], (int) $partes[0]);
    }
}
