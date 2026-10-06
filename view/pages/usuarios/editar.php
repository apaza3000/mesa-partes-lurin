<div class="app-content-header">
    <div class="container-fluid">
        <h1 class="mb-0 fs-2">Editar usuario:<?= htmlspecialchars($usuario['nombres'] ?? '') ?></h1>
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
            <div class="col-md-10 col-lg-10">
                <div class="card card-warning card-outline mb-4">

                    <form action="/usuarios/<?= $usuario['id_usuario'] ?> " class="needs-validation" method="POST">
                        <!-- Campo oculto con el ID del usuario -->
                        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">


                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-plus me-1"></i> Datos Personales</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tipo Documento</label>
                                    <select name="tipo_documento" class="form-select" required>
                                        <option value="DNI" <?= ($usuario['tipo_documento'] ?? '') === 'DNI' ? 'selected' : '' ?>>
                                            DNI
                                        </option>
                                        <option value="RUC" <?= ($usuario['tipo_documento'] ?? '') === 'CE' ? 'selected' : '' ?>>
                                            CE
                                        </option>
                                        <option value="CE" <?= ($usuario['tipo_documento'] ?? '') === 'Pasaporte' ? 'selected' : '' ?>>
                                            Pasaporte</option>
                                    </select>
                                    <?php if (isset($errors['tipo_documento'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['tipo_documento']) ? implode('<br>', $errors['tipo_documento']) : $errors['tipo_documento'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">N° Documento</label>
                                    <input type="text" name="numero_documento" class="form-control"
                                        value="<?= htmlspecialchars($usuario['numero_documento'] ?? '') ?>" required>
                                    <?php if (isset($errors['numero_documento'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['numero_documento']) ? implode('<br>', $errors['numero_documento']) : $errors['numero_documento'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['email']) ? implode('<br>', $errors['email']) : $errors['email'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nombres</label>
                                    <input type="text" name="nombres" class="form-control"
                                        value="<?= htmlspecialchars($usuario['nombres'] ?? '') ?>" required>
                                    <?php if (isset($errors['nombres'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['nombres']) ? implode('<br>', $errors['nombres']) : $errors['nombres'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Apellido Paterno</label>
                                    <input type="text" name="apellido_paterno" class="form-control"
                                        value="<?= htmlspecialchars($usuario['apellido_paterno'] ?? '') ?>" required>
                                    <?php if (isset($errors['apellido_paterno'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['apellido_paterno']) ? implode('<br>', $errors['apellido_paterno']) : $errors['apellido_paterno'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Apellido Materno</label>
                                    <input type="text" name="apellido_materno" class="form-control"
                                        value="<?= htmlspecialchars($usuario['apellido_materno'] ?? '') ?>" required>
                                    <?php if (isset($errors['apellido_materno'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['apellido_materno']) ? implode('<br>', $errors['apellido_materno']) : $errors['apellido_materno'] ?>
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
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control"
                                        value="<?= htmlspecialchars($usuario['username'] ?? '') ?>" required>
                                </div>
                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Nueva Contraseña</label>
                                    <input type="password" name="password" id="password"
                                        class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                        minlength="8" placeholder="Dejar en blanco para mantener la contraseña actual">
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback">
                                            <?= is_array($errors['password']) ? implode('<br>', $errors['password']) : $errors['password'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Área Asignada -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Área</label>
                                    <select name="id_area" class="form-select">
                                        <option value="">Seleccione un área</option>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?= $area['id_area'] ?>"
                                                <?= ($usuario['id_area'] == $area['id_area']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($area['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- Rol del Usuario -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Rol</label>
                                    <select name="id_rol" class="form-select" required>
                                        <option value="">Seleccione un rol</option>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?= $rol['id_rol'] ?>" <?= ($usuario['id_rol'] == $rol['id_rol']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rol['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <!-- Estado -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" class="form-select" required>
                                        <option value="Activo" <?= ($usuario['estado'] === 'Activo') ? 'selected' : '' ?>>
                                            Activo
                                        </option>
                                        <option value="Inactivo" <?= ($usuario['estado'] === 'Inactivo') ? 'selected' : '' ?>>
                                            Inactivo
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <!-- BOTONES DE ACCIÓN -->
                        <div class="card-footer">
                            <button type="submit" class="btn btn-warning me-2">
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