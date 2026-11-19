<?php
/**
 * TAREA E10-1: visor de auditoría del sistema (solo ADMINISTRADOR).
 *
 * @var array $filtros
 * @var array $registros
 * @var int $total
 * @var int $pagina
 * @var int $paginas
 * @var array $catalogos
 * @var array $usuarios
 */
$filtros = $filtros ?? [];
$registros = $registros ?? [];
$total = (int) ($total ?? 0);
$pagina = (int) ($pagina ?? 1);
$paginas = (int) ($paginas ?? 1);
$catalogos = $catalogos ?? ['modulos' => [], 'acciones' => []];
$usuarios = $usuarios ?? [];

$e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$modulos = is_array($catalogos['modulos'] ?? null) ? $catalogos['modulos'] : [];
$acciones = is_array($catalogos['acciones'] ?? null) ? $catalogos['acciones'] : [];

$filtrosLimpios = array_filter([
    'fecha_desde' => $filtros['fecha_desde'] ?? '',
    'fecha_hasta' => $filtros['fecha_hasta'] ?? '',
    'id_usuario'  => $filtros['id_usuario'] ?? 0,
    'modulo'      => $filtros['modulo'] ?? '',
    'accion'      => $filtros['accion'] ?? '',
    'texto'       => $filtros['texto'] ?? '',
], static fn($v): bool => $v !== '' && $v !== 0 && $v !== '0');

$urlPagina = static function (int $n) use ($filtrosLimpios): string {
    $params = $filtrosLimpios;
    $params['pagina'] = $n;

    return '/auditoria?' . http_build_query($params);
};

