<?php
$persona = $persona ?? [];
$expedientes = $expedientes ?? [];
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$nombreCompleto = $persona['tipo_persona'] === 'Natural'
    ? trim(($persona['nombres'] ?? '') . ' ' . ($persona['apellido_paterno'] ?? '') . ' ' . ($persona['apellido_materno'] ?? ''))
    : ($persona['razon_social'] ?? '');
?>
<div class="app-content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0 fs-3">Detalle de persona</h1>
        <div class="d-flex gap-2">
            <a href="/personas/<?= (int) ($persona['id_persona'] ?? 0) ?>/editar" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Editar
            </a>
            <a href="/personas" class="btn btn-secondary">Volver</a>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Información general</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="text-muted small">Persona</div>
                                <h4 class="mb-0"><?= $e($nombreCompleto ?: 'Sin nombre') ?></h4>
                            </div>
                            <span class="badge text-bg-<?= ($persona['estado'] ?? 'Activo') === 'Activo' ? 'success' : 'secondary' ?>">
                                <?= $e($persona['estado'] ?? 'Activo') ?>
                            </span>
                        </div>

                        <dl class="row mb-0">
                            <dt class="col-sm-5">Tipo</dt>
                            <dd class="col-sm-7"><?= $e($persona['tipo_persona'] ?? '-') ?></dd>

                            <dt class="col-sm-5">Documento</dt>
                            <dd class="col-sm-7"><?= $e(($persona['tipo_documento'] ?? '') . ': ' . ($persona['numero_documento'] ?? '')) ?></dd>

                            <?php if (($persona['tipo_persona'] ?? 'Natural') === 'Natural'): ?>
                                <dt class="col-sm-5">Nombres</dt>
                                <dd class="col-sm-7"><?= $e($persona['nombres'] ?? '-') ?></dd>

                                <dt class="col-sm-5">Apellido paterno</dt>
                                <dd class="col-sm-7"><?= $e($persona['apellido_paterno'] ?? '-') ?></dd>

                                <dt class="col-sm-5">Apellido materno</dt>
                                <dd class="col-sm-7"><?= $e($persona['apellido_materno'] ?? '-') ?></dd>
                            <?php else: ?>
                                <dt class="col-sm-5">Razón social</dt>
                                <dd class="col-sm-7"><?= $e($persona['razon_social'] ?? '-') ?></dd>
                            <?php endif; ?>

                            <dt class="col-sm-5">Correo</dt>
                            <dd class="col-sm-7"><?= $e($persona['email'] ?? '-') ?></dd>

                            <dt class="col-sm-5">Teléfono</dt>
                            <dd class="col-sm-7"><?= $e($persona['telefono'] ?? '-') ?></dd>

                            <dt class="col-sm-5">Dirección</dt>
                            <dd class="col-sm-7"><?= $e($persona['direccion'] ?? '-') ?></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Expedientes relacionados</h3>
                        <span class="badge text-bg-light"><?= count($expedientes) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($expedientes)): ?>
                            <div class="p-4 text-muted">No hay expedientes registrados para esta persona.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Asunto</th>
                                            <th>Prioridad</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($expedientes as $expediente): ?>
                                            <tr>
                                                <td><?= $e($expediente['codigo_expediente'] ?? '-') ?></td>
                                                <td><?= $e($expediente['asunto'] ?? '-') ?></td>
                                                <td><?= $e($expediente['prioridad'] ?? '-') ?></td>
                                                <td>
                                                    <span class="badge text-bg-light"><?= $e($expediente['estado_actual'] ?? '-') ?></span>
                                                </td>
                                                <td><?= $e($expediente['fecha_registro'] ?? '-') ?></td>
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
    </div>
</div>
