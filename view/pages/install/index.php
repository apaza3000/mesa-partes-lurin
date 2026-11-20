<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Instalación - Mesa de Partes Lurín</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Instalador de Mesa de Partes Lurín</h4>
                </div>
                <div class="card-body">
                    <h5>Paso 1: Comprobación de requisitos</h5>
                    <table class="table">
                        <tbody>
                        <?php foreach ($reqs as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r[0]) ?></td>
                                <td class="text-end">
                                    <?php if ($r[1]): ?>
                                        <span class="badge bg-success">OK</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">FALTA</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if ($todoOk): ?>
                        <a href="/install/db" class="btn btn-primary">Continuar</a>
                    <?php else: ?>
                        <div class="alert alert-danger">Corrija los requisitos faltantes antes de continuar.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>