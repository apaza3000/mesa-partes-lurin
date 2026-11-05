<?php
/**
 * Vista: Confirmación de registro en ventanilla (TAREA E02-1)
 * Variables recibidas:
 * @var string $titulo
 * @var array $expediente
 */

$expediente = $expediente ?? [];
$e = fn(?string $v): string => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$codigo = (string) ($expediente['codigo'] ?? '');
$idExpediente = (int) ($expediente['id_expediente'] ?? 0);
?>

<div class="app-content-header">
    <div class="container-fluid">
        <h1 class="mb-0 fs-3"><i class="bi bi-patch-check-fill text-success me-2"></i>Documento registrado correctamente</h1>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">

                <div class="card shadow-sm border-0 border-start border-success border-4">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-inbox-fill text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-muted text-uppercase small fw-bold mb-1">Código único de expediente</h5>
                        <h2 class="display-6 fw-bold text-primary mb-3"><?= $e($codigo) ?></h2>

                        <div class="row text-start g-2 justify-content-center">
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100">
                                    <div class="small text-muted text-uppercase fw-bold">Remitente</div>
                                    <div class="fw-semibold"><?= $e((string) ($expediente['remitente'] ?? '-')) ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100">
                                    <div class="small text-muted text-uppercase fw-bold">Canal / Prioridad</div>
                                    <div class="fw-semibold">
                                        <?= $e((string) ($expediente['canal'] ?? '-')) ?>
                                        <?php if (($expediente['prioridad'] ?? '') === 'Urgente'): ?>
                                            <span class="badge bg-danger ms-1">URGENTE</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary ms-1">NORMAL</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-2">
                                    <div class="small text-muted text-uppercase fw-bold">Asunto</div>
                                    <div class="fw-semibold"><?= $e((string) ($expediente['asunto'] ?? '-')) ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100">
                                    <div class="small text-muted text-uppercase fw-bold">Documento / Folios</div>
                                    <div class="fw-semibold">
                                        <?= $e((string) ($expediente['documento'] ?? '-')) ?>
                                        <span class="text-muted">— <?= (int) ($expediente['folios'] ?? 1) ?> folio(s)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-2 h-100">
                                    <div class="small text-muted text-uppercase fw-bold">Derivación inicial</div>
                                    <div class="fw-semibold">
                                        <?php if (!empty($expediente['area_destino'])): ?>
                                            <span class="badge bg-primary"><?= $e((string) $expediente['area_destino']) ?></span>
                                            <span class="d-block small text-muted">En bandeja del área, pendiente de recepción</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pendiente de derivar</span>
                                            <span class="d-block small text-muted">Registre la derivación desde la bandeja</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info text-start mt-3 mb-0 small">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            El ciudadano puede seguir su trámite en el portal público de consulta con el código
                            <strong><?= $e($codigo) ?></strong>.
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                            <a href="/mesa-partes/cargo/<?= $idExpediente ?>" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                                <i class="bi bi-printer-fill me-1"></i>Imprimir cargo de recepción
                            </a>
                            <a href="/mesa-partes/registrar" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Registrar otro documento
                            </a>
                            <a href="/mesa-partes/bandeja" class="btn btn-outline-primary">
                                <i class="bi bi-inbox me-1"></i>Ver bandeja del día
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
