<div class="app-content-header">
    <div class="container-fluid">
        <h1 class="mb-0 fs-3">Editar Área</h1>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-warning card-outline mb-4">
                    <?php if (!$area): ?>
                        <div class="alert alert-danger">El área solicitada no existe.</div>
                        <a href="/areas" class="btn btn-secondary">Volver</a>
                    <?php else: ?>
                        <form action="/areas/<?= (int) $area['id_area'] ?>/update" method="POST">
                            <!-- Campo oculto para llevar el ID -->
                            <input type="hidden" name="id_area" value="<?= (int) $area['id_area'] ?>">

                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="id_area_padre" class="form-label">Área superior (opcional)</label>
                                    <select class="form-select" id="id_area_padre" name="id_area_padre">
                                        <option value="">Sin área superior</option>
                                        <?php foreach ($areasPadre as $areaPadre): ?>
                                            <option value="<?= (int) $areaPadre['id_area'] ?>" <?= (int) $area['id_area_padre'] === (int) $areaPadre['id_area'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($areaPadre['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre del Área</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($area['nombre'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="siglas" class="form-label">Siglas</label>
                                    <input type="text" class="form-control" id="siglas" name="siglas" value="<?= htmlspecialchars($area['siglas'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="Activo" <?= $area['estado'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
                                        <option value="Inactivo" <?= $area['estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning text-white">Actualizar Cambios</button>
                                <a href="/areas" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>