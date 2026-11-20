<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Entorno - Instalador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white"><h4 class="mb-0">Paso 3: Configuración del entorno</h4></div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="post" action="/install/env">
                        <?= \App\Support\Csrf::campo() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Entorno (APP_ENV)</label>
                                <select name="app_env" class="form-select">
                                    <option value="local" <?= ($datos['app_env'] ?? '') === 'local' ? 'selected' : '' ?>>local</option>
                                    <option value="produccion" <?= ($datos['app_env'] ?? '') === 'produccion' ? 'selected' : '' ?>>produccion</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Zona horaria</label>
                                <input type="text" name="app_timezone" class="form-control" value="<?= htmlspecialchars($datos['app_timezone'] ?? 'America/Lima') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL de la aplicación (APP_URL)</label>
                            <input type="text" name="app_url" class="form-control" value="<?= htmlspecialchars($datos['app_url'] ?? '') ?>">
                        </div>
                        <hr>
                        <h6>Correo (opcional)</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="mail_habilitado" value="1" id="mh" <?= ($datos['mail_habilitado'] ?? 'false') === 'true' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="mh">Habilitar envío de correo</label>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">MAIL_HOST</label>
                                <input type="text" name="mail_host" class="form-control" value="<?= htmlspecialchars($datos['mail_host'] ?? '') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">MAIL_PORT</label>
                                <input type="number" name="mail_port" class="form-control" value="<?= htmlspecialchars($datos['mail_port'] ?? '587') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Cifrado</label>
                                <select name="mail_encryption" class="form-select">
                                    <option value="tls" <?= ($datos['mail_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>tls</option>
                                    <option value="ssl" <?= ($datos['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>ssl</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">MAIL_USERNAME</label>
                                <input type="text" name="mail_username" class="form-control" value="<?= htmlspecialchars($datos['mail_username'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">MAIL_PASSWORD</label>
                                <input type="password" name="mail_password" class="form-control" value="<?= htmlspecialchars($datos['mail_password'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">MAIL_FROM_ADDRESS</label>
                            <input type="email" name="mail_from_address" class="form-control" value="<?= htmlspecialchars($datos['mail_from_address'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">MAIL_FROM_NAME</label>
                            <input type="text" name="mail_from_name" class="form-control" value="<?= htmlspecialchars($datos['mail_from_name'] ?? 'Mesa de Partes Lurín') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">MAIL_MESA_PARTES (correo destino)</label>
                            <input type="email" name="mail_mesa_partes" class="form-control" value="<?= htmlspecialchars($datos['mail_mesa_partes'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Token API Decolecta (opcional)</label>
                            <input type="text" name="api_decolecta_token" class="form-control" value="<?= htmlspecialchars($datos['api_decolecta_token'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar y continuar</button>
                        <a href="/install/db" class="btn btn-secondary">Atrás</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>