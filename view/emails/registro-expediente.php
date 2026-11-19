<?php
// Plantilla de correo: registro de expediente.
$codigo = htmlspecialchars((string) ($datos['codigo'] ?? ''), ENT_QUOTES, 'UTF-8');
$asunto = htmlspecialchars((string) ($datos['asunto'] ?? ''), ENT_QUOTES, 'UTF-8');
$area = htmlspecialchars((string) ($datos['area_destino'] ?? 'Mesa de Partes'), ENT_QUOTES, 'UTF-8');
$fecha = htmlspecialchars((string) ($datos['fecha'] ?? date('d/m/Y H:i')), ENT_QUOTES, 'UTF-8');
$url = htmlspecialchars((string) ($datos['url_consulta'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Registro de expediente</title></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">
  <div style="max-width:600px;margin:auto;background:#fff;padding:24px;border-radius:8px;">
    <h2 style="color:#0d6efd;">Constancia de registro de expediente</h2>
    <p>Su documento ha sido registrado correctamente en Mesa de Partes.</p>
    <table style="width:100%;border-collapse:collapse;">
      <tr><td style="padding:6px;font-weight:bold;">Código</td><td style="padding:6px;"><?= $codigo ?></td></tr>
      <tr><td style="padding:6px;font-weight:bold;">Asunto</td><td style="padding:6px;"><?= $asunto ?></td></tr>
      <tr><td style="padding:6px;font-weight:bold;">Área destino</td><td style="padding:6px;"><?= $area ?></td></tr>
      <tr><td style="padding:6px;font-weight:bold;">Fecha</td><td style="padding:6px;"><?= $fecha ?></td></tr>
    </table>
    <?php if ($url !== ''): ?>
      <p style="margin-top:16px;">Consulte el estado de su expediente: <a href="<?= $url ?>"><?= $url ?></a></p>
    <?php endif; ?>
    <p style="color:#6c757d;font-size:12px;margin-top:24px;">Municipalidad de Lurín — Mesa de Partes</p>
  </div>
</body>
</html>