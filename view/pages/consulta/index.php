<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Consulta de Expediente - Mesa de Partes Virtual</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body.consulta-page {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }

        .consulta-box {
            max-width: 620px;
            width: 100%;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .3);
            border: none;
            overflow: hidden;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            text-align: center;
            padding: 30px;
        }

        .card-header i.fa-search {
            font-size: 2.6rem;
            color: #1e3c72;
            margin-bottom: 10px;
        }

        .card-header .logo-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 5px;
        }

        .card-header .logo-subtitle {
            color: #777;
            font-size: .95rem;
        }

        .btn-primary {
            background: #1e3c72;
            border-color: #1e3c72;
        }

        .btn-primary:hover {
            background: #2a5298;
            border-color: #2a5298;
        }

        .invalid-feedback {
            display: block;
        }

        .back-home {
            text-align: center;
            margin-top: 15px;
        }

        .back-home a {
            color: #fff;
            font-weight: 600;
        }
    </style>
</head>

<body class="consulta-page">

    <div class="consulta-box">

        <div class="card">

            <div class="card-header">
                <i class="fas fa-search"></i>
                <div class="logo-title">Consulta de Expediente</div>
                <div class="logo-subtitle">Ingresa tu código de expediente para ver el estado de tu trámite</div>
            </div>

            <form method="POST" action="/consulta/buscar" novalidate>
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

                    <div class="form-group">
                        <label>Código de Expediente *</label>
                        <input type="text" name="codigo"
                            class="form-control form-control-lg <?= isset($errors['codigo']) ? 'is-invalid' : '' ?>"
                            placeholder="Ej: EXP-2026-00001"
                            value="<?= htmlspecialchars($datos_viejos['codigo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required>
                        <?php if (isset($errors['codigo'])): ?>
                            <div class="invalid-feedback">*<?= implode('<br>', (array)$errors['codigo']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Número de Documento (opcional)</label>
                        <input type="text" name="numero_documento"
                            class="form-control form-control-lg"
                            placeholder="Ej: 12345678"
                            value="<?= htmlspecialchars($datos_viejos['numero_documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <small class="form-text text-muted">
                            Si ingresas tu número de documento, validamos que seas el remitente.
                        </small>
                    </div>

                </div>

                <div class="card-footer bg-white border-top">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                </div>
            </form>
        </div>

        <div class="back-home">
            <a href="/"><i class="fas fa-home"></i> Volver al inicio</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>