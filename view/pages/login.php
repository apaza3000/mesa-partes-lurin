<?php
$error_message = $error_message ?? "";
$data = $datos_viejos ?? [];
$errors = $errors ?? [];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Iniciar sesión | Mesa de Partes Lurín</title>
    <link rel="stylesheet" href="/view/assets/css/login.css">
    <script src="/view/assets/js/auth/login.js" defer></script>
</head>

<body>
    <main class="login-card">
        <div class="top-bar"></div>
        <section class="login-body" aria-labelledby="login-title">
            <header class="brand-header">
                <h1 class="brand-title" id="login-title">Mesa de Partes</h1>
                <p class="brand-subtitle">IESTP Lurín</p>
                <span class="badge">Sistema de gestión documentaria</span>
            </header>

            <form action="/login" method="POST" id="login-form">
                <div class="form-group">
                    <label class="form-label" for="identificador">Usuario o correo electrónico</label>
                    <input
                        class="form-input<?= isset($errors['username']) ? ' input-error' : '' ?>"
                        type="text"
                        id="identificador"
                        name="username"
                        value="<?= $data['username'] ?? "" ?>"
                        autocomplete="username"
                        maxlength="254"
                        required
                        autofocus
                        aria-describedby="<?= isset($errors['username']) ? 'identificador-error' : '' ?>">
                    <?php if (isset($errors['username'])): ?>
                        <p class="error-text" id="identificador-error" role="alert">
                            <?= implode("<br>", $errors["username"]) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <div class="password-field">
                        <input
                            class="form-input<?= isset($errors['password']) ? ' input-error' : '' ?>"
                            type="password"
                            id="password"
                            name="password"
                            value="<?= $data['password'] ?? "" ?>"
                            autocomplete="current-password"
                            required
                            aria-describedby="<?= isset($errors['password']) ? 'password-error' : '' ?>">
                        <button
                            class="password-toggle"
                            type="button"
                            id="toggle-password"
                            aria-label="Mostrar contraseña"
                            aria-pressed="false">
                            <span aria-hidden="true">Mostrar</span>
                        </button>
                    </div>
                    <?php if (isset($error_message) && $error_message != ''): ?>
                        <p class="error-text" id="password-error" role="alert">
                            <?= htmlspecialchars($error_message ?? "", ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>
                </div>

                <button class="btn-submit" type="submit" id="login-submit">
                    <span class="submit-label">Iniciar sesión</span>
                    <span class="spinner hidden" aria-hidden="true"></span>
                </button>
            </form>
        </section>
    </main>
</body>

</html>