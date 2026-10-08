<?php
$usuario = $usuario ?? [];
$expedientes = $expedientes ?? [];

$e = fn($v) => htmlspecialchars(
    (string) ($v ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$estado = $usuario['estado_usuario'] ?? 'Activo';

$nombreCompleto = trim(
    ($usuario['nombres'] ?? '') . ' ' .
    ($usuario['apellido_paterno'] ?? '') . ' ' .
    ($usuario['apellido_materno'] ?? '')
);

if (($usuario['tipo_persona'] ?? 'Natural') === 'Juridica') {
    $nombreCompleto = $usuario['razon_social'] ?? 'Sin razón social';
}

$roles = !empty($usuario['roles'])
    ? explode(', ', $usuario['roles'])
    : [];

?>

<div class="app-content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="mb-0 fs-3">Detalle del Usuario</h1>
            <p class="text-muted mb-0">
                Información completa de la cuenta
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="/usuarios" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Volver
            </a>

            <a href="/usuarios/<?= $e($usuario['id_usuario'] ?? '') ?>/editar" class="btn btn-warning">
                <i class="bi bi-pencil"></i>
                Editar
            </a>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="row g-4">

            <!-- INFORMACIÓN GENERAL -->
            <div class="col-lg-6">
                <div class="card h-100">

                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-person-vcard me-2"></i>
                            Información general
                        </h3>
                    </div>

                    <div class="card-body">

                        <div class="d-flex align-items-center justify-content-between mb-4">

                            <div>
                                <div class="text-muted small">
                                    Persona
                                </div>

                                <h4 class="mb-0">
                                    <?= $e($nombreCompleto ?: 'Sin nombre') ?>
                                </h4>
                            </div>
                        </div>

                        <dl class="row mb-0">

                            <dt class="col-sm-5">ID Usuario</dt>
                            <dd class="col-sm-7">
                                <?= $e($usuario['id_usuario'] ?? '-') ?>
                            </dd>

                            <dt class="col-sm-5">ID Persona</dt>
                            <dd class="col-sm-7">
                                <?= $e($usuario['id_persona'] ?? '-') ?>
                            </dd>

                            <dt class="col-sm-5">Tipo de persona</dt>
                            <dd class="col-sm-7">
                                <?= $e($usuario['tipo_persona'] ?? '-') ?>
                            </dd>

                            <dt class="col-sm-5">Documento</dt>
                            <dd class="col-sm-7">
                                <?= $e(
                                    ($usuario['tipo_documento'] ?? '') .
                                    ': ' .
                                    ($usuario['numero_documento'] ?? '')
                                ) ?>
                            </dd>

                            <?php if (($usuario['tipo_persona'] ?? 'Natural') === 'Natural'): ?>

                                <dt class="col-sm-5">Nombres</dt>
                                <dd class="col-sm-7">
                                    <?= $e($usuario['nombres'] ?? '-') ?>
                                </dd>

                                <dt class="col-sm-5">Apellido paterno</dt>
                                <dd class="col-sm-7">
                                    <?= $e($usuario['apellido_paterno'] ?? '-') ?>
                                </dd>

                                <dt class="col-sm-5">Apellido materno</dt>
                                <dd class="col-sm-7">
                                    <?= $e($usuario['apellido_materno'] ?? '-') ?>
                                </dd>

                            <?php else: ?>

                                <dt class="col-sm-5">Razón social</dt>
                                <dd class="col-sm-7">
                                    <?= $e($usuario['razon_social'] ?? '-') ?>
                                </dd>

                            <?php endif; ?>

                            <dt class="col-sm-5">Correo</dt>
                            <dd class="col-sm-7">
                                <?= $e($usuario['email'] ?? '-') ?>
                            </dd>

                            <dt class="col-sm-5">Teléfono</dt>
                            <dd class="col-sm-7">
                                <?= $e($usuario['telefono'] ?? '-') ?>
                            </dd>

                            <dt class="col-sm-5">Dirección</dt>
                            <dd class="col-sm-7">
                                <?= $e($usuario['direccion'] ?? '-') ?>
                            </dd>

                        </dl>

                    </div>
                </div>
            </div>
            <!-- INFORMACIÓN DE LA CUENTA -->
            <div class="col-lg-6">

                <div class="card h-100">

                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-person-gear me-2"></i>
                            Información de la cuenta
                        </h3>
                    </div>

                    <div class="card-body">

                        <dl class="row mb-0">


                            <dt class="col-sm-5">Usuario</dt>
                            <dd class="col-sm-7">
                                <code><?= $e($usuario['username'] ?? '-') ?></code>
                            </dd>

                            <dt class="col-sm-5">Estado</dt>
                            <dd class="col-sm-7">
                                <?php
                                $badgeEstado = match ($estado) {
                                    'Activo' => 'success',
                                    'Inactivo' => 'secondary',
                                    'Suspendido' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge text-bg-<?= $badgeEstado ?>">
                                    <?= $e($estado) ?>
                                </span>

                            </dd>

                            <dt class="col-sm-5">Último acceso</dt>
                            <dd class="col-sm-7">
                                <?= $e($usuario['ultimo_acceso'] ?? 'Nunca') ?>
                            </dd>

                            <dt class="col-sm-5">Creado en</dt>
                            <dd class="col-sm-7">
                                <?= $e($usuario['usuario_creado_en'] ?? '-') ?>
                            </dd>

                            <dt class="col-sm-5">Actualizado en</dt>
                            <dd class="col-sm-7">
                                <?= $e($usuario['usuario_actualizado_en'] ?? '-') ?>
                            </dd>

                        </dl>

                    </div>
                </div>

            </div>


            <!-- ÁREA -->
            <div class="col-lg-6">

                <div class="card h-100">

                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-diagram-3 me-2"></i>
                            Área
                        </h3>
                    </div>

                    <div class="card-body">

                        <?php if (!empty($usuario['id_area'])): ?>

                            <dl class="row mb-0">

                                <dt class="col-sm-5">ID Área</dt>
                                <dd class="col-sm-7">
                                    <?= $e($usuario['id_area']) ?>
                                </dd>

                                <dt class="col-sm-5">Nombre</dt>
                                <dd class="col-sm-7">
                                    <?= $e($usuario['area_nombre'] ?? '-') ?>
                                </dd>

                                <dt class="col-sm-5">Siglas</dt>
                                <dd class="col-sm-7">
                                    <span class="badge text-bg-info">
                                        <?= $e($usuario['area_siglas'] ?? '-') ?>
                                    </span>
                                </dd>

                            </dl>
                        <?php else: ?>

                            <div class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                El usuario no tiene un área asignada.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- ROLES -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-shield-lock me-2"></i>
                            Roles y permisos
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($usuario['id_rol'])): ?>

                            <dl class="row mb-0">

                                <dt class="col-sm-5">ID Rol</dt>
                                <dd class="col-sm-7">
                                    <?= $e($usuario['id_rol']) ?>
                                </dd>

                                <dt class="col-sm-5">Nombre</dt>
                                <dd class="col-sm-7">
                                    <?= $e($usuario['rol_nombre'] ?? '-') ?>
                                </dd>

                                <dt class="col-sm-5">Descripcion</dt>
                                <dd class="col-sm-7">
                                    <?= $e($usuario['rol_descripcion'] ?? '-') ?>
                                </dd>
                            </dl>
                        <?php else: ?>

                            <div class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                El usuario no tiene un área asignada.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>

        </div>

    </div>
</div>