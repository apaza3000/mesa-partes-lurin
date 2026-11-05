<?php
/**
 * Vista: Bandeja de Entrada — Gestión de Área
 * Variables recibidas:
 * @var string $titulo
 * @var string $tab
 * @var array $conteos
 * @var array $derivaciones
 * @var bool $esAdmin
 * @var int $idAreaUsuario
 * @var int $areaFiltro
 * @var string $nombreAreaActual
 * @var array $todasLasAreas
 * @var string|null $flash_success
 * @var string|null $flash_error
 */

$tab = $tab ?? 'pendientes';
$conteos = $conteos ?? ['pendientes' => 0, 'en_proceso' => 0, 'atendidos_devueltos' => 0, 'total' => 0];
$derivaciones = $derivaciones ?? [];
$todasLasAreas = $todasLasAreas ?? [];
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">
                    <i class="bi bi-inbox-fill text-primary me-2"></i>Bandeja de Gestión de Área
                </h1>
                <p class="text-muted mb-0">
                    Dependencia activa: <span class="badge bg-secondary fs-6"><?= htmlspecialchars($nombreAreaActual, ENT_QUOTES, 'UTF-8') ?></span>
                </p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <?php if ($esAdmin): ?>
                    <form method="GET" action="/area/bandeja-entrada" class="d-inline-flex align-items-center">
                        <input type="hidden" name="tab" value="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8') ?>">
                        <label for="select-area" class="me-2 text-muted small fw-bold mb-0">Filtrar Área:</label>
                        <select name="area_id" id="select-area" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            <option value="0" <?= $areaFiltro === 0 ? 'selected' : '' ?>>-- Todas las Áreas --</option>
                            <?php foreach ($todasLasAreas as $a): ?>
                                <option value="<?= $a['id_area'] ?>" <?= $areaFiltro === (int)$a['id_area'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['siglas'] . ' - ' . $a['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- ALERTAS FLASH -->
        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
                <span class="align-middle"><?= htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8') ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2 fs-5 align-middle"></i>
                <span class="align-middle"><?= htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8') ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <!-- TARJETAS DE RESUMEN / MÉTRICAS -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-warning border-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                                <i class="bi bi-clock-history fs-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted text-uppercase mb-1 small fw-bold">Por Recepcionar</h6>
                                <h3 class="mb-0 fw-bold"><?= $conteos['pendientes'] ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-primary border-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                                <i class="bi bi-arrow-repeat fs-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted text-uppercase mb-1 small fw-bold">En Atención</h6>
                                <h3 class="mb-0 fw-bold"><?= $conteos['en_proceso'] ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-success border-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                                <i class="bi bi-check2-circle fs-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted text-uppercase mb-1 small fw-bold">Atendidos / Devueltos</h6>
                                <h3 class="mb-0 fw-bold"><?= $conteos['atendidos_devueltos'] ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 border-start border-secondary border-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-secondary bg-opacity-10 text-secondary rounded-3 p-3 me-3">
                                <i class="bi bi-folder2-open fs-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted text-uppercase mb-1 small fw-bold">Total Derivaciones</h6>
                                <h3 class="mb-0 fw-bold"><?= $conteos['total'] ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENEDOR PRINCIPAL CON PESTAÑAS -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs card-header-tabs m-0 px-3 pt-2" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'pendientes' ? 'active fw-bold' : 'text-muted' ?>" 
                           href="/area/bandeja-entrada?tab=pendientes<?= $areaFiltro ? '&area_id=' . $areaFiltro : '' ?>">
                            <i class="bi bi-inbox me-1"></i> Pendientes de Recepción
                            <span class="badge bg-warning text-dark rounded-pill ms-1"><?= $conteos['pendientes'] ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'en_proceso' ? 'active fw-bold' : 'text-muted' ?>" 
                           href="/area/bandeja-entrada?tab=en_proceso<?= $areaFiltro ? '&area_id=' . $areaFiltro : '' ?>">
                            <i class="bi bi-gear-wide-connected me-1"></i> En Atención
                            <span class="badge bg-primary rounded-pill ms-1"><?= $conteos['en_proceso'] ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'atendidos_devueltos' ? 'active fw-bold' : 'text-muted' ?>" 
                           href="/area/bandeja-entrada?tab=atendidos_devueltos<?= $areaFiltro ? '&area_id=' . $areaFiltro : '' ?>">
                            <i class="bi bi-archive me-1"></i> Atendidos / Devueltos
                            <span class="badge bg-secondary rounded-pill ms-1"><?= $conteos['atendidos_devueltos'] ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tab === 'todos' ? 'active fw-bold' : 'text-muted' ?>" 
                           href="/area/bandeja-entrada?tab=todos<?= $areaFiltro ? '&area_id=' . $areaFiltro : '' ?>">
                            <i class="bi bi-list-task me-1"></i> Todos los Trámites
                            <span class="badge bg-dark rounded-pill ms-1"><?= $conteos['total'] ?></span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <?php if (empty($derivaciones)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted opacity-50" style="font-size: 4rem;"></i>
                        <h5 class="text-muted mt-3">No hay expedientes en esta categoría</h5>
                        <p class="text-muted small">Los trámites derivados aparecerán aquí cuando sean remitidos a su área.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tabla-derivaciones">
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th scope="col" style="width: 140px;">Expediente</th>
                                    <th scope="col">Asunto / Contenido</th>
                                    <th scope="col">Remitente</th>
                                    <th scope="col">Área Origen</th>
                                    <th scope="col" style="width: 140px;">Fecha Envío</th>
                                    <th scope="col" style="width: 110px;">Estado</th>
                                    <th scope="col" class="text-end" style="width: 170px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($derivaciones as $item): ?>
                                    <?php
                                        // Configuración de badges por estado
                                        $badgeEstado = 'bg-secondary';
                                        if ($item['estado_derivacion'] === 'Pendiente') $badgeEstado = 'bg-warning text-dark';
                                        elseif ($item['estado_derivacion'] === 'Recibido') $badgeEstado = 'bg-primary';
                                        elseif ($item['estado_derivacion'] === 'En_Atencion') $badgeEstado = 'bg-info text-dark';
                                        elseif ($item['estado_derivacion'] === 'Atendido') $badgeEstado = 'bg-success';
                                        elseif ($item['estado_derivacion'] === 'Devuelto') $badgeEstado = 'bg-danger';

                                        // Badge de prioridad
                                        $badgePrioridad = ($item['prioridad'] === 'Urgente') 
                                            ? '<span class="badge bg-danger ms-1">URGENTE</span>' 
                                            : '';

                                        // Badge de expediente observado (TAREA E05-1: pendiente de subsanación)
                                        $badgeObservado = (($item['estado_expediente'] ?? '') === 'Observado')
                                            ? '<span class="badge bg-warning text-dark ms-1" title="Pendiente de subsanación del administrado">OBSERVADO</span>'
                                            : '';
                                    ?>
                                    <tr>
                                        <!-- Expediente -->
                                        <td>
                                            <a href="javascript:void(0)" class="fw-bold text-decoration-none btn-ver-detalle" 
                                               data-id="<?= $item['id_derivacion'] ?>">
                                                <?= htmlspecialchars($item['codigo_expediente'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                            <?= $badgePrioridad ?>
                                            <?= $badgeObservado ?>
                                            <div class="text-muted small">
                                                <i class="bi bi-tag me-1"></i><?= htmlspecialchars($item['canal_ingreso'] ?? 'Virtual', ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </td>

                                        <!-- Asunto -->
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 320px;" title="<?= htmlspecialchars($item['asunto_expediente'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($item['asunto_expediente'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <?php if (!empty($item['observaciones'])): ?>
                                                <small class="text-muted d-block text-truncate" style="max-width: 320px;" title="<?= htmlspecialchars($item['observaciones'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="bi bi-chat-right-text me-1"></i><?= htmlspecialchars($item['observaciones'], ENT_QUOTES, 'UTF-8') ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Remitente -->
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 220px;">
                                                <?= htmlspecialchars($item['remitente_nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars(($item['remitente_tipo_doc'] ?? 'DOC') . ': ' . ($item['remitente_num_doc'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                            </small>
                                        </td>

                                        <!-- Área Origen -->
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($item['area_origen_siglas'] ?? 'MP', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <small class="d-block text-muted text-truncate" style="max-width: 180px;">
                                                <?= htmlspecialchars($item['area_origen_nombre'] ?? 'Mesa de Partes', ENT_QUOTES, 'UTF-8') ?>
                                            </small>
                                        </td>

                                        <!-- Fecha Envío -->
                                        <td>
                                            <div class="small fw-semibold"><?= date('d/m/Y', strtotime($item['fecha_envio'])) ?></div>
                                            <small class="text-muted"><?= date('H:i a', strtotime($item['fecha_envio'])) ?></small>
                                        </td>

                                        <!-- Estado -->
                                        <td>
                                            <span class="badge <?= $badgeEstado ?> rounded-pill">
                                                <?= htmlspecialchars($item['estado_derivacion'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>

                                        <!-- Acciones -->
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <!-- Botón Ver Detalle -->
                                                <button type="button" class="btn btn-outline-secondary btn-ver-detalle" 
                                                        data-id="<?= $item['id_derivacion'] ?>" title="Ver Expediente Completo">
                                                    <i class="bi bi-eye"></i>
                                                </button>

                                                <?php if ($item['estado_derivacion'] === 'Pendiente'): ?>
                                                    <!-- Botón Recepcionar -->
                                                    <form method="POST" action="/area/recepcionar" class="d-inline"
                                                          onsubmit="return confirm('¿Confirma que desea RECEPCIONAR el expediente <?= $item['codigo_expediente'] ?> en su área?');">
                                                        <input type="hidden" name="id_derivacion" value="<?= $item['id_derivacion'] ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                        <button type="submit" class="btn btn-success" title="Recepcionar Expediente">
                                                            <i class="bi bi-check-lg me-1"></i>Recepcionar
                                                        </button>
                                                    </form>

                                                    <!-- Botón Devolver -->
                                                    <button type="button" class="btn btn-outline-danger btn-abrir-devolver"
                                                            data-id="<?= $item['id_derivacion'] ?>"
                                                            data-codigo="<?= htmlspecialchars($item['codigo_expediente'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-origen="<?= htmlspecialchars($item['area_origen_nombre'] ?? 'Mesa de Partes', ENT_QUOTES, 'UTF-8') ?>"
                                                            title="Devolver al remitente">
                                                        <i class="bi bi-arrow-return-left"></i>
                                                    </button>
                                                <?php elseif (in_array($item['estado_derivacion'], ['Recibido', 'En_Atencion'])): ?>
                                                    <!-- Botón Atender (TAREA E04-1: emisión de documento de respuesta) -->
                                                    <a href="/area/atender/<?= $item['id_derivacion'] ?>" class="btn btn-primary" title="Atender Trámite">
                                                        <i class="bi bi-pencil-square me-1"></i>Atender
                                                    </a>

                                                    <!-- Botón Observar (TAREA E05-1: requerir subsanación al administrado) -->
                                                    <?php if (($item['estado_expediente'] ?? '') !== 'Observado'): ?>
                                                    <button type="button" class="btn btn-outline-warning btn-abrir-observar"
                                                            data-id="<?= $item['id_derivacion'] ?>"
                                                            data-codigo="<?= htmlspecialchars($item['codigo_expediente'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-tab="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8') ?>"
                                                            title="Observar para subsanación">
                                                        <i class="bi bi-exclamation-triangle me-1"></i>Observar
                                                    </button>
                                                    <?php endif; ?>

                                                    <!-- Botón Re-derivar a otra Área -->
                                                    <button type="button" class="btn btn-outline-primary btn-abrir-derivar"
                                                            data-id="<?= $item['id_derivacion'] ?>"
                                                            data-codigo="<?= htmlspecialchars($item['codigo_expediente'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-remitente="<?= htmlspecialchars($item['remitente_nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-area-actual="<?= (int)$item['id_area_destino'] ?>"
                                                            title="Derivar a otra Área">
                                                        <i class="bi bi-arrow-right-circle me-1"></i>Derivar
                                                    </button>

                                                    <!-- Botón Devolver si fue recibido por error -->
                                                    <button type="button" class="btn btn-outline-danger btn-abrir-devolver"
                                                            data-id="<?= $item['id_derivacion'] ?>"
                                                            data-codigo="<?= htmlspecialchars($item['codigo_expediente'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-origen="<?= htmlspecialchars($item['area_origen_nombre'] ?? 'Mesa de Partes', ENT_QUOTES, 'UTF-8') ?>"
                                                            title="Devolver">
                                                        <i class="bi bi-arrow-return-left"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL: DEVOLVER EXPEDIENTE                              -->
<!-- ======================================================= -->
<div class="modal fade" id="modalDevolver" tabindex="-1" aria-labelledby="modalDevolverLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="/area/devolver" class="modal-content">
            <input type="hidden" name="id_derivacion" id="devolver_id_derivacion">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalDevolverLabel">
                    <i class="bi bi-arrow-return-left me-2"></i>Devolver Expediente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <p>
                    Está a punto de devolver el expediente <strong id="devolver_codigo" class="text-danger"></strong> 
                    al área de origen <strong id="devolver_origen"></strong>.
                </p>

                <div class="mb-3">
                    <label for="motivo_devolucion" class="form-label fw-bold">Motivo de Devolución <span class="text-danger">*</span></label>
                    <textarea name="motivo" id="motivo_devolucion" rows="4" class="form-control" required
                              placeholder="Indique con claridad por qué no compete a su área o qué requisito insubsanable motivó la devolución..."></textarea>
                    <div class="form-text">Este motivo quedará registrado en la trazabilidad del expediente.</div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-arrow-return-left me-1"></i>Confirmar Devolución
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL: RE-DERIVAR EXPEDIENTE A OTRA ÁREA                -->
<!-- ======================================================= -->
<div class="modal fade" id="modalDerivar" tabindex="-1" aria-labelledby="modalDerivarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="/area/derivar" class="modal-content">
            <input type="hidden" name="id_derivacion" id="derivar_id_derivacion">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDerivarLabel">
                    <i class="bi bi-arrow-right-circle me-2"></i>Derivar a otra Área
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <span class="text-muted small d-block">Expediente:</span>
                            <strong id="derivar_codigo" class="text-primary fs-6"></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Remitente:</span>
                            <span id="derivar_remitente" class="small fw-semibold text-truncate d-block"></span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="derivar_id_area_destino" class="form-label fw-bold">
                        Área de Destino <span class="text-danger">*</span>
                    </label>
                    <select name="id_area_destino" id="derivar_id_area_destino" class="form-select" required>
                        <option value="">-- Seleccione el área de destino --</option>
                        <?php foreach ($todasLasAreas as $ar): ?>
                            <option value="<?= $ar['id_area'] ?>" data-siglas="<?= htmlspecialchars($ar['siglas'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($ar['siglas'] . ' - ' . $ar['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="derivar_observaciones" class="form-label fw-bold">
                        Instrucciones / Proveído de Derivación <span class="text-danger">*</span>
                    </label>
                    <textarea name="observaciones" id="derivar_observaciones" rows="4" class="form-control" required
                              placeholder="Ej: Pase para emisión de informe técnico de inspección / Opinión legal / Por corresponder a sus funciones..."></textarea>
                    <div class="form-text">Indique con claridad el motivo del pase y las acciones que debe realizar el área receptora.</div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send-check me-1"></i>Confirmar y Derivar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL: OBSERVAR EXPEDIENTE (TAREA E05-1)                -->
<!-- ======================================================= -->
<?php include __DIR__ . '/../observaciones/modal_crear.php'; ?>

<!-- ======================================================= -->
<!-- MODAL: DETALLE COMPLETO DEL EXPEDIENTE                  -->
<!-- ======================================================= -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetalleLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>Detalle del Expediente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body" id="modalDetalleBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT DE BANDEJA -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Modal de Devolución
    const modalDevolverEl = document.getElementById('modalDevolver');
    const modalDevolver = new bootstrap.Modal(modalDevolverEl);

    document.querySelectorAll('.btn-abrir-devolver').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const codigo = this.getAttribute('data-codigo');
            const origen = this.getAttribute('data-origen');

            document.getElementById('devolver_id_derivacion').value = id;
            document.getElementById('devolver_codigo').textContent = codigo;
            document.getElementById('devolver_origen').textContent = origen;
            document.getElementById('motivo_devolucion').value = '';

            modalDevolver.show();
        });
    });

    // Modal de Re-derivación a otra Área
    const modalDerivarEl = document.getElementById('modalDerivar');
    const modalDerivar = new bootstrap.Modal(modalDerivarEl);

    document.querySelectorAll('.btn-abrir-derivar').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const codigo = this.getAttribute('data-codigo');
            const remitente = this.getAttribute('data-remitente');
            const areaActual = this.getAttribute('data-area-actual');

            document.getElementById('derivar_id_derivacion').value = id;
            document.getElementById('derivar_codigo').textContent = codigo;
            document.getElementById('derivar_remitente').textContent = remitente;
            document.getElementById('derivar_observaciones').value = '';

            // Habilitar todas las opciones y deshabilitar la opción del área actual para no re-derivar a sí mismo
            const selectDestino = document.getElementById('derivar_id_area_destino');
            selectDestino.value = '';
            Array.from(selectDestino.options).forEach(opt => {
                if (opt.value && opt.value === areaActual) {
                    opt.disabled = true;
                    opt.textContent = opt.textContent.replace(' (Área actual)', '') + ' (Área actual)';
                } else if (opt.value) {
                    opt.disabled = false;
                    opt.textContent = opt.textContent.replace(' (Área actual)', '');
                }
            });

            modalDerivar.show();
        });
    });

    // Modal de Observación (TAREA E05-1)
    const modalObservarEl = document.getElementById('modalObservar');
    const modalObservar = new bootstrap.Modal(modalObservarEl);

    document.querySelectorAll('.btn-abrir-observar').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('observar_id_derivacion').value = this.getAttribute('data-id');
            document.getElementById('observar_codigo').textContent = this.getAttribute('data-codigo');
            document.getElementById('observar_tab').value = this.getAttribute('data-tab') || 'en_proceso';
            document.getElementById('observar_descripcion').value = '';

            modalObservar.show();
        });
    });

    // Modal de Detalle
    const modalDetalleEl = document.getElementById('modalDetalle');
    const modalDetalle = new bootstrap.Modal(modalDetalleEl);
    const modalDetalleBody = document.getElementById('modalDetalleBody');

    document.querySelectorAll('.btn-ver-detalle').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            modalDetalleBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando datos...</span>
                    </div>
                </div>
            `;
            modalDetalle.show();

            fetch(`/area/derivacion/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        modalDetalleBody.innerHTML = `<div class="alert alert-danger">${data.message || 'Error al cargar'}</div>`;
                        return;
                    }

                    const d = data.derivacion;
                    const docs = data.documentos || [];

                    let docsHtml = '';
                    if (docs.length === 0) {
                        docsHtml = '<p class="text-muted small">No se adjuntaron documentos adicionales.</p>';
                    } else {
                        docs.forEach(doc => {
                            let adjuntosHtml = '';
                            if (doc.adjuntos && doc.adjuntos.length > 0) {
                                adjuntosHtml = '<div class="mt-2"><strong class="small text-muted">Archivos adjuntos:</strong><ul class="list-unstyled mt-1 mb-0">';
                                doc.adjuntos.forEach(a => {
                                    adjuntosHtml += `
                                        <li class="small mb-1">
                                            <i class="bi bi-paperclip text-primary me-1"></i>
                                            <a href="/${a.ruta_archivo}" target="_blank" class="text-decoration-none">
                                                ${a.nombre_original} (${(a.tamanio / 1024).toFixed(1)} KB)
                                            </a>
                                        </li>
                                    `;
                                });
                                adjuntosHtml += '</ul></div>';
                            }

                            docsHtml += `
                                <div class="card mb-2 bg-light border-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between">
                                            <strong class="text-primary">${doc.tipo_documento_nombre || 'Documento'} ${doc.numero_documento || ''}</strong>
                                            <span class="badge bg-secondary">${doc.folios || 1} Folio(s)</span>
                                        </div>
                                        <p class="mb-1 small text-muted">${doc.asunto || ''}</p>
                                        ${adjuntosHtml}
                                    </div>
                                </div>
                            `;
                        });
                    }

                    modalDetalleBody.innerHTML = `
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="text-muted small text-uppercase fw-bold">Expediente</label>
                                <div class="fs-5 fw-bold text-primary">${d.codigo_expediente}</div>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <label class="text-muted small text-uppercase fw-bold">Prioridad</label>
                                <div><span class="badge ${d.prioridad === 'Urgente' ? 'bg-danger' : 'bg-secondary'}">${d.prioridad}</span></div>
                            </div>
                        </div>

                        <div class="card p-3 mb-3 border">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <strong class="small text-muted d-block">Remitente:</strong>
                                    <span>${d.remitente_nombre}</span>
                                    <div class="small text-muted">${d.remitente_documento || ''}</div>
                                </div>
                                <div class="col-md-6">
                                    <strong class="small text-muted d-block">Área de Origen:</strong>
                                    <span>${d.area_origen_nombre || 'Mesa de Partes'}</span>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div>
                                <strong class="small text-muted d-block">Asunto del Expediente:</strong>
                                <p class="mb-0">${d.asunto_expediente}</p>
                            </div>
                            ${d.observaciones ? `
                                <div class="mt-2 bg-warning bg-opacity-10 p-2 rounded">
                                    <strong class="small text-warning-emphasis d-block"><i class="bi bi-info-circle me-1"></i>Instrucciones de Derivación:</strong>
                                    <span class="small">${d.observaciones}</span>
                                </div>
                            ` : ''}
                        </div>

                        <h6 class="fw-bold mb-2"><i class="bi bi-files me-1"></i>Documentos Ingresados</h6>
                        ${docsHtml}
                    `;
                })
                .catch(err => {
                    modalDetalleBody.innerHTML = `<div class="alert alert-danger">Error de conexión al cargar el detalle: ${err.message}</div>`;
                });
        });
    });

});
</script>

