<?php
/**
 * Vista: Atención de Expediente con documento de respuesta (TAREA E04-1)
 * Variables recibidas:
 * @var string $titulo
 * @var array $derivacion
 * @var array $documentos
 * @var array $tiposRespuesta
 * @var array $errors
 * @var array $datos_viejos
 */

$derivacion = $derivacion ?? [];
$documentos = $documentos ?? [];
$tiposRespuesta = $tiposRespuesta ?? [];
$errors = $errors ?? [];
$datos_viejos = $datos_viejos ?? [];
$idDerivacion = (int) ($derivacion['id_derivacion'] ?? 0);

$err = static function (string $campo) use ($errors): string {
    return isset($errors[$campo]) ? ' is-invalid' : '';
};
$old = static function (string $campo, string $defecto = '') use ($datos_viejos): string {
    return htmlspecialchars((string) ($datos_viejos[$campo] ?? $defecto), ENT_QUOTES, 'UTF-8');
};
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h1 class="mb-0 fs-3">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Atención de Expediente
                </h1>
                <p class="text-muted mb-0">
                    Expediente <span class="badge bg-primary fs-6"><?= htmlspecialchars($derivacion['codigo_expediente'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if (($derivacion['prioridad'] ?? '') === 'Urgente'): ?>
                        <span class="badge bg-danger">URGENTE</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-sm-4 text-sm-end">
                <a href="/area/bandeja-entrada?tab=en_proceso" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver a la Bandeja
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <?php foreach ((array) $errors['general'] as $e): ?>
                    <div><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- ============ COLUMNA IZQUIERDA: CONTEXTO ============ -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i>Ficha del Expediente
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0 small">
                            <dt class="col-sm-4 text-muted">Remitente</dt>
                            <dd class="col-sm-8 fw-semibold">
                                <?= htmlspecialchars($derivacion['remitente_nombre'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                <div class="text-muted"><?= htmlspecialchars($derivacion['remitente_documento'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                            </dd>

                            <dt class="col-sm-4 text-muted">Asunto</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($derivacion['asunto_expediente'] ?? '—', ENT_QUOTES, 'UTF-8') ?></dd>

                            <dt class="col-sm-4 text-muted">Canal / Estado</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($derivacion['canal_ingreso'] ?? 'Virtual', ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="badge bg-primary"><?= htmlspecialchars($derivacion['estado_expediente'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </dd>

                            <dt class="col-sm-4 text-muted">Área Origen</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($derivacion['area_origen_nombre'] ?? 'Mesa de Partes', ENT_QUOTES, 'UTF-8') ?></dd>

                            <dt class="col-sm-4 text-muted">Fecha Envío</dt>
                            <dd class="col-sm-8">
                                <?= !empty($derivacion['fecha_envio']) ? date('d/m/Y H:i', strtotime($derivacion['fecha_envio'])) : '—' ?>
                            </dd>

                            <dt class="col-sm-4 text-muted">Recepción Área</dt>
                            <dd class="col-sm-8">
                                <?= !empty($derivacion['fecha_recepcion']) ? date('d/m/Y H:i', strtotime($derivacion['fecha_recepcion'])) : '—' ?>
                            </dd>
                        </dl>

                        <?php if (!empty($derivacion['observaciones'])): ?>
                            <div class="alert alert-warning bg-opacity-10 border-warning mt-3 mb-0 small">
                                <strong class="text-warning-emphasis d-block mb-1">
                                    <i class="bi bi-info-circle me-1"></i>Instrucciones de derivación:
                                </strong>
                                <?= nl2br(htmlspecialchars($derivacion['observaciones'], ENT_QUOTES, 'UTF-8')) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-files me-2 text-primary"></i>Documentos Ingresados
                    </div>
                    <div class="card-body">
                        <?php if (empty($documentos)): ?>
                            <p class="text-muted small mb-0">No hay documentos registrados en el expediente.</p>
                        <?php else: ?>
                            <?php foreach ($documentos as $doc): ?>
                                <div class="card bg-light border-0 mb-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <strong class="text-primary">
                                                <?= htmlspecialchars(($doc['tipo_documento_nombre'] ?? 'Documento') . ' ' . ($doc['numero_documento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            </strong>
                                            <span class="badge bg-secondary"><?= (int) ($doc['folios'] ?? 1) ?> folio(s)</span>
                                        </div>
                                        <p class="mb-1 small text-muted"><?= htmlspecialchars($doc['asunto'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                        <?php foreach ($doc['adjuntos'] ?? [] as $a): ?>
                                            <div class="small">
                                                <i class="bi bi-paperclip text-primary me-1"></i>
                                                <a href="/<?= htmlspecialchars($a['ruta_archivo'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="text-decoration-none">
                                                    <?= htmlspecialchars($a['nombre_original'], ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                                <span class="text-muted">(<?= number_format($a['tamanio'] / 1024, 1) ?> KB)</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ============ COLUMNA DERECHA: FORMULARIO DE RESPUESTA ============ -->
            <div class="col-lg-7">
                <form method="POST" action="/area/atender" enctype="multipart/form-data"
                      onsubmit="return confirm('¿Confirma la ATENCIÓN del expediente con el documento de respuesta indicado?');">
                    <input type="hidden" name="id_derivacion" value="<?= $idDerivacion ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="bi bi-reply-all me-2"></i>Documento de Respuesta
                        </div>
                        <div class="card-body">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="id_tipo_respuesta" class="form-label fw-bold">
                                        Tipo de documento de respuesta <span class="text-danger">*</span>
                                    </label>
                                    <select name="id_tipo_respuesta" id="id_tipo_respuesta" class="form-select<?= $err('id_tipo_respuesta') ?>" required>
                                        <option value="">-- Seleccione --</option>
                                        <?php $tipoViejo = (int) ($datos_viejos['id_tipo_respuesta'] ?? 0); ?>
                                        <?php foreach ($tiposRespuesta as $t): ?>
                                            <option value="<?= $t['id_tipo_doc'] ?>" <?= $tipoViejo === (int) $t['id_tipo_doc'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php foreach ($errors['id_tipo_respuesta'] ?? [] as $e): ?>
                                        <div class="text-danger small"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endforeach; ?>
                                    <div class="form-text">Ej: Oficio, Resolución, Informe, Memorando.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="num_respuesta" class="form-label fw-bold">
                                        Número de documento emitido <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="num_respuesta" id="num_respuesta" maxlength="100"
                                           class="form-control<?= $err('num_respuesta') ?>" required
                                           placeholder="Oficio N° 124-2026-MDL/GDU" value="<?= $old('num_respuesta') ?>">
                                    <?php foreach ($errors['num_respuesta'] ?? [] as $e): ?>
                                        <div class="text-danger small"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label for="resumen" class="form-label fw-bold">
                                    Resumen o conclusión de la respuesta <span class="text-danger">*</span>
                                </label>
                                <textarea name="resumen" id="resumen" rows="5" maxlength="500"
                                          class="form-control<?= $err('resumen') ?>" required
                                          placeholder="Síntesis de lo resuelto o informado al administrado (quedará registrado en la trazabilidad del expediente)..."><?= $old('resumen') ?></textarea>
                                <?php foreach ($errors['resumen'] ?? [] as $e): ?>
                                    <div class="text-danger small"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endforeach; ?>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-4">
                                    <label for="folios_respuesta" class="form-label fw-bold">Folios <span class="text-danger">*</span></label>
                                    <input type="number" name="folios_respuesta" id="folios_respuesta" min="1" max="9999"
                                           class="form-control<?= $err('folios_respuesta') ?>" required
                                           value="<?= $old('folios_respuesta', '1') ?>">
                                    <?php foreach ($errors['folios_respuesta'] ?? [] as $e): ?>
                                        <div class="text-danger small"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="col-md-4">
                                    <label for="fecha_respuesta" class="form-label fw-bold">Fecha del documento</label>
                                    <input type="date" name="fecha_respuesta" id="fecha_respuesta"
                                           class="form-control<?= $err('fecha_respuesta') ?>"
                                           value="<?= $old('fecha_respuesta', date('Y-m-d')) ?>">
                                    <?php foreach ($errors['fecha_respuesta'] ?? [] as $e): ?>
                                        <div class="text-danger small"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-3">
                                <label for="adjuntos" class="form-label fw-bold">
                                    Adjuntar respuesta en PDF <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="adjuntos[]" id="adjuntos" multiple accept="application/pdf,.pdf"
                                       class="form-control<?= $err('adjuntos') ?>" required>
                                <?php foreach ($errors['adjuntos'] ?? [] as $e): ?>
                                    <div class="text-danger small"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endforeach; ?>
                                <div class="form-text">
                                    Documento firmado y sellado (escaneo o PDF generado). Máximo 10 MB por archivo.
                                    Se registra como <code>tipo_documento_registro = Respuesta</code>.
                                </div>
                            </div>

                            <div class="alert alert-info small mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Al confirmar, el expediente pasará a estado <strong>Atendido</strong>, la derivación se cerrará
                                y quedará registro en el historial y en la auditoría del sistema.
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2-all me-1"></i>Registrar Atención y Respuesta
                                </button>
                                <a href="/area/bandeja-entrada?tab=en_proceso" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
