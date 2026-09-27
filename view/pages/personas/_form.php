<?php
$persona = $persona ?? [];
$errors = $errors ?? [];
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$tipoPersona = $persona['tipo_persona'] ?? 'Natural';
$tipoDocumento = $persona['tipo_documento'] ?? 'DNI';
?>
<div class="row g-3">
    <div class="col-md-4">
        <label for="tipo_persona" class="form-label">Tipo de persona <span class="text-danger">*</span></label>
        <select class="form-select <?= isset($errors['tipo_persona']) ? 'is-invalid' : '' ?>" id="tipo_persona"
            name="tipo_persona" required>
            <option value="Natural" <?= $tipoPersona === 'Natural' ? 'selected' : '' ?>>Natural</option>
            <option value="Juridica" <?= $tipoPersona === 'Juridica' ? 'selected' : '' ?>>Jurídica</option>
        </select>
        <?php if (isset($errors['tipo_persona'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['tipo_persona']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-4">
        <label for="tipo_documento" class="form-label">Tipo de documento <span class="text-danger">*</span></label>
        <select class="form-select <?= isset($errors['tipo_documento']) ? 'is-invalid' : '' ?>" id="tipo_documento"
            name="tipo_documento" required>
            <?php foreach (['DNI', 'CE', 'RUC', 'Pasaporte', 'Otro'] as $doc): ?>
            <option value="<?= $doc ?>" <?= $tipoDocumento === $doc ? 'selected' : '' ?>><?= $doc ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['tipo_documento'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['tipo_documento']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-4">
        <label for="numero_documento" class="form-label">Número de documento <span class="text-danger">*</span></label>
        <input type="text" class="form-control <?= isset($errors['numero_documento']) ? 'is-invalid' : '' ?>"
            id="numero_documento" name="numero_documento" value="<?= $e($persona['numero_documento'] ?? '') ?>"
            maxlength="20" required>
        <?php if (isset($errors['numero_documento'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['numero_documento']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-6 natural-field" <?= $tipoPersona === 'Juridica' ? 'style="display:none;"' : '' ?>>
        <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
        <input type="text" class="form-control <?= isset($errors['nombres']) ? 'is-invalid' : '' ?>" id="nombres"
            name="nombres" value="<?= $e($persona['nombres'] ?? '') ?>" required>
        <?php if (isset($errors['nombres'])): ?><div class="invalid-feedback d-block"><?= $e($errors['nombres']) ?>
        </div><?php endif; ?>
    </div>

    <div class="col-md-3 natural-field" <?= $tipoPersona === 'Juridica' ? 'style="display:none;"' : '' ?>>
        <label for="apellido_paterno" class="form-label">Apellido paterno <span class="text-danger">*</span></label>
        <input type="text" class="form-control <?= isset($errors['apellido_paterno']) ? 'is-invalid' : '' ?>"
            id="apellido_paterno" name="apellido_paterno" value="<?= $e($persona['apellido_paterno'] ?? '') ?>"
            required>
        <?php if (isset($errors['apellido_paterno'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['apellido_paterno']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-3 natural-field" <?= $tipoPersona === 'Juridica' ? 'style="display:none;"' : '' ?>>
        <label for="apellido_materno" class="form-label">Apellido materno <span class="text-danger">*</span></label>
        <input type="text" class="form-control <?= isset($errors['apellido_materno']) ? 'is-invalid' : '' ?>"
            id="apellido_materno" name="apellido_materno" value="<?= $e($persona['apellido_materno'] ?? '') ?>"
            required>
        <?php if (isset($errors['apellido_materno'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['apellido_materno']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-12 juridica-field" <?= $tipoPersona === 'Natural' ? 'style="display:none;"' : '' ?>>
        <label for="razon_social" class="form-label">Razón social <span class="text-danger">*</span></label>
        <input type="text" class="form-control <?= isset($errors['razon_social']) ? 'is-invalid' : '' ?>"
            id="razon_social" name="razon_social" value="<?= $e($persona['razon_social'] ?? '') ?>" required>
        <?php if (isset($errors['razon_social'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['razon_social']) ?></div><?php endif; ?>
    </div>

    <div class="col-md-4">
        <label for="email" class="form-label">Correo electrónico
            <span class="text-danger">*</span>
        </label>
        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email"
            name="email" value="<?= $e($persona['email'] ?? '') ?>" maxlength="150" required>
        <?php if (isset($errors['email'])): ?><div class="invalid-feedback d-block"><?= $e($errors['email']) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <label for="telefono" class="form-label">Teléfono
            <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control <?= isset($errors['telefono']) ? 'is-invalid' : '' ?>" id="telefono"
            name="telefono" value="<?= $e($persona['telefono'] ?? '') ?>" maxlength="30" required>
        <?php if (isset($errors['telefono'])): ?><div class="invalid-feedback d-block">

            <?= $e($errors['telefono']) ?></div>
        <?php endif; ?>
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
        <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
        <textarea class="form-control <?= isset($errors['direccion']) ? 'is-invalid' : '' ?>" id="direccion"
            name="direccion" rows="3" maxlength="255" required><?= $e($persona['direccion'] ?? '') ?></textarea>
        <?php if (isset($errors['direccion'])): ?><div class="invalid-feedback d-block">
            <?= $e($errors['direccion']) ?></div><?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoPersona = document.getElementById('tipo_persona');
    const naturalFields = document.querySelectorAll('.natural-field');
    const juridicaFields = document.querySelectorAll('.juridica-field');

    const toggleTipoPersona = () => {
        const isNatural = tipoPersona.value === 'Natural';
        naturalFields.forEach(el => {
            el.style.display = isNatural ? '' : 'none';
            const input = el.querySelector('input');
            if (input) {
                input.required = isNatural;
            }
        });
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