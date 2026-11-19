<?php
/**
 * TAREA E09-1: panel de reportes documentarios.
 *
 * @var array $filtros
 * @var array $resumen
 * @var array $porDia
 * @var array $porArea
 * @var array $porTipo
 * @var array $porEstado
 * @var array $porCanal
 * @var array $porPrioridad
 * @var array $slaPorArea
 * @var array $detalle
 * @var array $areas
 * @var array $tipos
 * @var bool $esAdmin
 * @var int $slaDias
 */
$filtros = $filtros ?? [];
$resumen = $resumen ?? [];
$porDia = $porDia ?? [];
$porArea = $porArea ?? [];
$porTipo = $porTipo ?? [];
$porEstado = $porEstado ?? [];
$porCanal = $porCanal ?? [];
$porPrioridad = $porPrioridad ?? [];
$slaPorArea = $slaPorArea ?? [];
$detalle = $detalle ?? [];
$areas = $areas ?? [];
$tipos = $tipos ?? [];
$esAdmin = $esAdmin ?? false;
$slaDias = (int) ($slaDias ?? \App\Models\Reporte::SLA_DIAS);

$e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$json = static fn(array $v): string => json_encode(
    array_values($v),
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?: '[]';

$queryFiltros = http_build_query(array_filter([
    'fecha_desde' => $filtros['fecha_desde'] ?? '',
    'fecha_hasta' => $filtros['fecha_hasta'] ?? '',
    'id_area'     => $filtros['id_area'] ?? 0,
    'id_tipo_doc' => $filtros['id_tipo_doc'] ?? 0,
    'estado'      => $filtros['estado'] ?? '',
    'canal'       => $filtros['canal'] ?? '',
    'prioridad'   => $filtros['prioridad'] ?? '',
], static fn($v) => $v !== '' && $v !== 0 && $v !== '0'));

$total = (int) ($resumen['total'] ?? 0);
$atendidos = (int) ($resumen['atendidos'] ?? 0);
$pendientes = (int) ($resumen['pendientes'] ?? 0);
$observados = (int) ($resumen['observados'] ?? 0);
$archivados = (int) ($resumen['archivados'] ?? 0);
$dentro = (int) ($resumen['sla_cumplido'] ?? 0);
$fuera = (int) ($resumen['sla_incumplido'] ?? 0);
$vencidos = (int) ($resumen['sla_pendiente_vencido'] ?? 0);
$promedio = (float) ($resumen['promedio_atencion'] ?? 0);

$estadoClases = [
    'Registrado'  => 'secondary',
    'En_Proceso'  => 'primary',
    'Observado'   => 'warning',
    'Atendido'    => 'success',
    'Archivado'   => 'dark',
];
?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-0 fs-3"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Reportes documentarios</h1>
                <p class="text-muted mb-0">Indicadores de volumen, estados y cumplimiento del plazo de atención (SLA <?= $slaDias ?> días).</p>
            </div>
            <div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
                <a href="/reportes/exportar/csv?<?= $e($queryFiltros) ?>" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-filetype-csv me-1"></i> Exportar CSV
                </a>
                <a href="/reportes/exportar/pdf?<?= $e($queryFiltros) ?>" class="btn btn-sm btn-outline-danger" target="_blank" rel="noopener">
                    <i class="bi bi-filetype-pdf me-1"></i> Exportar PDF
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="/reportes" class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="fecha_desde">Desde</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde"
                               value="<?= $e($filtros['fecha_desde'] ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="fecha_hasta">Hasta</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta"
                               value="<?= $e($filtros['fecha_hasta'] ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="id_area">Área</label>
                        <select class="form-select form-select-sm" id="id_area" name="id_area" <?= $esAdmin ? '' : 'disabled' ?>>
                            <?php if ($esAdmin): ?>
                                <option value="0">Todas</option>
                            <?php endif; ?>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id_area'] ?>" <?= (int) ($filtros['id_area'] ?? 0) === (int) $area['id_area'] ? 'selected' : '' ?>>
                                    <?= $e(($area['siglas'] ?? '') . ' - ' . ($area['nombre'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$esAdmin): ?>
                            <input type="hidden" name="id_area" value="<?= (int) ($filtros['id_area'] ?? 0) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="id_tipo_doc">Tipo</label>
                        <select class="form-select form-select-sm" id="id_tipo_doc" name="id_tipo_doc">
                            <option value="0">Todos</option>
                            <?php foreach ($tipos as $tipo): ?>
                                <option value="<?= (int) $tipo['id_tipo_doc'] ?>" <?= (int) ($filtros['id_tipo_doc'] ?? 0) === (int) $tipo['id_tipo_doc'] ? 'selected' : '' ?>>
                                    <?= $e($tipo['nombre'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="estado">Estado</label>
                        <select class="form-select form-select-sm" id="estado" name="estado">
                            <option value="">Todos</option>
                            <?php foreach (\App\Support\Catalogos::EstadoExpediente as $opcion): ?>
                                <option value="<?= $opcion ?>" <?= ($filtros['estado'] ?? '') === $opcion ? 'selected' : '' ?>><?= $opcion ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="canal">Canal</label>
                        <select class="form-select form-select-sm" id="canal" name="canal">
                            <option value="">Todos</option>
                            <?php foreach (\App\Support\Catalogos::CanalIngreso as $opcion): ?>
                                <option value="<?= $opcion ?>" <?= ($filtros['canal'] ?? '') === $opcion ? 'selected' : '' ?>><?= $opcion ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="prioridad">Prioridad</label>
                        <select class="form-select form-select-sm" id="prioridad" name="prioridad">
                            <option value="">Todas</option>
                            <?php foreach (\App\Support\Catalogos::Prioridad as $opcion): ?>
                                <option value="<?= $opcion ?>" <?= ($filtros['prioridad'] ?? '') === $opcion ? 'selected' : '' ?>><?= $opcion ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                        <a href="/reportes" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Expedientes</div>
                        <div class="fs-2 fw-bold"><?= $total ?></div>
                        <div class="small text-muted"><?= $atendidos ?> atendidos · <?= $pendientes ?> pendientes</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Observados</div>
                        <div class="fs-2 fw-bold text-warning"><?= $observados ?></div>
                        <div class="small text-muted"><?= $archivados ?> archivados</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Dentro del plazo</div>
                        <div class="fs-2 fw-bold text-success"><?= $dentro ?></div>
                        <div class="small text-muted"><?= $fuera ?> fuera de plazo</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase">Promedio de atención</div>
                        <div class="fs-2 fw-bold"><?= number_format($promedio, 1) ?> <span class="fs-6 fw-normal">días</span></div>
                        <div class="small text-muted"><?= $vencidos ?> pendientes vencidos</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h3 class="card-title mb-0">Volumen diario</h3></div>
                    <div class="card-body"><canvas id="grafico-dia" height="110"></canvas></div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h3 class="card-title mb-0">Por estado</h3></div>
                    <div class="card-body"><canvas id="grafico-estado" height="180"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h3 class="card-title mb-0">Volumen por área</h3></div>
                    <div class="card-body"><canvas id="grafico-area" height="150"></canvas></div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h3 class="card-title mb-0">Volumen por tipo de documento</h3></div>
                    <div class="card-body"><canvas id="grafico-tipo" height="150"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h3 class="card-title mb-0">Canal de ingreso</h3></div>
                    <div class="card-body"><canvas id="grafico-canal" height="170"></canvas></div>
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h3 class="card-title mb-0">Cumplimiento del SLA por área</h3></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Área</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center text-success">Dentro</th>
                                        <th class="text-center text-danger">Fuera</th>
                                        <th class="text-center text-warning">Vencidos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($slaPorArea === []): ?>
                                        <tr><td colspan="5" class="text-center text-muted py-3">Sin datos para el periodo.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($slaPorArea as $fila): ?>
                                            <tr>
                                                <td><?= $e($fila['area'] ?? 'Sin derivar') ?></td>
                                                <td class="text-center"><?= (int) ($fila['total'] ?? 0) ?></td>
                                                <td class="text-center text-success"><?= (int) ($fila['cumplido'] ?? 0) ?></td>
                                                <td class="text-center text-danger"><?= (int) ($fila['incumplido'] ?? 0) ?></td>
                                                <td class="text-center text-warning"><?= (int) ($fila['pendientes_vencidos'] ?? 0) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Detalle de expedientes</h3>
                <span class="badge text-bg-light border"><?= count($detalle) ?> fila(s) · máximo 200 en pantalla</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Registro</th>
                                <th>Remitente</th>
                                <th>Asunto</th>
                                <th>Área</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>SLA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($detalle === []): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No hay expedientes para los filtros seleccionados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($detalle as $fila): ?>
                                    <?php $estado = (string) ($fila['estado_actual'] ?? ''); ?>
                                    <tr>
                                        <td class="fw-semibold"><?= $e($fila['codigo_expediente'] ?? '') ?></td>
                                        <td class="small text-muted"><?= $e($fila['fecha_registro'] ?? '') ?></td>
                                        <td class="small">
                                            <?= $e($fila['remitente_nombre'] ?? '') ?>
                                            <div class="text-muted"><?= $e($fila['remitente_documento'] ?? '') ?></div>
                                        </td>
                                        <td class="small"><?= $e($fila['asunto'] ?? '') ?></td>
                                        <td class="small"><?= $e($fila['area_actual'] ?? 'Sin derivar') ?></td>
                                        <td class="small"><?= $e($fila['tipo_documento'] ?? 'Sin tipo') ?></td>
                                        <td><span class="badge bg-<?= $estadoClases[$estado] ?? 'secondary' ?>"><?= $e($estado) ?></span></td>
                                        <td class="small"><?= $e($fila['sla'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const paleta = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0', '#fd7e14', '#6c757d'];

    const porDia = <?= $json($porDia) ?>;
    const porEstado = <?= $json($porEstado) ?>;
    const porArea = <?= $json($porArea) ?>;
    const porTipo = <?= $json($porTipo) ?>;
    const porCanal = <?= $json($porCanal) ?>;

    const base = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    };

    const canvasDia = document.getElementById('grafico-dia');
    if (canvasDia) {
        new Chart(canvasDia, {
            type: 'line',
            data: {
                labels: porDia.map(function (f) { return f.dia; }),
                datasets: [{
                    label: 'Expedientes',
                    data: porDia.map(function (f) { return f.total; }),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,.15)',
                    fill: true,
                    tension: .3
                }]
            },
            options: base
        });
    }

    const canvasEstado = document.getElementById('grafico-estado');
    if (canvasEstado) {
        new Chart(canvasEstado, {
            type: 'doughnut',
            data: {
                labels: porEstado.map(function (f) { return f.etiqueta; }),
                datasets: [{ data: porEstado.map(function (f) { return f.total; }), backgroundColor: paleta }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const canvasArea = document.getElementById('grafico-area');
    if (canvasArea) {
        new Chart(canvasArea, {
            type: 'bar',
            data: {
                labels: porArea.map(function (f) { return f.etiqueta; }),
                datasets: [{ data: porArea.map(function (f) { return f.total; }), backgroundColor: '#198754' }]
            },
            options: { ...base, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const canvasTipo = document.getElementById('grafico-tipo');
    if (canvasTipo) {
        new Chart(canvasTipo, {
            type: 'bar',
            data: {
                labels: porTipo.map(function (f) { return f.etiqueta; }),
                datasets: [{ data: porTipo.map(function (f) { return f.total; }), backgroundColor: '#6f42c1' }]
            },
            options: { ...base, indexAxis: 'y', scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    const canvasCanal = document.getElementById('grafico-canal');
    if (canvasCanal) {
        new Chart(canvasCanal, {
            type: 'pie',
            data: {
                labels: porCanal.map(function (f) { return f.etiqueta; }),
                datasets: [{ data: porCanal.map(function (f) { return f.total; }), backgroundColor: paleta }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
});
</script>
