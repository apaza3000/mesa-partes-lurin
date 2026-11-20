<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>BD - Instalador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-primary text-white"><h4 class="mb-0">Paso 2: Base de datos</h4></div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="post" action="/install/db">
                        <?= \App\Support\Csrf::campo() ?>
                        <div class="mb-3">
                            <label class="form-label">Host</label>
                            <input type="text" name="db_host" class="form-control" value="<?= htmlspecialchars($datos['db_host'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Puerto</label>
                            <input type="number" name="db_port" class="form-control" value="<?= htmlspecialchars($datos['db_port'] ?? '3306') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre de base de datos</label>
                            <input type="text" name="db_name" class="form-control" value="<?= htmlspecialchars($datos['db_name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="db_user" class="form-control" value="<?= htmlspecialchars($datos['db_user'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="db_pass" class="form-control" value="<?= htmlspecialchars($datos['db_pass'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Probar conexión y continuar</button>
                        <a href="/install" class="btn btn-secondary">Atrás</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>