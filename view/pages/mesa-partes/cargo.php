<?php
/**
 * Vista: Cargo de Recepción imprimible (base para TAREA E03-1)
 * Variables recibidas:
 * @var string $titulo
 * @var array $expediente
 * @var array $documentos
 * @var array $derivaciones
 */

$expediente = $expediente ?? [];
$documentos = $documentos ?? [];
$derivaciones = $derivaciones ?? [];

$e = fn(?string $v): string => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$fechaRegistro = !empty($expediente['fecha_registro']) ? date('d/m/Y H:i', strtotime((string) $expediente['fecha_registro'])) : '—';
$codigo = (string) ($expediente['codigo_expediente'] ?? '');
$consultaUrl = 'https://mesapartes.lurin.gob.pe/consulta?codigo=' . rawurlencode($codigo);
$totalFolios = 0;
foreach ($documentos as $documento) {
    $totalFolios += (int) ($documento['folios'] ?? 0);
}
?>

<style>
    .cargo-recepcion { max-width: 820px; margin: 0 auto; }
    .cargo-recepcion .cargo-marco { border: 2px solid #212529; padding: 24px; }
    .cargo-recepcion .cargo-cabecera { border-bottom: 2px solid #212529; padding-bottom: 12px; }
    .cargo-recepcion table td { padding: 4px 8px; }
    .cargo-recepcion .campo-label { font-size: .7rem; text-transform: uppercase; color: #6c757d; letter-spacing: .03em; }
    .cargo-recepcion .campo-valor { font-weight: 600; }
    .cargo-sello { border: 2px dashed #6c757d; border-radius: 8px; min-height: 110px; }
    @media print {
        .no-print { display: none !important; }
        .app-content, .app-content-header, main, .container-fluid { margin: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
        .cargo-recepcion .cargo-marco { border-width: 1px; }
    }
</style>

<div class="app-content-header no-print">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <h1 class="mb-0 fs-4"><i class="bi bi-receipt text-primary me-2"></i>Cargo de Recepción</h1>
                <p class="text-muted mb-0">Constancia interna del documento registrado en ventanilla.</p>
            </div>
            <div class="col-sm-5 text-sm-end mt-2 mt-sm-0">
                <?php $idExp = (int) ($expediente['id_expediente'] ?? 0); ?>
                <button type="button" class="btn btn-primary" onclick="window.print();">
                    <i class="bi bi-printer-fill me-1"></i>Imprimir cargo
                </button>
                <a href="/mesa-partes/cargo/<?= $idExp ?>/pdf" class="btn btn-success">
                    <i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF
                </a>
                <a href="/mesa-partes/hoja-ruta/<?= $idExp ?>" class="btn btn-outline-primary">
                    <i class="bi bi-signpost-split me-1"></i>Hoja de ruta
                </a>
                <a href="/mesa-partes/bandeja" class="btn btn-outline-secondary">Volver a la bandeja</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="cargo-recepcion">
            <div class="cargo-marco bg-white shadow-sm">

                <!-- Cabecera institucional -->
                <div class="cargo-cabecera d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <img src="/view/assets/img/logo.png" alt="Municipalidad de Lurín" width="60" height="60" class="me-2">
                        <div>
                            <div class="fw-bold text-uppercase">Municipalidad Distrital de Lurín</div>
                            <div class="small">Mesa de Partes — Sistema de Trámite Documentario</div>
                            <div class="small text-muted">Plataforma virtual de recepción documental</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-uppercase small">Cargo de Recepción</div>
                        <div class="fs-5 fw-bold text-primary"><?= $e($codigo) ?></div>
                        <div class="small text-muted">Canal: <?= $e((string) ($expediente['canal_ingreso'] ?? 'Presencial')) ?></div>
                    </div>
                </div>

                <!-- Datos del administrado y del documento -->
                <table class="table table-sm table-borderless mb-3">
                    <tbody>
                        <tr>
                            <td width="50%" class="align-top">
                                <div class="campo-label">Remitente</div>
                                <div class="campo-valor"><?= $e((string) ($expediente['remitente_nombre'] ?? '—')) ?></div>
                                <div class="small text-muted">
                                    <?= $e((string) ($expediente['tipo_documento'] ?? 'DNI')) ?>:
                                    <?= $e((string) ($expediente['numero_documento'] ?? '—')) ?>
                                </div>
                                <div class="small text-muted">
                                    <?= $e((string) ($expediente['direccion'] ?? '')) ?>
                                    <?= !empty($expediente['telefono']) ? ' · Tel. ' . $e((string) $expediente['telefono']) : '' ?>
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="campo-label">Fecha y hora de registro</div>
                                <div class="campo-valor"><?= $e($fechaRegistro) ?></div>
                                <div class="campo-label mt-2">Prioridad</div>
                                <div class="campo-valor"><?= $e((string) ($expediente['prioridad'] ?? 'Normal')) ?></div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="align-top">
                                <div class="campo-label">Asunto del expediente</div>
                                <div class="campo-valor"><?= $e((string) ($expediente['asunto'] ?? '—')) ?></div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Documentos recibidos -->
                <div class="campo-label mb-1">Documentos recibidos</div>
                <table class="table table-sm table-bordered mb-3">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Tipo</th>
                            <th>N° documento</th>
                            <th>Asunto</th>
                            <th class="text-center">Folios</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documentos)): ?>
                            <tr><td colspan="4" class="text-center text-muted small">Sin documentos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($documentos as $documento): ?>
                                <tr>
                                    <td class="small"><?= $e((string) ($documento['tipo_documento_nombre'] ?? 'Documento')) ?></td>
                                    <td class="small"><?= $e((string) ($documento['numero_documento'] ?? '—')) ?></td>
                                    <td class="small"><?= $e((string) ($documento['asunto'] ?? '—')) ?></td>
                                    <td class="small text-center"><?= (int) ($documento['folios'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" class="text-end fw-bold small text-uppercase">Total folios</td>
                                <td class="text-center fw-bold"><?= $totalFolios ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Derivación / trazabilidad -->
                <?php if (!empty($derivaciones)): ?>
                    <div class="campo-label mb-1">Derivación efectuada</div>
                    <ul class="small mb-3">
                        <?php foreach ($derivaciones as $derivacion): ?>
                            <li>
                                <?= $e((string) ($derivacion['area_origen_nombre'] ?? 'Mesa de Partes')) ?>
                                &rarr; <strong><?= $e((string) ($derivacion['area_destino_nombre'] ?? '—')) ?></strong>
                                (<?= $e((string) $derivacion['estado']) ?>)
                                <?php if (!empty($derivacion['fecha_recepcion'])): ?>
                                    — recibido <?= date('d/m/Y H:i', strtotime((string) $derivacion['fecha_recepcion'])) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-warning small py-2 mb-3">
                        El expediente se registró sin derivación inicial; quedará en la bandeja de Mesa de Partes hasta su asignación.
                    </div>
                <?php endif; ?>

                <!-- Cierre: QR / código, firma y sello -->
                <div class="row g-3 align-items-end mt-2">
                    <div class="col-sm-4 text-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=<?= rawurlencode($consultaUrl) ?>"
                             alt="Código QR de seguimiento" width="110" height="110" class="mb-1">
                        <div class="small text-muted">
                            Escanee para seguir su trámite:<br>
                            <span class="fw-semibold"><?= $e($codigo) ?></span>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="cargo-sello d-flex align-items-center justify-content-center">
                                    <span class="small text-muted text-center">Sello y firma<br>del receptor</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border-bottom pt-4 mt-3 text-center">
                                    <div class="small fw-bold text-uppercase">Mesa de Partes</div>
                                    <div class="small text-muted">Documento registrado en el sistema</div>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted mt-2">
                            Conserve este cargo: acredita la recepción de su documento y permite consultar el estado del trámite.
                            El plazo de atención se computa desde la fecha y hora de registro.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
