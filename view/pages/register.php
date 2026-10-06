<?php if (isset($trace)): ?>
    <pre><?php echo htmlspecialchars(print_r($trace, true)); ?></pre>

    <?php return; ?>
<?php endif; ?>




<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Cuenta - Usuario Invitado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/view/assets/css/register.css">
</head>

<body class="register-page">

    <div class="register-box">
        <div class="card">

            <div class="card-header">
                <div class="register-brand">
                    <i class="fas fa-landmark" aria-hidden="true"></i>
                    <span>Mesa de Partes Virtual</span>
                </div>
                <div class="logo-title">Crear Cuenta de Invitado</div>
                <div class="logo-subtitle">Regístrate para consultar y hacer seguimiento a tus expedientes</div>
            </div>

            <form method="POST" action="/register" id="formRegistro" novalidate>
                <div class="card-body">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Corrige los siguientes errores:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $campo => $mensajes): ?>
                                    <?php foreach ((array) $mensajes as $msg): ?>
                                        <li><?= htmlspecialchars($msg) ?></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- ===== Datos personales ===== -->
                    <div class="section-title"><i class="fas fa-id-card"></i> Datos personales</div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Documento *</label>
                                <select name="tipo_documento"
                                    class="form-control <?= isset($errors['tipo_documento']) ? 'is-invalid' : '' ?>"
                                    required>
                                    <?php $td_actual = $datos_viejos['tipo_documento'] ?? 'DNI'; ?>
                                    <?php foreach (['DNI', 'CE', 'Pasaporte', 'Otro'] as $td): ?>
                                        <option value="<?= $td ?>" <?= $td_actual === $td ? 'selected' : '' ?>>
                                            <?= $td ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['tipo_documento'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['tipo_documento']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Número de Documento *</label>
                                <input type="text" name="numero_documento"
                                    class="form-control <?= isset($errors['numero_documento']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($datos_viejos['numero_documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <?php if (isset($errors['numero_documento'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['numero_documento']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nombres *</label>
                                <input type="text" name="nombres"
                                    class="form-control <?= isset($errors['nombres']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($datos_viejos['nombres'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <?php if (isset($errors['nombres'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['nombres']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Apellido Paterno *</label>
                                <input type="text" name="apellido_paterno"
                                    class="form-control <?= isset($errors['apellido_paterno']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($datos_viejos['apellido_paterno'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <?php if (isset($errors['apellido_paterno'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['apellido_paterno']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Apellido Materno</label>
                                <input type="text" name="apellido_materno"
                                    class="form-control <?= isset($errors['apellido_materno']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($datos_viejos['apellido_materno'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <?php if (isset($errors['apellido_materno'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['apellido_materno']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Correo Electrónico *</label>
                                <input type="email" name="email"
                                    class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($datos_viejos['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="telefono"
                                    class="form-control <?= isset($errors['telefono']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($datos_viejos['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <?php if (isset($errors['telefono'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['telefono']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion"
                            class="form-control <?= isset($errors['direccion']) ? 'is-invalid' : '' ?>"
                            value="<?= htmlspecialchars($datos_viejos['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['direccion'])): ?>
                            <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['direccion']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- ===== Datos de acceso ===== -->
                    <div class="section-title"><i class="fas fa-lock"></i> Datos de acceso</div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Usuario *</label>
                                <input type="text" name="username"
                                    class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                    value="<?= htmlspecialchars($datos_viejos['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required>
                                <?php if (isset($errors['username'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['username']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Contraseña *</label>
                                <input type="password" name="password"
                                    class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                    minlength="6" required>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['password']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Confirmar Contraseña *</label>
                                <input type="password" name="password_confirm"
                                    class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                                    minlength="6" required>
                                <?php if (isset($errors['password_confirm'])): ?>
                                    <div class="invalid-feedback">*<?= implode('<br>', (array) $errors['password_confirm']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group form-check mt-3">
                        <input type="checkbox" class="form-check-input <?= isset($errors['terminos']) ? 'is-invalid' : '' ?>"
                            id="terminos" name="terminos" required>
                        <label class="form-check-label" for="terminos">
                            Acepto los términos y condiciones del servicio
                        </label>
                        <?php if (isset($errors['terminos'])): ?>
                            <div class="invalid-feedback d-block">*<?= implode('<br>', (array) $errors['terminos']) ?></div>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="card-footer bg-white border-top">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-user-plus"></i> Crear Cuenta
                    </button>
                </div>
            </form>
        </div>

        <div class="login-link">
            ¿Ya tienes una cuenta? <a href="/login">Inicia sesión aquí</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/view/assets/js/register/register.js"></script>

</body>

</html>