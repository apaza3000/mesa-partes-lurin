<?php
$error_message = $error_message ?? "";
$data = $datos_viejos ?? [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Recuperar contraseña | Mesa de Partes Lurín</title>
    <link rel="stylesheet" href="/view/assets/css/login.css">
</head>

<body>
    <main class="login-card">
        <div class="top-bar"></div>
        <section class="login-body" aria-labelledby="recuperar-title">
            <a class="back-home" href="/login" aria-label="Volver al login">
                <span class="back-arrow" aria-hidden="true">&larr;</span>
                Volver al inicio de sesión
            </a>
            <header class="brand-header">
                <h1 class="brand-title auth-title" id="recuperar-title">Recuperar contraseña</h1>
                <p class="brand-subtitle">IESTP Lurín</p>
                <span class="badge">Sistema de gestión documentaria</span>
            </header>

            <?php foreach (($flash ?? []) as $flashItem): ?>
                <p class="error-text" role="alert"><?= htmlspecialchars($flashItem['mensaje'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endforeach; ?>

            <form action="/recuperar" method="POST" id="recuperar-form" data-title="Recuperar Contraseña">
                <?= \App\Support\Csrf::campo() ?>
                <div class="form-group">
                    <label class="form-label" for="email">Correo electrónico</label>
                    <input class="form-input" type="email" id="email" name="email"
                        value="<?= htmlspecialchars($data['email'] ?? "", ENT_QUOTES, 'UTF-8') ?>" autocomplete="email"
                        maxlength="150" required autofocus>
                </div>

                <?php if (isset($error_message) && $error_message != ''): ?>
                    <p class="error-text" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <button class="btn-submit" type="submit" id="recuperar-submit">
                    <span class="submit-label">Enviar enlace</span>
                </button>
            </form>
        </section>
    </main>
</body>

</html>