<div class="app-content-header">
    <div class="container-fluid">
        <h1 class="mb-0 fs-3">Registrar Nuevo Rol</h1>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                error
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline mb-4">
                    <form action="/roles/store" method="POST">
                        <div class="card-body">
                            <div class="form-group mb-3 has-validation">
                                <label for="nombre" class="form-label">Nombre del Rol</label>
                                <input type="text" class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre"
                                    value="<?= htmlspecialchars($datos_viejos['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                                <?php if (isset($errors['nombre'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', $errors['nombre']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group mb-3 has-validation">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control <?= isset($errors['descripcion']) ? 'is-invalid' : '' ?>" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($datos_viejos['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                <?php if (isset($errors['descripcion'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', $errors['descripcion']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group mb-3 has-validation">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select <?= isset($errors['estado']) ? 'is-invalid' : '' ?>" id="estado" name="estado">
                                    <option value="Activo" <?= ($datos_viejos['estado'] ?? 'Activo') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="Inactivo" <?= ($datos_viejos['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                                <?php if (isset($errors['estado'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', $errors['estado']) ?></div>
                                <?php endif; ?>
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