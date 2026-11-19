<?php
/**
 * TAREA E07-1: listado del catálogo de tipos de documento.
 *
 * @var array $tipos
 * @var string $csrf_token
 */
$tipos = $tipos ?? [];
$csrf_token = $csrf_token ?? \App\Support\Csrf::token();
?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3"><i class="bi bi-file-earmark-text-fill text-primary me-2"></i>Tipos de documento</h1>
                <p class="text-muted mb-0">Catálogo usado por ventanilla, portal público y documentos de respuesta.</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="/tipos-documento/nuevo" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle-fill me-1"></i> Nuevo tipo
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th class="text-center">Documentos</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($tipos === []): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No hay tipos de documento registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tipos as $indice => $tipo): ?>
                                    <tr>
                                        <td><?= $indice + 1 ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars((string) $tipo['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars((string) ($tipo['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-center">
                                            <span class="badge text-bg-light border"><?= (int) ($tipo['total_documentos'] ?? 0) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $tipo['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                                                <?= htmlspecialchars((string) $tipo['estado'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="/tipos-documento/<?= (int) $tipo['id_tipo_doc'] ?>/editar"
                                               class="btn btn-sm btn-warning text-white" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <?php
                                            $tieneDocumentos = (int) ($tipo['total_documentos'] ?? 0) > 0;
                                            $esActivo = $tipo['estado'] === 'Activo';
                                            ?>
                                            <form action="/tipos-documento/<?= (int) $tipo['id_tipo_doc'] ?>/estado"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Confirma el cambio de estado de este tipo de documento?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="estado" value="<?= $esActivo ? 'Inactivo' : 'Activo' ?>">
                                                <button type="submit"
                                                        class="btn btn-sm btn-<?= $esActivo ? 'outline-secondary' : 'outline-success' ?>"
                                                        title="<?= $esActivo ? 'Inactivar' : 'Activar' ?>"
                                                        <?= ($esActivo && $tieneDocumentos) ? 'disabled' : '' ?>>
                                                    <i class="bi bi-<?= $esActivo ? 'toggle-off' : 'toggle-on' ?>"></i>
                                                </button>
                                            </form>
                                            <?php if ($esActivo && $tieneDocumentos): ?>
                                                <div class="small text-muted mt-1">No se puede inactivar: tiene documentos asociados.</div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
