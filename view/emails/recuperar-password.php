<?php
$nombre = htmlspecialchars($nombre ?? 'usuario', ENT_QUOTES, 'UTF-8');
$url = htmlspecialchars($url ?? '', ENT_QUOTES, 'UTF-8');
$expira = htmlspecialchars($expira ?? '1 hora', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Recuperación de contraseña</title></head>
<body style="font-family:Arial,sans-serif;color:#222;">
  <h2>Hola <?= $nombre ?>,</h2>
  <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en Mesa de Partes Lurín.</p>
  <p>Haz clic en el siguiente enlace para crear una nueva contraseña. El enlace expira en <?= $expira ?>.</p>
  <p><a href="<?= $url ?>" style="display:inline-block;padding:10px 16px;background:#1a5fb4;color:#fff;text-decoration:none;border-radius:6px;">Restablecer contraseña</a></p>
  <p>Si no solicitaste este cambio, ignora este correo. Tu contraseña actual seguirá vigente.</p>
  <hr>
  <small>Mesa de Partes Lurín - IESTP Lurín</small>
</body>
</html>