<?php $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); ?>
<div class="app-content-header">
    <div class="container-fluid d-flex justify-content-between">
        <h1 class="fs-3"><?= $e($expediente['codigo_expediente']) ?></h1><a
            href="/expedientes/<?= (int) $expediente['id_expediente'] ?>/seguimiento"
            class="btn btn-outline-primary">Seguimiento</a>
        <a href="/expedientes/<?= (int) $expediente['id_expediente'] ?>/constancia"
            class="btn btn-outline-secondary ms-2" target="_blank">Constancia</a>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">Datos del expediente</div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Remitente</dt>
                            <dd class="col-sm-8">
                                <?= $e(trim($expediente['nombres'].' '.$expediente['apellido_paterno'].' '.$expediente['apellido_materno'])) ?>
                            </dd>
                            <dt class="col-sm-4">Asunto</dt>
                            <dd class="col-sm-8"><?= $e($expediente['asunto']) ?></dd>
                            <dt class="col-sm-4">Canal / prioridad</dt>
                            <dd class="col-sm-8"><?= $e($expediente['canal_ingreso']) ?> /
                                <?= $e($expediente['prioridad']) ?></dd>
                            <dt class="col-sm-4">Estado</dt>
                            <dd class="col-sm-8"><?= $e(str_replace('_', ' ', $expediente['estado_actual'])) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">Documentos y archivos</div>
                    <div class="card-body">
                        <?php foreach ($documentos as $documento): ?>
                        <h6><?= $e($documento['tipo_doc_nombre']) ?><?= $documento['numero_documento'] ? ' - '.$e($documento['numero_documento']) : '' ?>
                        </h6>
                        <ul><?php foreach ($documento['adjuntos'] as $archivo): ?><li>
                                <?= $e($archivo['nombre_original']) ?> <small
                                    class="text-muted"><?= $e(substr($archivo['hash_archivo'], 0, 16)) ?>…</small></li>
                            <?php endforeach; ?></ul>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">Historial del expediente</div>
            <div class="card-body" id="timeline-expediente">
                <?php foreach ($historial as $evento): ?>
                <div class="mb-3 border-start border-primary ps-3">
                    <strong><?= $e(str_replace('_', ' ', $evento['estado_nuevo'])) ?></strong>
                    <div class="text-muted small"><?= $e($evento['fecha']) ?></div>
                    <div><?= $e($evento['comentario']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<script src="/public/assets/js/expediente/detail.js"></script>