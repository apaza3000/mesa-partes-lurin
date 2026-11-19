<?php
$expediente = $expediente ?? null;
$historial = $historial ?? [];
$derivaciones = $derivaciones ?? [];
$observaciones = $observaciones ?? [];
$codigo = $codigo ?? '';
?>
<div class="container mt-4">
    <h2>Trazabilidad de expediente</h2>
    <form method="GET" action="/trazabilidad" class="mb-3">
        <div class="input-group">
            <input type="text" name="codigo" class="form-control" value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?>" placeholder="Código de expediente">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>
    <?php if ($codigo !== '' && !$expediente): ?>
        <div class="alert alert-warning">No se encontró el expediente.</div>
    <?php endif; ?>
    <?php if ($expediente): ?>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Código:</strong> <?= htmlspecialchars($expediente['codigo_expediente']) ?></p>
                <p><strong>Asunto:</strong> <?= htmlspecialchars($expediente['asunto']) ?></p>
                <p><strong>Estado:</strong> <?= htmlspecialchars($expediente['estado_actual']) ?></p>
            </div>
        </div>
        <h4>Historial</h4>
        <ul class="list-group mb-3">
            <?php foreach ($historial as $h): ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($h['fecha'] ?? '') ?> —
                    <?= htmlspecialchars($h['estado_anterior'] ?? '') ?> → <?= htmlspecialchars($h['estado_nuevo'] ?? '') ?>
                    <?php if (!empty($h['comentario'])): ?><br><small><?= htmlspecialchars($h['comentario']) ?></small><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <h4>Derivaciones</h4>
        <ul class="list-group mb-3">
            <?php foreach ($derivaciones as $d): ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($d['area_origen'] ?? '') ?> → <?= htmlspecialchars($d['area_destino'] ?? '') ?>
                    (<?= htmlspecialchars($d['estado'] ?? '') ?>)
                </li>
            <?php endforeach; ?>
        </ul>
        <h4>Observaciones</h4>
        <ul class="list-group">
            <?php foreach ($observaciones as $o): ?>
                <li class="list-group-item"><?= htmlspecialchars($o['descripcion'] ?? '') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>