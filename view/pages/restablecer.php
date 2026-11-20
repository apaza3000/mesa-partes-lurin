<?php
$error_message = $error_message ?? "";
$token = $token ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Restablecer contraseña | Mesa de Partes Lurín</title>
    <link rel="stylesheet" href="/view/assets/css/login.css">
</head>
<body>
<main class="login-card">
    <div class="top-bar"></div>
    <section class="login-body" aria-labelledby="restablecer-title">
        <a class="back-home" href="/login" aria-label="Volver al login">
            <span class="back-arrow" aria-hidden="true">&larr;</span>
            Volver al inicio de sesión
        </a>
        <header class="brand-header">
            <h1 class="brand-title" id="restablecer-title">Nueva contraseña</h1>
            <p class="brand-subtitle">IESTP Lurín</p>
            <span class="badge">Sistema de gestión documentaria</span>
        </header>

        <?php foreach (($flash ?? []) as $flashItem): ?>
            <p class="error-text" role="alert"><?= htmlspecialchars($flashItem['mensaje'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        <?php endforeach; ?>

        <form action="/restablecer" method="POST" id="restablecer-form">
            <?= \App\Support\Csrf::campo() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label class="form-label" for="password">Nueva contraseña</label>
                <input
                    class="form-input"
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                    autofocus>
                <small>Mínimo 8 caracteres, una mayúscula, un número y un carácter especial.</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirm">Confirmar contraseña</label>
                <input
                    class="form-input"
                    type="password"
                    id="password_confirm"
                    name="password_confirm"
                    autocomplete="new-password"
                    minlength="8"
                    required>
            </div>

            <?php if (isset($error_message) && $error_message != ''): ?>
                <p class="error-text" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <button class="btn-submit" type="submit" id="restablecer-submit">
                <span class="submit-label">Restablecer contraseña</span>
            </button>
        </form>
    </section>
</main>
</body>
</html>