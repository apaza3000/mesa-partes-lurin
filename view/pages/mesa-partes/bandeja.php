<?php
/**
 * Vista: Bandeja de Mesa de Partes (TAREA E02-2)
 * Variables recibidas:
 * @var string $titulo
 * @var array $filtros
 * @var array $expedientes
 * @var int $total
 * @var int $paginas
 * @var int $paginaActual
 * @var array $indicadores
 * @var array $areas
 * @var string|null $flash_success
 * @var string|null $flash_error
 */

$filtros = $filtros ?? [];
$expedientes = $expedientes ?? [];
$indicadores = $indicadores ?? ['total' => 0, 'urgentes' => 0, 'sin_derivar' => 0, 'pendientes_recepcion' => 0, 'internos' => 0];
$areas = $areas ?? [];
$paginas = max(1, (int) ($paginas ?? 1));
$paginaActual = max(1, (int) ($paginaActual ?? 1));
$total = (int) ($total ?? 0);

$e = fn(?string $v): string => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

// MEN-04: el catálogo de estados vive en App\Support\Catalogos, no en un `const`
// global de la vista (un doble include provocaba "Cannot redeclare").
$EstadosDerivacionBandeja = \App\Support\Catalogos::DerivacionEtiquetas;

$enlacePagina = function (int $pagina) use ($filtros): string {
    $query = array_filter([
        'desde'   => $filtros['desde'] ?? '',
        'hasta'   => $filtros['hasta'] ?? '',
        'canal'   => $filtros['canal'] ?? '',
        'estado'  => $filtros['estado'] ?? '',
        'area_id' => !empty($filtros['area_id']) ? (string) $filtros['area_id'] : '',
        'q'       => $filtros['q'] ?? '',
    ], static fn($v) => $v !== '' && $v !== '0');
    $query['pagina'] = (string) $pagina;
    return '/mesa-partes/bandeja?' . http_build_query($query);
};
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-0 fs-3"><i class="bi bi-inbox-fill text-primary me-2"></i>Bandeja de Mesa de Partes</h1>
                <p class="text-muted mb-0">Documentos ingresados por ventanilla (canal Presencial e Interno).</p>
            </div>
            <div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
                <a href="/mesa-partes/registrar" class="btn btn-primary">
                    <i class="bi bi-file-earmark-plus-fill me-1"></i>Registrar documento
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= $e($flash_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2"></i><?= $e($flash_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- INDICADORES -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="bi bi-folder2-open fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Ingresos del periodo</h6>
                            <h3 class="mb-0 fw-bold"><?= (int) $indicadores['total'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Urgentes</h6>
                            <h3 class="mb-0 fw-bold"><?= (int) $indicadores['urgentes'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                            <i class="bi bi-send-slash-fill fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Sin derivar</h6>
                            <h3 class="mb-0 fw-bold"><?= (int) $indicadores['sin_derivar'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                            <i class="bi bi-hourglass-split fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Áreas no reciben</h6>
                            <h3 class="mb-0 fw-bold"><?= (int) $indicadores['pendientes_recepcion'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form method="GET" action="/mesa-partes/bandeja" class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label for="desde" class="form-label small fw-bold mb-1">Desde</label>
                        <input type="date" class="form-control form-control-sm" id="desde" name="desde" value="<?= $e((string) ($filtros['desde'] ?? '')) ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="hasta" class="form-label small fw-bold mb-1">Hasta</label>
                        <input type="date" class="form-control form-control-sm" id="hasta" name="hasta" value="<?= $e((string) ($filtros['hasta'] ?? '')) ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="canal" class="form-label small fw-bold mb-1">Canal</label>
                        <select class="form-select form-select-sm" id="canal" name="canal">
                            <option value="">Todos</option>
                            <option value="Presencial" <?= ($filtros['canal'] ?? '') === 'Presencial' ? 'selected' : '' ?>>Presencial</option>
                            <option value="Interno" <?= ($filtros['canal'] ?? '') === 'Interno' ? 'selected' : '' ?>>Interno</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="estado" class="form-label small fw-bold mb-1">Derivación</label>
                        <select class="form-select form-select-sm" id="estado" name="estado">
                            <option value="">Todas</option>
                            <?php foreach ($EstadosDerivacionBandeja as $valor => $etiqueta): ?>
                                <option value="<?= $e($valor) ?>" <?= ($filtros['estado'] ?? '') === $valor ? 'selected' : '' ?>><?= $e($etiqueta) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="area_id" class="form-label small fw-bold mb-1">Área destino</label>
                        <select class="form-select form-select-sm" id="area_id" name="area_id">
                            <option value="0">Todas</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id_area'] ?>" <?= (int) ($filtros['area_id'] ?? 0) === (int) $area['id_area'] ? 'selected' : '' ?>>
                                    <?= $e((string) ($area['siglas'] ?? '')) ?> — <?= $e((string) ($area['nombre'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label for="q" class="form-label small fw-bold mb-1">Búsqueda</label>
                        <input type="text" class="form-control form-control-sm" id="q" name="q"
                               value="<?= $e((string) ($filtros['q'] ?? '')) ?>" placeholder="Código, asunto, DNI/RUC">
                    </div>
                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <a href="/mesa-partes/bandeja" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Hoy</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel-fill me-1"></i>Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTADO -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-1"></i>Registros del periodo</h6>
                <span class="badge bg-secondary"><?= $total ?> resultado(s) — página <?= $paginaActual ?>/<?= $paginas ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($expedientes)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted opacity-50" style="font-size: 4rem;"></i>
                        <h5 class="text-muted mt-3">No hay documentos registrados en el periodo seleccionado</h5>
                        <p class="text-muted small">Registre un documento en ventanilla para que aparezca en esta bandeja.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th style="width: 150px;">Expediente</th>
                                    <th>Asunto / Documento</th>
                                    <th style="width: 200px;">Remitente</th>
                                    <th style="width: 110px;">Canal</th>
                                    <th style="width: 180px;">Derivación inicial</th>
                                    <th style="width: 120px;">Estado</th>
                                    <th style="width: 130px;">Registrado</th>
                                    <th class="text-end" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expedientes as $item): ?>
                                    <?php
                                    $estadoDerivacion = (string) ($item['estado_derivacion'] ?? '');
                                    $badgeDerivacion = $estadoDerivacion === '' ? 'bg-light text-dark border' : match ($estadoDerivacion) {
                                        'Pendiente' => 'bg-warning text-dark',
                                        'Recibido' => 'bg-primary',
                                        'En_Atencion' => 'bg-info text-dark',
                                        'Atendido' => 'bg-success',
                                        'Devuelto' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                    $etiquetaDerivacion = $estadoDerivacion === ''
                                        ? 'Sin derivar'
                                        : ($EstadosDerivacionBandeja[$estadoDerivacion] ?? $estadoDerivacion);
                                    $badgeEstado = match ((string) ($item['estado_actual'] ?? '')) {
                                        'En_Proceso' => 'bg-primary',
                                        'Observado' => 'bg-warning text-dark',
                                        'Atendido' => 'bg-success',
                                        'Archivado' => 'bg-dark',
                                        default => 'bg-secondary',
                                    };
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0)" class="fw-bold text-decoration-none btn-ver-detalle"
                                               data-id="<?= (int) $item['id_expediente'] ?>">
                                                <?= $e((string) $item['codigo_expediente']) ?>
                                            </a>
                                            <?php if (($item['prioridad'] ?? '') === 'Urgente'): ?>
                                                <span class="badge bg-danger ms-1">URGENTE</span>
                                            <?php endif; ?>
                                            <div class="text-muted small">
                                                <i class="bi bi-files me-1"></i><?= (int) ($item['doc_folios'] ?? 0) ?> folio(s)
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 320px;"
                                                 title="<?= $e((string) $item['asunto']) ?>"><?= $e((string) $item['asunto']) ?></div>
                                            <small class="text-muted d-block text-truncate" style="max-width: 320px;">
                                                <i class="bi bi-tag me-1"></i><?= $e((string) ($item['tipo_doc_nombre'] ?? 'Documento')) ?>
                                                <?= $e((string) ($item['doc_numero'] ?? '')) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 190px;"><?= $e((string) $item['remitente_nombre']) ?></div>
                                            <small class="text-muted">
                                                <?= $e((string) ($item['remitente_tipo_doc'] ?? 'DOC')) ?>: <?= $e((string) ($item['remitente_num_doc'] ?? '-')) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= $e((string) $item['canal_ingreso']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $badgeDerivacion ?> rounded-pill"><?= $e($etiquetaDerivacion) ?></span>
                                            <?php if (!empty($item['area_destino_nombre'])): ?>
                                                <small class="d-block text-muted text-truncate" style="max-width: 170px;">
                                                    <?= $e((string) ($item['area_destino_siglas'] ?? '')) ?> — <?= $e((string) $item['area_destino_nombre']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $badgeEstado ?> rounded-pill"><?= $e((string) $item['estado_actual']) ?></span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold"><?= date('d/m/Y', strtotime((string) $item['fecha_registro'])) ?></div>
                                            <small class="text-muted"><?= date('H:i', strtotime((string) $item['fecha_registro'])) ?></small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary btn-ver-detalle"
                                                        data-id="<?= (int) $item['id_expediente'] ?>" title="Ver expediente">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <?php if ($estadoDerivacion === ''): ?>
                                                    <a href="/mesa-partes/registrar" class="btn btn-outline-primary" title="Expediente sin derivación inicial: registre la derivación desde la bandeja del área">
                                                        <i class="bi bi-send"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="/mesa-partes/cargo/<?= (int) $item['id_expediente'] ?>" target="_blank" rel="noopener"
                                                   class="btn btn-outline-dark" title="Reimprimir cargo de recepción">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                <a href="/mesa-partes/cargo/<?= (int) $item['id_expediente'] ?>/pdf" target="_blank" rel="noopener"
                                                   class="btn btn-outline-success" title="Descargar cargo en PDF">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                                <a href="/mesa-partes/hoja-ruta/<?= (int) $item['id_expediente'] ?>" target="_blank" rel="noopener"
                                                   class="btn btn-outline-primary" title="Hoja de ruta del trámite (PDF)">
                                                    <i class="bi bi-signpost-split"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($paginas > 1): ?>
                        <div class="card-footer bg-white d-flex justify-content-center">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $e($enlacePagina(max(1, $paginaActual - 1))) ?>">Anterior</a>
                                </li>
                                <?php for ($i = 1; $i <= $paginas; $i++): ?>
                                    <?php if ($i === 1 || $i === $paginas || abs($i - $paginaActual) <= 2): ?>
                                        <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= $e($enlacePagina($i)) ?>"><?= $i ?></a>
                                        </li>
                                    <?php elseif (abs($i - $paginaActual) === 2): ?>
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <li class="page-item <?= $paginaActual >= $paginas ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $e($enlacePagina(min($paginas, $paginaActual + 1))) ?>">Siguiente</a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DETALLE -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetalleLabel"><i class="bi bi-file-earmark-text me-2"></i>Expediente registrado en ventanilla</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalDetalleBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-outline-dark" id="enlaceCargo" target="_blank" rel="noopener">
                    <i class="bi bi-printer me-1"></i>Cargo de recepción
                </a>
                <a href="#" class="btn btn-success" id="enlaceCargoPdf" target="_blank" rel="noopener">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF
                </a>
                <a href="#" class="btn btn-outline-primary" id="enlaceHojaRuta" target="_blank" rel="noopener">
                    <i class="bi bi-signpost-split me-1"></i>Hoja de ruta
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElemento = document.getElementById('modalDetalle');
    const modal = new bootstrap.Modal(modalElemento);
    const cuerpo = document.getElementById('modalDetalleBody');
    const enlaceCargo = document.getElementById('enlaceCargo');
    const enlaceCargoPdf = document.getElementById('enlaceCargoPdf');
    const enlaceHojaRuta = document.getElementById('enlaceHojaRuta');

    const escapar = (texto) => String(texto ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    const fecha = (valor) => {
        if (!valor) return '—';
        const partes = String(valor).split(/[^0-9]+/);
        if (partes.length < 6) return escapar(valor);
        return partes[2] + '/' + partes[1] + '/' + partes[0] + ' ' + partes[3] + ':' + partes[4];
    };

    document.querySelectorAll('.btn-ver-detalle').forEach((boton) => {
        boton.addEventListener('click', async function () {
            const id = this.getAttribute('data-id');
            cuerpo.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
            enlaceCargo.href = '/mesa-partes/cargo/' + id;
            enlaceCargoPdf.href = '/mesa-partes/cargo/' + id + '/pdf';
            enlaceHojaRuta.href = '/mesa-partes/hoja-ruta/' + id;
            modal.show();

            try {
                const respuesta = await fetch('/mesa-partes/expediente/' + id, { headers: { Accept: 'application/json' } });
                const data = await respuesta.json();

                if (!data.success) {
                    cuerpo.innerHTML = '<div class="alert alert-danger">' + escapar(data.message || 'No se pudo cargar el expediente.') + '</div>';
                    return;
                }

                const exp = data.expediente;
                let html = `
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="text-muted small text-uppercase fw-bold">Expediente</label>
                            <div class="fs-5 fw-bold text-primary">${escapar(exp.codigo_expediente)}</div>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <label class="text-muted small text-uppercase fw-bold">Prioridad / Estado</label>
                            <div>
                                <span class="badge ${exp.prioridad === 'Urgente' ? 'bg-danger' : 'bg-secondary'}">${escapar(exp.prioridad)}</span>
                                <span class="badge bg-info text-dark">${escapar(exp.estado_actual)}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card border p-3 mb-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <strong class="small text-muted d-block">Remitente</strong>
                                ${escapar(exp.remitente_nombre)}
                                <div class="small text-muted">${escapar(exp.tipo_documento)}: ${escapar(exp.numero_documento)}</div>
                                <div class="small text-muted">${escapar(exp.email)} ${exp.telefono ? '· ' + escapar(exp.telefono) : ''}</div>
                            </div>
                            <div class="col-md-6">
                                <strong class="small text-muted d-block">Ingreso</strong>
                                <div class="small">Canal: ${escapar(exp.canal_ingreso)}</div>
                                <div class="small">Registrado: ${fecha(exp.fecha_registro)}</div>
                                <div class="small">Folios totales: ${escapar(exp.total_folios)}</div>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div>
                            <strong class="small text-muted d-block">Asunto</strong>
                            <p class="mb-0">${escapar(exp.asunto)}</p>
                        </div>
                        ${exp.direccion ? `<div class="mt-2"><strong class="small text-muted d-block">Dirección</strong><span class="small">${escapar(exp.direccion)}</span></div>` : ''}
                    </div>`;

                const documentos = data.documentos || [];
                html += '<h6 class="fw-bold mb-2"><i class="bi bi-files me-1"></i>Documentos y adjuntos</h6>';
                if (!documentos.length) {
                    html += '<p class="text-muted small">Sin documentos registrados.</p>';
                } else {
                    documentos.forEach((doc) => {
                        let adjuntos = '';
                        (doc.adjuntos || []).forEach((archivo) => {
                            adjuntos += `
                                <li class="small mb-1">
                                    <i class="bi bi-paperclip text-primary me-1"></i>
                                    <a href="/${escapar(archivo.ruta_archivo)}" target="_blank" rel="noopener" class="text-decoration-none">
                                        ${escapar(archivo.nombre_original)}
                                    </a>
                                    <span class="text-muted">(${(Number(archivo.tamanio || 0) / 1024).toFixed(1)} KB)</span>
                                </li>`;
                        });
                        html += `
                            <div class="card bg-light border-0 mb-2">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <strong class="text-primary">${escapar(doc.tipo_documento_nombre || 'Documento')} ${escapar(doc.numero_documento || '')}</strong>
                                        <span class="badge bg-secondary">${escapar(doc.folios)} folio(s)</span>
                                    </div>
                                    <p class="mb-1 small text-muted">${escapar(doc.asunto)}</p>
                                    <div class="small text-muted">Tipo de registro: ${escapar(doc.tipo_documento_registro)}</div>
                                    ${adjuntos ? '<ul class="list-unstyled mt-2 mb-0">' + adjuntos + '</ul>' : ''}
                                </div>
                            </div>`;
                    });
                }

                const derivaciones = data.derivaciones || [];
                html += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-send me-1"></i>Derivaciones</h6>';
                if (!derivaciones.length) {
                    html += '<div class="alert alert-warning small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>El expediente aún no tiene derivación registrada.</div>';
                } else {
                    html += '<ul class="list-unstyled mb-0">';
                    derivaciones.forEach((der) => {
                        html += `
                            <li class="small mb-1">
                                <span class="badge bg-light text-dark border me-1">${escapar(der.estado)}</span>
                                ${escapar(der.area_origen_nombre || 'Origen')} &rarr; <strong>${escapar(der.area_destino_nombre || 'Destino')}</strong>
                                <span class="text-muted">· ${fecha(der.fecha_envio)}</span>
                                ${der.observaciones ? `<div class="text-muted ms-2">${escapar(der.observaciones)}</div>` : ''}
                            </li>`;
                    });
                    html += '</ul>';
                }

                const historial = data.historial || [];
                if (historial.length) {
                    html += '<h6 class="fw-bold mt-3 mb-2"><i class="bi bi-clock-history me-1"></i>Trazabilidad</h6><ul class="list-unstyled mb-0 small">';
                    historial.forEach((registro) => {
                        html += `<li class="mb-1"><span class="text-muted">${fecha(registro.fecha)}</span> — ${escapar(registro.comentario)}</li>`;
                    });
                    html += '</ul>';
                }

                cuerpo.innerHTML = html;
            } catch (error) {
                cuerpo.innerHTML = '<div class="alert alert-danger">Error de conexión al cargar el expediente.</div>';
            }
        });
    });
});
</script>
