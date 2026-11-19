<?php
/**
 * TAREA E05-2: formulario público de subsanación de observaciones.
 *
 * @var array $expediente
 * @var array $observaciones
 * @var array $errors
 * @var array $datos_viejos
 * @var string $csrf_token
 */
$expediente = $expediente ?? [];
$observaciones = $observaciones ?? [];
$errors = $errors ?? [];
$datos_viejos = $datos_viejos ?? [];
$exito = $exito ?? '';

$codigo = (string) ($expediente['codigo_expediente'] ?? ($datos_viejos['codigo'] ?? ''));
$nombre = trim((string) ($expediente['razon_social'] ?? ''));
if ($nombre === '') {
    $nombre = trim(($expediente['nombres'] ?? '') . ' ' . ($expediente['apellido_paterno'] ?? '') . ' ' . ($expediente['apellido_materno'] ?? ''));
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Subsanar observación — <?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body.subsanar-page { background: #f5f7fb; min-height: 100vh; padding: 30px 15px; }
        .subsanar-box { max-width: 860px; margin: 0 auto; }
        .header-card {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff; border-radius: 12px; padding: 25px 30px; margin-bottom: 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .2);
        }
        .header-card .codigo { font-size: 1.3rem; font-weight: 700; background: rgba(255,255,255,.15); display: inline-block; padding: 5px 14px; border-radius: 6px; }
        .card { border-radius: 10px; border: none; box-shadow: 0 4px 18px rgba(0,0,0,.06); }
        .card-header { background: #fff; border-bottom: 1px solid #eee; }
        .card-header h5 { margin: 0; color: #1e3c72; font-weight: 700; font-size: 1rem; }
        .observacion-item { background: #fff8e6; border-left: 4px solid #f0ad4e; border-radius: 6px; padding: 12px 15px; margin-bottom: 10px; }
        .invalid-feedback { display: block; }
    </style>
</head>

<body class="subsanar-page">
    <div class="subsanar-box">
        <div class="header-card">
            <div class="codigo"><?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?></div>
            <h3 class="mt-2 mb-1">Subsanación de observación</h3>
            <div class="small">
                <?= htmlspecialchars($nombre !== '' ? $nombre : 'Administrado', ENT_QUOTES, 'UTF-8') ?>
                · <?= htmlspecialchars((string) ($expediente['asunto'] ?? 'Expediente observado'), ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>

        <?php if (!empty($exito)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars((string) $exito, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($exito !== ''): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($exito, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Corrija los siguientes errores:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $campo => $mensajes): ?>
                        <?php foreach ((array) $mensajes as $msg): ?>
                            <li><?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-header">
                <h5><i class="fas fa-exclamation-triangle text-warning"></i> Observaciones pendientes</h5>
            </div>
            <div class="card-body">
                <?php if ($observaciones === []): ?>
                    <p class="text-muted mb-0">No hay observaciones pendientes registradas para este expediente.</p>
                <?php else: ?>
                    <?php foreach ($observaciones as $observacion): ?>
                        <div class="observacion-item">
                            <div class="d-flex justify-content-between">
                                <strong>Observación #<?= (int) $observacion['id_observacion'] ?></strong>
                                <small class="text-muted"><?= htmlspecialchars((string) $observacion['fecha_registro'], ENT_QUOTES, 'UTF-8') ?></small>
                            </div>
                            <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars((string) $observacion['descripcion'], ENT_QUOTES, 'UTF-8')) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-file-upload"></i> Presentar subsanación</h5>
            </div>
            <form method="POST" action="/consulta/subsanar" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="codigo" value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?>">

                <div class="card-body">
                    <div class="form-group">
                        <label for="comentario">Detalle de la subsanación *</label>
                        <textarea id="comentario" name="comentario" rows="4"
                            class="form-control <?= isset($errors['comentario']) ? 'is-invalid' : '' ?>"
                            placeholder="Describa cómo cumple lo requerido por el área." required><?= htmlspecialchars((string) ($datos_viejos['comentario'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php if (isset($errors['comentario'])): ?>
                            <div class="invalid-feedback"><?= implode('<br>', array_map('htmlspecialchars', (array) $errors['comentario'])) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="adjuntos">Documentos de sustento (PDF — máximo 10 MB por archivo)</label>
                        <input type="file" id="adjuntos" name="adjuntos[]" class="form-control-file" multiple accept=".pdf">
                        <?php if (isset($errors['adjuntos'])): ?>
                            <div class="text-danger small mt-1"><?= implode('<br>', array_map('htmlspecialchars', (array) $errors['adjuntos'])) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer bg-white border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Enviar subsanación
                    </button>
                    <a href="/consulta?codigo=<?= rawurlencode($codigo) ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>

        <div class="text-center mt-3">
            <a href="/" class="text-muted"><i class="fas fa-home"></i> Volver al inicio</a>
        </div>
    </div>
</body>

</html>
