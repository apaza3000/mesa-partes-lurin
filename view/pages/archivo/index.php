<?php
/**
 * TAREA E06-1: Archivo Central.
 *
 * @var array $expedientes
 * @var array $filtros
 * @var array $areas
 * @var array $anios
 * @var string $csrf_token
 */
$expedientes = $expedientes ?? [];
$filtros = $filtros ?? [];
$areas = $areas ?? [];
$anios = $anios ?? [];
$csrf_token = $csrf_token ?? '';
?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3"><i class="bi bi-archive-fill text-primary me-2"></i>Archivo Central</h1>
                <p class="text-muted mb-0">Expedientes atendidos y archivados con su ubicación física.</p>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="/archivo" class="row g-2 align-items-end">
                    <div class="col-sm-3">
                        <label class="form-label small mb-1">Código</label>
                        <input type="text" name="codigo" class="form-control form-control-sm" value="<?= htmlspecialchars((string) ($filtros['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="EXP-2026-00001">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label small mb-1">Año</label>
                        <select name="anio" class="form-select form-select-sm">
                            <option value="0">Todos</option>
                            <?php foreach ($anios as $anio): ?>
                                <option value="<?= (int) $anio ?>" <?= (int) ($filtros['anio'] ?? 0) === (int) $anio ? 'selected' : '' ?>><?= (int) $anio ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label small mb-1">Área</label>
                        <select name="area_id" class="form-select form-select-sm">
                            <option value="0">Todas</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id_area'] ?>" <?= (int) ($filtros['id_area'] ?? 0) === (int) $area['id_area'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $area['siglas'] . ' - ' . (string) $area['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label small mb-1">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="Atendido" <?= ($filtros['estado'] ?? '') === 'Atendido' ? 'selected' : '' ?>>Atendido</option>
                            <option value="Archivado" <?= ($filtros['estado'] ?? '') === 'Archivado' ? 'selected' : '' ?>>Archivado</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Remitente</th>
                                <th>Área</th>
                                <th>Estado</th>
                                <th>Ubicación física</th>
                                <th>Fechas</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($expedientes === []): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No hay expedientes atendidos o archivados con esos filtros.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($expedientes as $exp): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars((string) $exp['codigo_expediente'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div class="small text-muted"><?= htmlspecialchars((string) $exp['asunto'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars((string) $exp['remitente_nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            <div class="small text-muted"><?= htmlspecialchars((string) $exp['remitente_tipo_doc'] . ' ' . (string) $exp['remitente_documento'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($exp['area_actual'] ?? 'Sin derivar'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $exp['estado_actual'] === 'Archivado' ? 'dark' : 'success' ?>">
                                                <?= htmlspecialchars((string) $exp['estado_actual'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="small">
                                            <?php if (!empty($exp['caja']) || !empty($exp['estante'])): ?>
                                                Caja: <?= htmlspecialchars((string) ($exp['caja'] ?? '—'), ENT_QUOTES, 'UTF-8') ?><br>
                                                Archivador: <?= htmlspecialchars((string) ($exp['archivador'] ?? '—'), ENT_QUOTES, 'UTF-8') ?><br>
                                                Estante: <?= htmlspecialchars((string) ($exp['estante'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin ubicación</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted">
                                            Reg.: <?= htmlspecialchars((string) $exp['fecha_registro'], ENT_QUOTES, 'UTF-8') ?><br>
                                            Arch.: <?= htmlspecialchars((string) ($exp['fecha_archivado'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="text-end">
                                            <button type="button"
                                                class="btn btn-sm btn-<?= $exp['estado_actual'] === 'Archivado' ? 'warning' : 'primary' ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalArchivar"
                                                data-id="<?= (int) $exp['id_expediente'] ?>"
                                                data-codigo="<?= htmlspecialchars((string) $exp['codigo_expediente'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-caja="<?= htmlspecialchars((string) ($exp['caja'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                data-archivador="<?= htmlspecialchars((string) ($exp['archivador'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                data-estante="<?= htmlspecialchars((string) ($exp['estante'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-box-seam me-1"></i><?= $exp['estado_actual'] === 'Archivado' ? 'Reubicar' : 'Archivar' ?>
                                            </button>
                                            <a href="/archivo/<?= (int) $exp['id_expediente'] ?>/cargo" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                                                <i class="bi bi-file-pdf"></i> Cargo
                                            </a>
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

<div class="modal fade" id="modalArchivar" tabindex="-1" aria-labelledby="modalArchivarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/archivo/archivar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id_expediente" id="archivar-id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalArchivarLabel">Archivar expediente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Expediente: <strong id="archivar-codigo">—</strong></p>
                    <div class="mb-3">
                        <label for="archivar-caja" class="form-label">Caja *</label>
                        <input type="text" class="form-control" id="archivar-caja" name="caja" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label for="archivar-archivador" class="form-label">Archivador</label>
                        <input type="text" class="form-control" id="archivar-archivador" name="archivador" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="archivar-estante" class="form-label">Estante *</label>
                        <input type="text" class="form-control" id="archivar-estante" name="estante" maxlength="50" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar ubicación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalArchivar');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        if (!boton) return;

        document.getElementById('archivar-id').value = boton.getAttribute('data-id') || '';
        document.getElementById('archivar-codigo').textContent = boton.getAttribute('data-codigo') || '—';
        document.getElementById('archivar-caja').value = boton.getAttribute('data-caja') || '';
        document.getElementById('archivar-archivador').value = boton.getAttribute('data-archivador') || '';
        document.getElementById('archivar-estante').value = boton.getAttribute('data-estante') || '';
    });
});
</script>
