<?php
$areas = $areas ?? [];
$roles = $roles ?? [];
?>
<div class="app-content-header">
    <div class="container-fluid">
        <h1 class="mb-0 fs-3">Registrar Nuevo Usuario</h1>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Alerta general para errores de BD o globales -->
        <?php if (!empty($errors['db'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($errors['db'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card card-primary card-outline mb-4">

                    <form id="form-crear-usuario" action="/usuarios/store" method="POST" class="needs-validation" novalidate>
                        <!-- CSRF Token de seguridad -->
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <!-- SECCIÓN 1: DATOS PERSONALES -->
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-plus me-1"></i> Datos Personales</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Tipo Documento -->
                                <div class="col-md-4 col-lg-3 col-sm-6 mb-3">
                                    <label for="tipo_documento" class="form-label">Tipo Documento</label>
                                    <?php $tipoDocOld = $datos_viejos['tipo_documento'] ?? 'DNI'; ?>
                                    <select name="tipo_documento" id="tipo_documento" class="form-select <?= isset($errors['tipo_documento']) ? 'is-invalid' : '' ?>" required>
                                        <option value="DNI" <?= $tipoDocOld === 'DNI' ? 'selected' : '' ?>>DNI</option>
                                        <option value="CE" <?= $tipoDocOld === 'CE' ? 'selected' : '' ?>>CE</option>
                                        <option value="Pasaporte" <?= $tipoDocOld === 'Pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
                                    </select>
                                    <?php if (isset($errors['tipo_documento'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['tipo_documento']) ? implode('<br>', $errors['tipo_documento']) : $errors['tipo_documento'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- N° Documento -->
                                <div class="col-md-4 col-lg-3 col-sm-6 mb-3">
                                    <label for="numero_documento" class="form-label">N° Documento</label>
                                    <input type="text" name="numero_documento" id="numero_documento"
                                        class="form-control <?= isset($errors['numero_documento']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['numero_documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        required maxlength="20">
                                    <?php if (isset($errors['numero_documento'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['numero_documento']) ? implode('<br>', $errors['numero_documento']) : $errors['numero_documento'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Correo Electrónico -->
                                <div class="col-md-4 col-lg-3 col-sm-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email"
                                        class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        required placeholder="ejemplo@correo.com">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['email']) ? implode('<br>', $errors['email']) : $errors['email'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Nombres -->
                                <div class="col-md-4 mb-3">
                                    <label for="nombres" class="form-label">Nombres</label>
                                    <input type="text" name="nombres" id="nombres"
                                        class="form-control <?= isset($errors['nombres']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['nombres'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                                    <?php if (isset($errors['nombres'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['nombres']) ? implode('<br>', $errors['nombres']) : $errors['nombres'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Apellido Paterno -->
                                <div class="col-md-4 mb-3">
                                    <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                                    <input type="text" name="apellido_paterno" id="apellido_paterno"
                                        class="form-control <?= isset($errors['apellido_paterno']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['apellido_paterno'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                                    <?php if (isset($errors['apellido_paterno'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['apellido_paterno']) ? implode('<br>', $errors['apellido_paterno']) : $errors['apellido_paterno'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Apellido Materno -->
                                <div class="col-md-4 mb-3">
                                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                    <input type="text" name="apellido_materno" id="apellido_materno"
                                        class="form-control <?= isset($errors['apellido_materno']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['apellido_materno'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if (isset($errors['apellido_materno'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['apellido_materno']) ? implode('<br>', $errors['apellido_materno']) : $errors['apellido_materno'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>


                                <!-- Teléfono -->
                                <div class="col-md-4 mb-3">
                                    <label for="telefono" class="form-label">Teléfono celular</label>
                                    <input
                                        type="text"
                                        name="telefono"
                                        id="telefono"
                                        class="form-control <?= isset($errors['telefono']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if (isset($errors['telefono'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['telefono'])
                                                ? implode('<br>', $errors['telefono'])
                                                : htmlspecialchars($errors['telefono'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Direccion -->
                                <div class="col-md-8 mb-3">
                                    <label for="direccion" class="form-label">Direccion</label>
                                    <input
                                        type="text"
                                        name="direccion"
                                        id="direccion"
                                        class="form-control <?= isset($errors['direccion']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if (isset($errors['direccion'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['direccion'])
                                                ? implode('<br>', $errors['direccion'])
                                                : htmlspecialchars($errors['direccion'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <hr class="my-0">

                        <!-- SECCIÓN 2: CREDENCIALES Y ASIGNACIÓN -->
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-key me-1"></i> Credenciales y Asignación</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Username -->
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Usuario (Username)</label>
                                    <input type="text" name="username" id="username"
                                        class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                        value="<?= htmlspecialchars($datos_viejos['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        required placeholder="jdoe">
                                    <?php if (isset($errors['username'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['username']) ? implode('<br>', $errors['username']) : $errors['username'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" name="password" id="password"
                                        class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                        required minlength="8">
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['password']) ? implode('<br>', $errors['password']) : $errors['password'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Área Asignada -->
                                <div class="col-md-6 mb-3">
                                    <label for="id_area" class="form-label">Área Asignada</label>
                                    <select name="id_area" id="id_area"
                                        class="form-select <?= isset($errors['id_area']) ? 'is-invalid' : '' ?>" required>
                                        <option value="" disabled <?= empty($datos_viejos['id_area']) ? 'selected' : '' ?>>Seleccione Un Área</option>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?= $area['id_area'] ?>"
                                                <?= (isset($datos_viejos['id_area']) && $datos_viejos['id_area'] == $area['id_area']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($area['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['id_area'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['id_area']) ? implode('<br>', $errors['id_area']) : $errors['id_area'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Rol del Usuario -->
                                <div class="col-md-6 mb-3">
                                    <label for="id_rol" class="form-label">Rol del Usuario</label>
                                    <select name="id_rol" id="id_rol"
                                        class="form-select <?= isset($errors['id_rol']) ? 'is-invalid' : '' ?>" required>
                                        <option value="" disabled <?= empty($datos_viejos['id_rol']) ? 'selected' : '' ?>>Seleccione Un Rol</option>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?= $rol['id_rol'] ?>"
                                                <?= (isset($datos_viejos['id_rol']) && $datos_viejos['id_rol'] == $rol['id_rol']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['id_rol'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['id_rol']) ? implode('<br>', $errors['id_rol']) : $errors['id_rol'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- BOTONES DE ACCIÓN -->
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-save me-1"></i> Guardar Usuario
                            </button>
                            <a href="/usuarios" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>