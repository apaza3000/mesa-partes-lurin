<div class="app-content-header">
    <div class="container-fluid"><h1 class="mb-0 fs-3">Registrar Nuevo Rol</h1></div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars(is_array($error) ? implode(', ', $error) : $error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline mb-4">
                    <form action="/roles/store" method="POST">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Rol</label>
                                <input type="text" class="form-control" id="nombre" name="nombre"
                                    value="<?= htmlspecialchars($datos_viejos['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($datos_viejos['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="Activo" <?= ($datos_viejos['estado'] ?? 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="Inactivo" <?= ($datos_viejos['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Guardar Rol</button>
                            <a href="/roles" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