$clasesAccion = [
    'CREAR'     => 'success',
    'REGISTRAR' => 'success',
    'ACTUALIZAR'=> 'primary',
    'EDITAR'    => 'primary',
    'ELIMINAR'  => 'danger',
    'INACTIVAR' => 'warning',
    'LOGIN'     => 'info',
    'LOGOUT'    => 'secondary',
];
?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h1 class="mb-0 fs-3"><i class="bi bi-shield-check text-primary me-2"></i>Auditoría del sistema</h1>
                <p class="text-muted mb-0">
                    Trazabilidad de las acciones registradas. Mostrando <?= count($registros) ?> de <?= $total ?> registro(s).
                </p>
            </div>
            <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                <a href="/auditoria" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Últimos 30 días
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="/auditoria" class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="fecha_desde">Desde</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde"
                               value="<?= $e($filtros['fecha_desde'] ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="fecha_hasta">Hasta</label>
                        <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta"
                               value="<?= $e($filtros['fecha_hasta'] ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="id_usuario">Usuario</label>
                        <select class="form-select form-select-sm" id="id_usuario" name="id_usuario">
                            <option value="0">Todos</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= (int) $usuario['id_usuario'] ?>"
                                    <?= (int) ($filtros['id_usuario'] ?? 0) === (int) $usuario['id_usuario'] ? 'selected' : '' ?>>
                                    <?= $e($usuario['username'] . ($usuario['nombre'] !== '' ? ' — ' . $usuario['nombre'] : '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="modulo">Módulo</label>
                        <select class="form-select form-select-sm" id="modulo" name="modulo">
                            <option value="">Todos</option>
                            <?php foreach ($modulos as $modulo): ?>
                                <option value="<?= $e($modulo) ?>" <?= ($filtros['modulo'] ?? '') === $modulo ? 'selected' : '' ?>>
                                    <?= $e($modulo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1" for="accion">Acción</label>
                        <select class="form-select form-select-sm" id="accion" name="accion">
                            <option value="">Todas</option>
                            <?php foreach ($acciones as $accion): ?>
                                <option value="<?= $e($accion) ?>" <?= ($filtros['accion'] ?? '') === $accion ? 'selected' : '' ?>>
                                    <?= $e($accion) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label small mb-1" for="texto">Texto libre</label>
                        <input type="text" class="form-control form-control-sm" id="texto" name="texto" maxlength="100"
                               value="<?= $e($filtros['texto'] ?? '') ?>" placeholder="Descripción, usuario o IP">
                    </div>
                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                        <a href="/auditoria" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:150px">Fecha</th>
                                <th>Usuario</th>
                                <th>Módulo</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                                <th>Tabla / Registro</th>
                                <th>IP</th>
                                <th class="text-end">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($registros === []): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No se encontraron registros de auditoría para los filtros seleccionados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($registros as $registro): ?>
                                    <?php
                                    $accion = strtoupper((string) ($registro['accion'] ?? ''));
                                    $clase = $clasesAccion[$accion] ?? 'secondary';
                                    $usuarioEtiqueta = trim((string) ($registro['username'] ?? ''));
                                    if ($usuarioEtiqueta === '') {
                                        $usuarioEtiqueta = (int) ($registro['id_usuario'] ?? 0) > 0
                                            ? 'Usuario #' . (int) $registro['id_usuario']
                                            : 'Sistema';
                                    }
                                    ?>
                                    <tr>
                                        <td class="small text-muted"><?= $e($registro['fecha'] ?? '') ?></td>
                                        <td class="small">
                                            <span class="fw-semibold"><?= $e($usuarioEtiqueta) ?></span>
                                            <?php if (!empty($registro['usuario_nombre'])): ?>
                                                <div class="text-muted"><?= $e($registro['usuario_nombre']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small"><?= $e($registro['modulo'] ?? '') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $clase ?>"><?= $e($registro['accion'] ?? '') ?></span>
                                        </td>
                                        <td class="small" style="max-width: 360px;">
                                            <?= $e(mb_strimwidth((string) ($registro['descripcion'] ?? ''), 0, 120, '…')) ?>
                                        </td>
                                        <td class="small text-muted">
                                            <?= $e($registro['tabla_afectada'] ?? '—') ?>
                                            <?php if (!empty($registro['id_registro'])): ?>
                                                <div>#<?= (int) $registro['id_registro'] ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted"><?= $e($registro['ip'] ?? '—') ?></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#modalAuditoria"
                                                    data-id="<?= (int) $registro['id_auditoria'] ?>"
                                                    title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($paginas > 1): ?>
                <div class="card-footer bg-white">
                    <nav aria-label="Paginación de auditoría">
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $pagina <= 1 ? '#' : $e($urlPagina($pagina - 1)) ?>">Anterior</a>
                            </li>
                            <?php
                            $inicio = max(1, $pagina - 2);
                            $fin = min($paginas, $pagina + 2);
                            ?>
                            <?php for ($n = $inicio; $n <= $fin; $n++): ?>
                                <li class="page-item <?= $n === $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $e($urlPagina($n)) ?>"><?= $n ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $pagina >= $paginas ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $pagina >= $paginas ? '#' : $e($urlPagina($pagina + 1)) ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAuditoria" tabindex="-1" aria-labelledby="modalAuditoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAuditoriaLabel">Detalle de auditoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="auditoria-error" class="alert alert-danger d-none"></div>
                <dl class="row mb-0" id="auditoria-detalle">
                    <dt class="col-sm-4">Fecha</dt><dd class="col-sm-8" data-campo="fecha">—</dd>
                    <dt class="col-sm-4">Usuario</dt><dd class="col-sm-8" data-campo="usuario">—</dd>
                    <dt class="col-sm-4">Módulo</dt><dd class="col-sm-8" data-campo="modulo">—</dd>
                    <dt class="col-sm-4">Acción</dt><dd class="col-sm-8" data-campo="accion">—</dd>
                    <dt class="col-sm-4">Tabla afectada</dt><dd class="col-sm-8" data-campo="tabla">—</dd>
                    <dt class="col-sm-4">ID del registro</dt><dd class="col-sm-8" data-campo="registro">—</dd>
                    <dt class="col-sm-4">IP</dt><dd class="col-sm-8" data-campo="ip">—</dd>
                    <dt class="col-sm-4">Descripción</dt><dd class="col-sm-8" data-campo="descripcion">—</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalAuditoria');
    if (!modal) return;

    const errorBox = document.getElementById('auditoria-error');
    const campos = modal.querySelectorAll('[data-campo]');

    function pintar(registro) {
        const usuario = registro.username
            ? registro.username + (registro.usuario_nombre ? ' — ' + registro.usuario_nombre : '')
            : (registro.id_usuario ? 'Usuario #' + registro.id_usuario : 'Sistema');

        const valores = {
            fecha: registro.fecha || '—',
            usuario: usuario,
            modulo: registro.modulo || '—',
            accion: registro.accion || '—',
            tabla: registro.tabla_afectada || '—',
            registro: registro.id_registro || '—',
            ip: registro.ip || '—',
            descripcion: registro.descripcion || '—'
        };

        campos.forEach(function (campo) {
            const clave = campo.getAttribute('data-campo');
            campo.textContent = valores[clave] !== undefined ? valores[clave] : '—';
        });
    }

    modal.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        if (!boton) return;

        errorBox.classList.add('d-none');
        errorBox.textContent = '';
        campos.forEach(function (campo) { campo.textContent = 'Cargando…'; });

        const id = boton.getAttribute('data-id');
        if (!id) return;

        fetch('/auditoria/' + encodeURIComponent(id), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (respuesta) {
                if (!respuesta.ok) throw new Error('No se pudo cargar el registro.');
                return respuesta.json();
            })
            .then(function (data) {
                if (!data.success || !data.registro) throw new Error('Registro no encontrado.');
                pintar(data.registro);
            })
            .catch(function (error) {
                errorBox.textContent = error.message || 'Error al cargar el detalle.';
                errorBox.classList.remove('d-none');
                campos.forEach(function (campo) { campo.textContent = '—'; });
            });
    });
});
</script>
