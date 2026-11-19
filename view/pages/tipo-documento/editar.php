<?php
/**
 * TAREA E07-1: edición de un tipo de documento.
 *
 * @var array $tipo
 * @var array $errors
 * @var array $datos_viejos
 * @var string $csrf_token
 */
$tipo = $tipo ?? [];
$errors = $errors ?? [];
$datos_viejos = $datos_viejos ?? $tipo;
$csrf_token = $csrf_token ?? \App\Support\Csrf::token();

$valor = static fn(string $campo, string $defecto = ''): string =>
    htmlspecialchars((string) ($datos_viejos[$campo] ?? $tipo[$campo] ?? $defecto), ENT_QUOTES, 'UTF-8');
?>
<div class="app-content-header">
    <div class="container-fluid">
        <h1 class="mb-0 fs-3">Editar tipo de documento</h1>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-7">
                <div class="card card-warning card-outline mb-4">
                    <?php if ($tipo === []): ?>
                        <div class="card-body">
                            <div class="alert alert-danger mb-0">El tipo de documento solicitado no existe.</div>
                        </div>
                        <div class="card-footer">
                            <a href="/tipos-documento" class="btn btn-secondary">Volver</a>
                        </div>
                    <?php else: ?>
                        <form action="/tipos-documento/<?= (int) $tipo['id_tipo_doc'] ?>" method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="card-body">
                                <?php if (!empty($errors['db'])): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $errors['db']), ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" id="nombre" name="nombre" maxlength="100" required
                                           class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>"
                                           value="<?= $valor('nombre') ?>">
                                    <?php if (isset($errors['nombre'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars(implode(' ', $errors['nombre']), ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea id="descripcion" name="descripcion" rows="3" maxlength="255"
                                              class="form-control <?= isset($errors['descripcion']) ? 'is-invalid' : '' ?>"><?= $valor('descripcion') ?></textarea>
                                    <?php if (isset($errors['descripcion'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars(implode(' ', $errors['descripcion']), ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select id="estado" name="estado" class="form-select">
                                        <?php foreach (\App\Support\Catalogos::EstadoSimple as $estado): ?>
                                            <option value="<?= $estado ?>" <?= ($datos_viejos['estado'] ?? $tipo['estado'] ?? 'Activo') === $estado ? 'selected' : '' ?>>
                                                <?= $estado ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning text-white">Actualizar cambios</button>
                                <a href="/tipos-documento" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
