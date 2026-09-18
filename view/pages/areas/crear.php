<div class="app-content-header">
    <div class="container-fluid">
        <h1 class="mb-0 fs-3">Registrar Nueva Área</h1>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline mb-4">
                    <form action="plantilla.php?p=areas/guardar" method="POST">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="id_area_padre" class="form-label">Área superior (opcional)</label>
                                <select class="form-select" id="id_area_padre" name="id_area_padre">
                                    <option value="">Sin área superior</option>
                                    <?php foreach ($areasPadre as $areaPadre): ?>
                                        <option value="<?= (int) $areaPadre['id_area'] ?>"><?= htmlspecialchars($areaPadre['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Área</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej. Dirección" required>
                            </div>
                            <div class="mb-3">
                                <label for="siglas" class="form-label">Siglas</label>
                                <input type="text" class="form-control" id="siglas" name="siglas" placeholder="Ej. DIR" required>
                            </div>
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Guardar Área</button>
                            <a href="plantilla.php?p=areas/index" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>