<?php
$persona = $persona ?? [];
$errors = $errors ?? [];
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$tipoPersona = $persona['tipo_persona'] ?? 'Natural';
$tipoDocumento = $persona['tipo_documento'] ?? 'DNI';
?>
<div class="row g-3">
    <div class="col-md-4">
        <label for="tipo_persona" class="form-label">Tipo de persona</label>
        <select class="form-select <?= isset($errors['tipo_persona']) ? 'is-invalid' : '' ?>" id="tipo_persona"
            name="tipo_persona">
            <option value="Natural" <?= $tipoPersona === 'Natural' ? 'selected' : '' ?>>Natural</option>
            <option value="Juridica" <?= $tipoPersona === 'Juridica' ? 'selected' : '' ?>>Jurídica</option>
        </select>
        <?php if (isset($errors['tipo_persona'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['tipo_persona']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-4">
        <label for="tipo_documento" class="form-label">Tipo de documento</label>
        <select class="form-select <?= isset($errors['tipo_documento']) ? 'is-invalid' : '' ?>" id="tipo_documento"
            name="tipo_documento">
            <?php foreach (['DNI', 'CE', 'RUC', 'Pasaporte', 'Otro'] as $doc): ?>
            <option value="<?= $doc ?>" <?= $tipoDocumento === $doc ? 'selected' : '' ?>><?= $doc ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['tipo_documento'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['tipo_documento']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-4">
        <label for="numero_documento" class="form-label">Número de documento</label>
        <input type="text" class="form-control <?= isset($errors['numero_documento']) ? 'is-invalid' : '' ?>"
            id="numero_documento" name="numero_documento" value="<?= $e($persona['numero_documento'] ?? '') ?>"
            maxlength="20" required>
        <?php if (isset($errors['numero_documento'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['numero_documento']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-6 natural-field" <?= $tipoPersona === 'Juridica' ? 'style="display:none;"' : '' ?>>
        <label for="nombres" class="form-label">Nombres</label>
        <input type="text" class="form-control <?= isset($errors['nombres']) ? 'is-invalid' : '' ?>" id="nombres"
            name="nombres" value="<?= $e($persona['nombres'] ?? '') ?>">
        <?php if (isset($errors['nombres'])): ?><div class="invalid-feedback d-block"><?= $e($errors['nombres']) ?>
        </div><?php endif; ?>
    </div>

    <div class="col-md-3 natural-field" <?= $tipoPersona === 'Juridica' ? 'style="display:none;"' : '' ?>>
        <label for="apellido_paterno" class="form-label">Apellido paterno</label>
        <input type="text" class="form-control <?= isset($errors['apellido_paterno']) ? 'is-invalid' : '' ?>"
            id="apellido_paterno" name="apellido_paterno" value="<?= $e($persona['apellido_paterno'] ?? '') ?>">
        <?php if (isset($errors['apellido_paterno'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['apellido_paterno']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-3 natural-field" <?= $tipoPersona === 'Juridica' ? 'style="display:none;"' : '' ?>>
        <label for="apellido_materno" class="form-label">Apellido materno</label>
        <input type="text" class="form-control <?= isset($errors['apellido_materno']) ? 'is-invalid' : '' ?>"
            id="apellido_materno" name="apellido_materno" value="<?= $e($persona['apellido_materno'] ?? '') ?>">
        <?php if (isset($errors['apellido_materno'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['apellido_materno']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-12 juridica-field" <?= $tipoPersona === 'Natural' ? 'style="display:none;"' : '' ?>>
        <label for="razon_social" class="form-label">Razón social</label>
        <input type="text" class="form-control <?= isset($errors['razon_social']) ? 'is-invalid' : '' ?>"
            id="razon_social" name="razon_social" value="<?= $e($persona['razon_social'] ?? '') ?>">
        <?php if (isset($errors['razon_social'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['razon_social']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-4">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email"
            name="email" value="<?= $e($persona['email'] ?? '') ?>">
        <?php if (isset($errors['email'])): ?><div class="invalid-feedback d-block"><?= $e($errors['email']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" class="form-control" id="telefono" name="telefono"
            value="<?= $e($persona['telefono'] ?? '') ?>">
    </div>

    <div class="col-md-4">
        <label for="estado" class="form-label">Estado</label>
        <select class="form-select" id="estado" name="estado">
            <option value="Activo" <?= (($persona['estado'] ?? 'Activo') === 'Activo') ? 'selected' : '' ?>>Activo
            </option>
            <option value="Inactivo" <?= (($persona['estado'] ?? 'Activo') === 'Inactivo') ? 'selected' : '' ?>>Inactivo
            </option>
        </select>
    </div>

    <div class="col-md-12">
        <label for="direccion" class="form-label">Dirección</label>
        <textarea class="form-control" id="direccion" name="direccion"
            rows="3"><?= $e($persona['direccion'] ?? '') ?></textarea>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoPersona = document.getElementById('tipo_persona');
    const naturalFields = document.querySelectorAll('.natural-field');
    const juridicaFields = document.querySelectorAll('.juridica-field');

    const toggleTipoPersona = () => {
        const isNatural = tipoPersona.value === 'Natural';
        naturalFields.forEach(el => el.style.display = isNatural ? '' : 'none');
        juridicaFields.forEach(el => el.style.display = isNatural ? 'none' : '');
        const razonSocial = document.getElementById('razon_social');
        if (razonSocial) {
            razonSocial.required = !isNatural;
        }
    };

    if (tipoPersona) {
        tipoPersona.addEventListener('change', toggleTipoPersona);
        toggleTipoPersona();
    }
});
</script>