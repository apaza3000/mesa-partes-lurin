<?php
$persona = $persona ?? [];
$errors = $errors ?? [];
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
?>
<div class="app-content-header"><div class="container-fluid"><h1 class="mb-0 fs-3">Registrar persona remitente</h1></div></div>
<div class="app-content"><div class="container-fluid"><div class="card card-primary card-outline"><form action="/personas" method="POST"><div class="card-body">
<?php if (isset($errors['db'])): ?><div class="alert alert-danger"><?= $e($errors['db']) ?></div><?php endif; ?>
<?php include __DIR__ . '/_form.php'; ?>
</div><div class="card-footer"><button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Guardar</button> <a href="/personas" class="btn btn-secondary">Cancelar</a></div></form></div></div></div>
