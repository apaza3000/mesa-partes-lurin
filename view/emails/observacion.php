<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente observado</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #222; font-size: 14px;">
    <h2 style="color: #b02a37;">Expediente observado</h2>
    <p>Su expediente <strong><?= htmlspecialchars($codigo ?? '', ENT_QUOTES, 'UTF-8') ?></strong> ha sido observado y requiere subsanación.</p>
    <p><strong>Motivo:</strong> <?= nl2br(htmlspecialchars($motivo ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
    <p>Puede subsanar desde: <a href="<?= htmlspecialchars($url_subsanar ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($url_subsanar ?? '', ENT_QUOTES, 'UTF-8') ?></a></p>
    <hr>
    <p style="font-size: 12px; color: #666;">Mesa de Partes — Municipalidad de Lurín</p>
</body>
</html>