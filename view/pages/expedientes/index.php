<?php $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); ?>
<div class="app-content-header">
    <div class="container-fluid d-flex justify-content-between">
        <h1 class="fs-3">Expedientes</h1>
        <a href="/expedientes/crear" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo expediente</a>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body table-responsive">
                <table id="tabla-expedientes" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Remitente</th>
                            <th>Asunto</th>
                            <th>Canal</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expedientes as $expediente): ?>
                        <tr>
                            <td><strong><?= $e($expediente['codigo_expediente']) ?></strong></td>
                            <td><?= $e(trim(($expediente['razon_social'] ?: '') ?: implode(' ', [$expediente['nombres'], $expediente['apellido_paterno'], $expediente['apellido_materno']]))) ?>
                            </td>
                            <td><?= $e($expediente['asunto']) ?></td>
                            <td><?= $e($expediente['canal_ingreso']) ?></td>
                            <td><span
                                    class="badge <?= $expediente['prioridad'] === 'Urgente' ? 'text-bg-danger' : 'text-bg-secondary' ?>"><?= $e($expediente['prioridad']) ?></span>
                            </td>
                            <td><?= $e(str_replace('_', ' ', $expediente['estado_actual'])) ?></td>
                            <td><?= $e($expediente['fecha_registro']) ?></td>
                            <td><a href="/expedientes/<?= (int) $expediente['id_expediente'] ?>"
                                    class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="/public/assets/js/expediente/index.js"></script>