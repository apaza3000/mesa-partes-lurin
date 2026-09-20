<?php
$areasPadre = $areasPadre ?? [];
$errors = $errors ?? [];
$datos_viejos = $datos_viejos ?? [];
?>

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
                    <form action="/areas/store" method="POST">
                        <div class="card-body">
                            <div class="form-group mb-3 has-validation">
                                <label for="id_area_padre" class="form-label">Área superior</label>
                                <select class="form-select <?php echo isset($errors['id_area_padre']) ? 'is-invalid' : ''; ?>" id="id_area_padre" name="id_area_padre" value="<?= $datos_viejos['id_area_padre'] ?? '' ?>">
                                    <option value="">Sin área superior</option>
                                    <?php foreach ($areasPadre as $areaPadre): ?>
                                        <option value="<?= (int) $areaPadre['id_area'] ?>"><?= htmlspecialchars($areaPadre['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    <?php echo  isset($errors['id_area_padre']) ? "* " . implode('<br>', $errors['id_area_padre']) : ''; ?>
                                </div>
                            </div>
                            <div class="form-group mb-3 has-validation">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control <?php echo isset($errors['nombre']) ? 'is-invalid' : ''; ?>" id="nombre" name="nombre" placeholder="Ej. Dirección" value="<?= $datos_viejos['nombre'] ?? '' ?>" required>
                                <div class="invalid-feedback">
                                    <?php echo isset($errors['nombre']) ?  "* " . implode('<br>', $errors['nombre']) : ''; ?>
                                </div>
                            </div>
                            <div class="form-group mb-3 has-validation">
                                <label for="siglas" class="form-label">Siglas</label>
                                <input type="text" class="form-control <?php echo isset($errors['siglas']) ? 'is-invalid' : ''; ?>" id="siglas" name="siglas" placeholder="Ej. DIR" value="<?= $datos_viejos['siglas'] ?? '' ?>" required>
                                <div class="invalid-feedback">
                                    <?php echo isset($errors['siglas']) ?  "* " . implode('<br>', $errors['siglas']) : ''; ?>
                                </div>
                            </div>
                            <div class="form-group has-validation mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select <?php echo isset($errors['estado']) ? 'is-invalid' : ''; ?>" id="estado" name="estado" value="<?= $datos_viejos['estado'] ?? '' ?>">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                                <div class="invalid-feedback">
                                    <?php echo isset($errors['estado']) ?  "* " . implode('<br>', $errors['estado']) : ''; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Guardar Área</button>
                            <a href="/areas" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


</div>