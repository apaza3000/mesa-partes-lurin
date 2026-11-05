<?php
/**
 * Partial único de mensajes flash (OPT-07 / MEN-05).
 *
 * Se incluye en el layout `app` para que TODA página muestre los mensajes que
 * cualquier controlador haya encolado con `Flash::exito/error/aviso`, sin que
 * cada vista tenga que recordar leerlos. `Flash::pull()` cosecha también las
 * claves antiguas (`rol_mensaje`, `flash_error`, …) y las limpia de la sesión.
 */

use App\Support\Flash;

$flashMensajes = Flash::pull();
?>
<?php if (!empty($flashMensajes)): ?>
    <div class="container-fluid mt-3" id="flash-mensajes">
        <?php foreach ($flashMensajes as $mensaje): ?>
            <?php
            $tipo = $mensaje['tipo'] ?? 'info';
            $clase = match ($tipo) {
                'success' => 'alert-success',
                'danger' => 'alert-danger',
                'warning' => 'alert-warning',
                default => 'alert-info',
            };
            ?>
            <div class="alert alert-dismissible <?= $clase ?> shadow-sm" role="alert">
                <?= htmlspecialchars($mensaje['mensaje'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-core-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
