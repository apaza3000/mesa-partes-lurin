<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Gestión de Roles</h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="plantilla.php?p=roles/crear" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nuevo Rol
                </a>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        
        <!-- Alertas de éxito o error -->
        <?php if (isset($_SESSION['rol_mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['rol_mensaje']; unset($_SESSION['rol_mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['rol_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['rol_error']; unset($_SESSION['rol_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Lista de Roles Registrados</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Rol</th>
                            <th>Slug</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $r): ?>
                                <tr>
                                    <td><?= $r['id']; ?></td>
                                    <td><?= htmlspecialchars($r['nombre_rol']); ?></td>
                                    <td><code><?= htmlspecialchars($r['slug']); ?></code></td>
                                    <td>
                                        <span class="badge <?= $r['estado'] == 1 ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?= $r['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="plantilla.php?p=roles/editar&id=<?= $r['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        
                                        <!-- Formulario para cambiar estado -->
                                        <form action="plantilla.php?p=roles/estado" method="POST" style="display:inline-block;">
                                            <input type="hidden" name="id" value="<?= $r['id']; ?>">
                                            <input type="hidden" name="estado" value="<?= $r['estado'] == 1 ? 0 : 1; ?>">
                                            <button type="submit" class="btn btn-sm <?= $r['estado'] == 1 ? 'btn-danger' : 'btn-success'; ?>" title="Cambiar estado">
                                                <i class="bi <?= $r['estado'] == 1 ? 'bi-lock' : 'bi-unlock'; ?>"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay roles registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!--end::App Content-->