<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Editar Rol</h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="plantilla.php?p=roles/index" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        <div class="card card-warning card-outline mb-4">
            <form action="plantilla.php?p=roles/actualizar" method="POST">
                <input type="hidden" name="id" value="<?= $rol['id'] ?? ''; ?>">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nombre_rol" class="form-label">Nombre del Rol</label>
                        <input type="text" class="form-control" id="nombre_rol" name="nombre_rol" value="<?= htmlspecialchars($rol['nombre_rol'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($rol['slug'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-control" id="estado" name="estado">
                            <option value="1" <?= (isset($rol['estado']) && $rol['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?= (isset($rol['estado']) && $rol['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">Actualizar Rol</button>
                    <a href="plantilla.php?p=roles/index" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end::App Content-->