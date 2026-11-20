<?php
// Plantilla de correo: registro de expediente.
// Variables esperadas (extraídas por el controlador): $codigo, $asunto, $area,
// $area_destino, $fecha, $url_consulta, $url, $qr_base64, $remitente.
$codigoHtml = htmlspecialchars((string) ($codigo ?? ''), ENT_QUOTES, 'UTF-8');
$asuntoHtml = htmlspecialchars((string) ($asunto ?? ''), ENT_QUOTES, 'UTF-8');
$areaHtml = htmlspecialchars((string) (($area_destino ?? $area) ?? 'Mesa de Partes'), ENT_QUOTES, 'UTF-8');
$fechaHtml = htmlspecialchars((string) ($fecha ?? date('d/m/Y H:i')), ENT_QUOTES, 'UTF-8');
$url = htmlspecialchars((string) (($url_consulta ?? $url) ?? ''), ENT_QUOTES, 'UTF-8');
$remitenteHtml = htmlspecialchars((string) ($remitente ?? ''), ENT_QUOTES, 'UTF-8');
$qrBase64 = (string) ($qr_base64 ?? '');
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Registro de expediente</title>
</head>

<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">
  <div style="max-width:600px;margin:auto;background:#fff;padding:24px;border-radius:8px;">
    <h2 style="color:#0d6efd;">Constancia de registro de expediente</h2>
    <p>Su documento ha sido registrado correctamente en Mesa de Partes.</p>
    <table style="width:100%;border-collapse:collapse;">
      <tr>
        <td style="padding:6px;font-weight:bold;">Código</td>
        <td style="padding:6px;"><?= $codigoHtml ?></td>
      </tr>
      <tr>
        <td style="padding:6px;font-weight:bold;">Remitente</td>
        <td style="padding:6px;"><?= $remitenteHtml ?></td>
      </tr>
      <tr>
        <td style="padding:6px;font-weight:bold;">Asunto</td>
        <td style="padding:6px;"><?= $asuntoHtml ?></td>
      </tr>
      <tr>
        <td style="padding:6px;font-weight:bold;">Área destino</td>
        <td style="padding:6px;"><?= $areaHtml ?></td>
      </tr>
      <tr>
        <td style="padding:6px;font-weight:bold;">Fecha</td>
        <td style="padding:6px;"><?= $fechaHtml ?></td>
      </tr>
    </table>
    <?php if ($url !== ''): ?>
      <p style="margin-top:16px;">Consulte el estado de su expediente: <a href="<?= $url ?>"><?= $url ?></a></p>
    <?php endif; ?>
    <?php if ($qrBase64 !== ''): ?>
      <div style="margin-top:16px;text-align:center;">
        <p style="font-size:12px;color:#6c757d;">Escanee el código QR para consultar su trámite:</p>
        <img
          src="cid:qr_expediente"
          alt="Código QR"
          width="140"
          height="140"
          style="border:1px solid #dee2e6;padding:4px;">
      </div>
    <?php endif; ?>
    <p style="color:#6c757d;font-size:12px;margin-top:24px;">Municipalidad de Lurín — Mesa de Partes</p>
  </div>
</body>

</html>