<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Expediente Registrado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .exito-box {
            max-width: 600px;
            width: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .3);
            padding: 40px 30px;
            text-align: center;
        }

        .exito-box .icon-ok {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 15px;
        }

        .exito-box h2 {
            color: #1e3c72;
            font-weight: 700;
        }

        .codigo {
            background: #f1f5fb;
            border: 2px dashed #1e3c72;
            border-radius: 8px;
            padding: 15px;
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e3c72;
            letter-spacing: 2px;
            margin: 20px 0;
        }

        .info {
            color: #555;
            font-size: .95rem;
            text-align: left;
            margin: 20px 0;
        }

        .info i {
            color: #1e3c72;
            width: 20px;
        }
    </style>
</head>

<body>
    <div class="exito-box">
        <i class="fas fa-check-circle icon-ok"></i>
        <h2>¡Expediente registrado!</h2>
        <p class="text-muted">Guarda este código para consultar el estado de tu trámite.</p>

        <div class="codigo"><?= htmlspecialchars($expediente['codigo']) ?></div>

        <div class="info">
            <p class="mb-1"><i class="fas fa-file-alt"></i> <strong>Asunto:</strong> <?= htmlspecialchars($expediente['asunto']) ?></p>
            <p class="mb-1"><i class="fas fa-envelope"></i> <strong>Correo:</strong> <?= htmlspecialchars($expediente['email']) ?></p>
            <p class="mb-0"><i class="fas fa-clock"></i> <strong>Estado:</strong> Registrado</p>
        </div>

        <a href="/consulta" class="btn btn-primary btn-lg mt-3">
            <i class="fas fa-search"></i> Consultar mi expediente
        </a>
        <a href="/" class="btn btn-secondary btn-lg mt-3 ml-2">
            <i class="fas fa-home"></i> Volver al inicio
        </a>
    </div>
</body>

</html>