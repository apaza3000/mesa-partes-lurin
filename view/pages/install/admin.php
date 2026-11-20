<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Administrador - Instalador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white"><h4 class="mb-0">Paso 4: Crear usuario administrador (Director)</h4></div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="post" action="/install/admin">
                        <?= \App\Support\Csrf::campo() ?>
                        <div class="mb-3">
                            <label class="form-label">Tipo de documento</label>
                            <select name="tipo_documento" class="form-select">
                                <?php foreach ($tiposDocumento as $t): ?>
                                    <option value="<?= htmlspecialchars($t) ?>" <?= (($datos['tipo_documento'] ?? 'DNI') === $t) ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Número de documento</label>
                            <input type="text" name="numero_documento" class="form-control" value="<?= htmlspecialchars($datos['numero_documento'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombres" class="form-control" value="<?= htmlspecialchars($datos['nombres'] ?? '') ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellido paterno</label>
                                <input type="text" name="apellido_paterno" class="form-control" value="<?= htmlspecialchars($datos['apellido_paterno'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellido materno</label>
                                <input type="text" name="apellido_materno" class="form-control" value="<?= htmlspecialchars($datos['apellido_materno'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($datos['email'] ?? '') ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($datos['direccion'] ?? '') ?>">
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Usuario (username)</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($datos['username'] ?? '') ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password" name="password_confirm" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">Instalar</button>
                        <a href="/install/env" class="btn btn-secondary">Atrás</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>