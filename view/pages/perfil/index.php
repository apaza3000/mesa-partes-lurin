<?php
$usuario = $usuario ?? [];
$flash = $flash ?? [];
$csrf_token = $csrf_token ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Mi perfil</title></head>
<body>
<div class="container mt-4">
    <h2>Mi perfil</h2>
    <?php foreach ($flash as $f): ?>
        <div class="alert alert-<?= htmlspecialchars($f['tipo'] ?? 'info') ?>"><?= htmlspecialchars($f['mensaje'] ?? '') ?></div>
    <?php endforeach; ?>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Usuario:</strong> <?= htmlspecialchars($usuario['username'] ?? '') ?></p>
            <p><strong>Nombre:</strong> <?= htmlspecialchars(trim(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellido_paterno'] ?? '') . ' ' . ($usuario['apellido_materno'] ?? ''))) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($usuario['email'] ?? '') ?></p>
            <p><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol_nombre'] ?? '') ?></p>
            <p><strong>Área:</strong> <?= htmlspecialchars($usuario['area_nombre'] ?? '') ?></p>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Cambiar contraseña</div>
        <div class="card-body">
            <form action="/perfil" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-3">
                    <label>Contraseña actual</label>
                    <input type="password" name="password_actual" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Nueva contraseña</label>
                    <input type="password" name="password_nueva" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>