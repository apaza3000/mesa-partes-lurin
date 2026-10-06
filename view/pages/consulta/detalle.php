<?php
$estadoColores = [
    'Registrado' => 'secondary',
    'En_Proceso' => 'info',
    'Observado'  => 'warning',
    'Atendido'   => 'success',
    'Archivado'  => 'dark',
];
$colorEstado = $estadoColores[$expediente['estado_actual']] ?? 'secondary';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Expediente <?= htmlspecialchars($expediente['codigo_expediente']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body.detalle-page {
            background: #f5f7fb;
            min-height: 100vh;
            padding: 30px 15px;
        }

        .detalle-box {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header-card {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #fff;
            border-radius: 12px;
            padding: 25px 30px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .2);
            margin-bottom: 20px;
        }

        .header-card h3 {
            margin: 0 0 6px;
            font-weight: 700;
        }

        .header-card .codigo {
            font-size: 1.4rem;
            font-weight: 700;
            background: rgba(255, 255, 255, .15);
            display: inline-block;
            padding: 6px 16px;
            border-radius: 6px;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .header-card .meta {
            font-size: .9rem;
            opacity: .9;
        }

        .badge-estado {
            font-size: .85rem;
            padding: 6px 14px;
            border-radius: 20px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, .06);
            border: none;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 700;
            color: #1e3c72;
            font-size: 1rem;
        }

        .card-header h5 i {
            margin-right: 8px;
        }

        .info-item {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
            font-size: .93rem;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item .label {
            color: #666;
            font-weight: 600;
            width: 140px;
            flex-shrink: 0;
        }

        .info-item .value {
            color: #222;
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: "";
            position: absolute;
            top: 0;
            left: 9px;
            height: 100%;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 22px;
        }

        .timeline-item::before {
            content: "";
            position: absolute;
            left: -30px;
            top: 3px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #1e3c72;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #1e3c72;
        }

        .timeline-item .fecha {
            font-size: .78rem;
            color: #888;
        }

        .timeline-item .estado {
            font-weight: 700;
            color: #1e3c72;
            font-size: .95rem;
        }

        .timeline-item .comentario {
            font-size: .9rem;
            color: #555;
        }

        /* Adjuntos */
        .adjunto-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 8px;
            transition: all .2s;
        }

        .adjunto-item:hover {
            background: #eef2f9;
            transform: translateX(4px);
        }

        .adjunto-item .info {
            display: flex;
            align-items: center;
        }

        .adjunto-item i.fa-file {
            font-size: 1.6rem;
            color: #1e3c72;
            margin-right: 12px;
        }

        .adjunto-item .nombre {
            font-weight: 600;
            color: #333;
            font-size: .93rem;
        }

        .adjunto-item .tam {
            font-size: .78rem;
            color: #888;
        }

        .btn-descargar {
            background: #1e3c72;
            color: #fff;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
        }

        .btn-descargar:hover {
            background: #2a5298;
            color: #fff;
            text-decoration: none;
        }

        .doc-principal {
            border-left: 4px solid #1e3c72;
            background: #f1f5fb;
        }

        .no-data {
            text-align: center;
            color: #999;
            padding: 20px;
            font-style: italic;
            font-size: .9rem;
        }
    </style>
</head>

<body class="detalle-page">

    <div class="detalle-box">

        <!-- ===== HEADER ===== -->
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <div class="codigo"><?= htmlspecialchars($expediente['codigo_expediente']) ?></div>
                    <h3><?= htmlspecialchars($expediente['asunto']) ?></h3>
                    <div class="meta">
                        <i class="fas fa-calendar"></i>
                        Registrado el <?= date('d/m/Y H:i', strtotime($expediente['fecha_registro'])) ?>
                        <span class="mx-2">•</span>
                        <i class="fas fa-flag"></i>
                        Prioridad: <strong><?= htmlspecialchars($expediente['prioridad']) ?></strong>
                    </div>
                </div>
                <div class="text-right">
                    <span class="badge badge-<?= $colorEstado ?> badge-estado">
                        <?= str_replace('_', ' ', $expediente['estado_actual']) ?>
                    </span>
                    <div class="mt-2">
                        <a href="/consulta" class="btn btn-light btn-sm">
                            <i class="fas fa-search"></i> Nueva consulta
                        </a>
                        <a href="/" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- ===== Columna izquierda: datos ===== -->
            <div class="col-lg-6">

                <!-- Remitente -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5><i class="fas fa-user"></i> Remitente</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $nombreCompleto = trim(
                            ($expediente['nombres'] ?? '') . ' ' .
                                ($expediente['apellido_paterno'] ?? '') . ' ' .
                                ($expediente['apellido_materno'] ?? '')
                        );
                        if (empty($nombreCompleto) && !empty($expediente['razon_social'])) {
                            $nombreCompleto = $expediente['razon_social'];
                        }
                        ?>
                        <div class="info-item">
                            <div class="label">Documento</div>
                            <div class="value">
                                <?= htmlspecialchars($expediente['tipo_documento']) ?>
                                <?= htmlspecialchars($expediente['numero_documento']) ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="label">Nombre</div>
                            <div class="value"><?= htmlspecialchars($nombreCompleto) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Correo</div>
                            <div class="value"><?= htmlspecialchars($expediente['email'] ?? '—') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Teléfono</div>
                            <div class="value"><?= htmlspecialchars($expediente['telefono'] ?? '—') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Dirección</div>
                            <div class="value"><?= htmlspecialchars($expediente['direccion'] ?? '—') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Expediente -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5><i class="fas fa-folder-open"></i> Expediente</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <div class="label">Código</div>
                            <div class="value"><strong><?= htmlspecialchars($expediente['codigo_expediente']) ?></strong></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Canal</div>
                            <div class="value"><?= htmlspecialchars($expediente['canal_ingreso']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="label">Fecha registro</div>
                            <div class="value"><?= date('d/m/Y H:i', strtotime($expediente['fecha_registro'])) ?></div>
                        </div>
                        <?php if (!empty($expediente['fecha_atencion'])): ?>
                            <div class="info-item">
                                <div class="label">Fecha atención</div>
                                <div class="value"><?= date('d/m/Y H:i', strtotime($expediente['fecha_atencion'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($expediente['fecha_archivado'])): ?>
                            <div class="info-item">
                                <div class="label">Fecha archivo</div>
                                <div class="value"><?= date('d/m/Y H:i', strtotime($expediente['fecha_archivado'])) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Observaciones -->
                <?php if (!empty($observaciones)): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><i class="fas fa-exclamation-circle text-warning"></i> Observaciones</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($observaciones as $o): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars(trim(($o['nombres'] ?? '') . ' ' . ($o['apellido_paterno'] ?? ''))) ?: 'Sistema' ?></strong>
                                        <span class="badge badge-<?= $o['estado'] === 'Subsanada' ? 'success' : 'warning' ?>">
                                            <?= $o['estado'] ?>
                                        </span>
                                    </div>
                                    <p class="mb-1 mt-2"><?= htmlspecialchars($o['descripcion']) ?></p>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($o['fecha_registro'])) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- ===== Columna derecha: historial + documentos ===== -->
            <div class="col-lg-6">

                <!-- Documentos y adjuntos -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5><i class="fas fa-file-alt"></i> Documentos y adjuntos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($documentos)): ?>
                            <div class="no-data">No hay documentos registrados.</div>
                        <?php else: ?>
                            <?php foreach ($documentos as $doc): ?>
                                <div class="mb-3 <?= $doc['es_principal'] ? 'doc-principal p-3 rounded' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= htmlspecialchars($doc['tipo_doc_nombre']) ?></strong>
                                            <?php if ($doc['es_principal']): ?>
                                                <span class="badge badge-primary ml-2">Principal</span>
                                            <?php endif; ?>
                                            <div class="text-muted small mt-1">
                                                <?= htmlspecialchars($doc['asunto']) ?>
                                            </div>
                                            <div class="text-muted small">
                                                <?php if (!empty($doc['numero_documento'])): ?>
                                                    N° <?= htmlspecialchars($doc['numero_documento']) ?> •
                                                <?php endif; ?>
                                                <?= (int)$doc['folios'] ?> folio(s)
                                                <?php if (!empty($doc['fecha_documento'])): ?>
                                                    • <?= date('d/m/Y', strtotime($doc['fecha_documento'])) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($doc['adjuntos'])): ?>
                                        <div class="mt-2">
                                            <?php foreach ($doc['adjuntos'] as $a): ?>
                                                <div class="adjunto-item">
                                                    <div class="info">
                                                        <i class="fas fa-file-<?= $a['extension'] === 'pdf' ? 'pdf' : 'alt' ?>"></i>
                                                        <div>
                                                            <div class="nombre"><?= htmlspecialchars($a['nombre_original']) ?></div>
                                                            <div class="tam">
                                                                <?= strtoupper(htmlspecialchars($a['extension'])) ?>
                                                                • <?= number_format($a['tamanio'] / 1024, 1) ?> KB
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a href="/consulta/descargar/<?= (int)$a['id_archivo'] ?>"
                                                        class="btn-descargar">
                                                        <i class="fas fa-download"></i> Descargar
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Historial -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5><i class="fas fa-history"></i> Historial de estados</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($historial)): ?>
                            <div class="no-data">Sin movimientos registrados.</div>
                        <?php else: ?>
                            <div class="timeline">
                                <?php foreach ($historial as $h): ?>
                                    <div class="timeline-item">
                                        <div class="fecha"><?= date('d/m/Y H:i', strtotime($h['fecha'])) ?></div>
                                        <div class="estado">
                                            <?php if ($h['estado_anterior']): ?>
                                                <?= str_replace('_', ' ', $h['estado_anterior']) ?> →
                                            <?php endif; ?>
                                            <?= str_replace('_', ' ', $h['estado_nuevo']) ?>
                                        </div>
                                        <?php if (!empty($h['comentario'])): ?>
                                            <div class="comentario"><?= htmlspecialchars($h['comentario']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Derivaciones -->
                <?php if (!empty($derivaciones)): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5><i class="fas fa-route"></i> Derivaciones</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($derivaciones as $d): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?= htmlspecialchars($d['area_origen']) ?></strong>
                                            <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                            <strong><?= htmlspecialchars($d['area_destino']) ?></strong>
                                        </div>
                                        <span class="badge badge-<?= $d['estado'] === 'Atendido' ? 'success' : 'warning' ?>">
                                            <?= str_replace('_', ' ', $d['estado']) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($d['observaciones'])): ?>
                                        <p class="mb-1 mt-2 small"><?= htmlspecialchars($d['observaciones']) ?></p>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        Enviado: <?= date('d/m/Y H:i', strtotime($d['fecha_envio'])) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>