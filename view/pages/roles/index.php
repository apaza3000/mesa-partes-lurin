<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h1 class="mb-0 fs-3">Gestión de Roles</h1></div>
            <div class="col-sm-6 text-end">
                <a href="/roles/nuevo" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo Rol
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <?php if (!empty($_SESSION['rol_mensaje']) || !empty($_SESSION['rol_error'])): ?>
            <div class="alert alert-<?= !empty($_SESSION['rol_error']) ? 'danger' : 'success' ?>">
                <?= htmlspecialchars($_SESSION['rol_error'] ?? $_SESSION['rol_mensaje'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['rol_error'], $_SESSION['rol_mensaje']); ?>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header"><h3 class="card-title">Listado de Roles</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th><th>Nombre</th><th>Descripción</th><th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$roles): ?>
                                <tr><td colspan="5" class="text-center py-4">No hay roles registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($roles as $indice => $rol): ?>
                                    <tr>
                                        <td><?= $indice + 1 ?></td>
                                        <td><?= htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($rol['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $rol['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                                                <?= htmlspecialchars($rol['estado'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="/roles/<?= (int) $rol['id_rol'] ?>/editar" class="btn btn-sm btn-warning text-white" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="/roles/<?= (int) $rol['id_rol'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este rol?');">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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
