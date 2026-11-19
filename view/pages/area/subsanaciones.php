<?php
/**
 * TAREA E05-2: bandeja del área con las observaciones subsanadas.
 *
 * @var array $subsanaciones
 * @var bool $esAdmin
 * @var int $areaFiltro
 * @var array $areas
 * @var string $csrf_token
 */
$subsanaciones = $subsanaciones ?? [];
$areas = $areas ?? [];
$esAdmin = $esAdmin ?? false;
$areaFiltro = (int) ($areaFiltro ?? 0);
$csrf_token = $csrf_token ?? '';
?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3"><i class="bi bi-clipboard2-check-fill text-primary me-2"></i>Subsanaciones por revisar</h1>
                <p class="text-muted mb-0">Observaciones presentadas por el administrado y pendientes de levantamiento.</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <?php if ($esAdmin): ?>
                    <form method="GET" action="/area/subsanaciones" class="d-inline-flex align-items-center">
                        <label for="select-area" class="me-2 text-muted small fw-bold mb-0">Filtrar área:</label>
                        <select name="area_id" id="select-area" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                            <option value="0" <?= $areaFiltro === 0 ? 'selected' : '' ?>>-- Todas --</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id_area'] ?>" <?= $areaFiltro === (int) $area['id_area'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $area['siglas'] . ' - ' . (string) $area['nombre'], ENT_QUOTES, 'UTF-8') ?>
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
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Expediente</th>
                                <th>Remitente</th>
                                <th>Área</th>
                                <th>Observación</th>
                                <th>Subsanado</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($subsanaciones === []): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No hay subsanaciones pendientes de revisión.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($subsanaciones as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars((string) $item['codigo_expediente'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div class="small text-muted"><?= htmlspecialchars((string) ($item['asunto_expediente'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars((string) ($item['remitente_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            <div class="small text-muted"><?= htmlspecialchars((string) ($item['remitente_documento'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars((string) ($item['area_destino_siglas'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            <div class="small text-muted"><?= htmlspecialchars((string) ($item['area_destino_nombre'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td style="max-width: 320px;">
                                            <div class="small"><?= nl2br(htmlspecialchars((string) $item['descripcion'], ENT_QUOTES, 'UTF-8')) ?></div>
                                        </td>
                                        <td class="small text-muted"><?= htmlspecialchars((string) ($item['fecha_subsanacion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-end">
                                            <form method="POST" action="/area/subsanaciones/levantar" class="d-inline-block" style="min-width: 240px;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="id_observacion" value="<?= (int) $item['id_observacion'] ?>">
                                                <input type="text" name="comentario" class="form-control form-control-sm mb-1" maxlength="500" placeholder="Comentario (opcional)">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check2-circle me-1"></i>Levantar observación
                                                </button>
                                            </form>
                                        </td>
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
