<?php $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); ?>
<div class="app-content-header">
    <div class="container-fluid">
        <h1 class="fs-3">Seguimiento: <?= $e($expediente['codigo_expediente']) ?></h1>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="timeline" id="timeline">
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
</div>
<script src="/public/assets/js/expediente/seguimiento.js"></script>