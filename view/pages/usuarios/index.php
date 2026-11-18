<?php
$paginaActual = $paginaActual ?? 0;
$totalPaginas = $totalPaginas ?? 0;
$filtros = $filtros ?? [];

?>
<main class="app-main">

    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Users</h1>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class=" float-sm-end">
                            <!--Boton nuevo usuario 👇-->
                            <a href="/usuarios/crear" class="btn btn-primary">
                                <i class="bi bi-person-plus-fill me-1" aria-hidden="true"> </i>
                                New user
                            </a>
                        </ol>
                    </nav>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content Header-->
    <!--begin::App Content-->
    <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <!--begin::Card-->


                    <div class="card mb-4">
                       

<!-- FILTROS -->
<div class="card-header">
    <form method="GET" action="/usuarios"
          class="d-flex flex-wrap align-items-end justify-content-between gap-3">

        <!-- CAMPOS DE FILTRO: IZQUIERDA -->
        <div class="d-flex flex-wrap align-items-end gap-2">

            <!-- BUSCADOR -->
            <div>
                <label for="q" class="form-label small fw-bold mb-1">
                    Buscar
                </label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    class="form-control"
                    placeholder="Nombre o usuario..."
                    value="<?= htmlspecialchars($filtros['q'] ?? '') ?>"
                >
            </div>

            <!-- ÁREA -->
            <div>
                <label for="area_id" class="form-label small fw-bold mb-1">
                    Área
                </label>
                <select name="area_id" id="area_id" class="form-select">
                    <option value="all">Todas las áreas</option>

                    <?php foreach ($areas as $area): ?>
                        <option
                            value="<?= $area['id_area'] ?>"
                            <?= ($filtros['area_id'] ?? 'all') == $area['id_area'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($area['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ROL -->
            <div>
                <label for="role_id" class="form-label small fw-bold mb-1">
                    Rol
                </label>
                <select name="role_id" id="role_id" class="form-select">
                    <option value="all">Todos los roles</option>

                    <?php foreach ($roles as $rol): ?>
                        <option
                            value="<?= $rol['id_rol'] ?>"
                            <?= ($filtros['role_id'] ?? 'all') == $rol['id_rol'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($rol['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ESTADO -->
            
<!-- ESTADO -->
<div>
    <label for="estado" class="form-label small fw-bold mb-1">
        Estado
    </label>

    <select name="estado" id="estado" class="form-select">
        <option value="all">Todos los estados</option>

        <?php foreach ($estados as $estado): ?>
            <option
                value="<?= htmlspecialchars($estado) ?>"
                <?= ($filtros['estado'] ?? 'all') === $estado ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($estado) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>


        </div>

        <!-- BOTONES: DERECHA -->
        <div class="d-flex align-items-end gap-2 ms-auto">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel"></i> Filtrar
            </button>

            <a href="/usuarios" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>

    </form>
</div>


                    </div>
                    <div class="card mb-4">
                        <!--tabla-->

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle m-0">
                                    <thead class="table table-hover align-middle ">
                                        <tr class="table-dark text-center">
                                            <th>N°</th>
                                            <th>User</th>
                                            <th>Area</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($usuarios)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">No hay usuarios registrados.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            // 1. Definir los registros mostrados por página (debe coincidir con el controlador)
                                            $porPagina = 10;
                                            // 2. Calcular el offset base según la página actual enviada por el controlador
                                            $pagina = $paginaActual ?? 1;
                                            $offsetBase = ($pagina - 1) * $porPagina;
                                            ?>

                                            <?php foreach ($usuarios as $indice => $user):
                                                $nombreCompleto = $user['nombres'] . ' ' . $user['apellido_paterno'] . ' ' . $user['apellido_materno']
                                                    ?>

                                                <tr>
                                                    <!--N°-->
                                                    <td>
                                                        <?= $offsetBase + $indice + 1 ?>
                                                    </td>
                                                    <!--USER-->
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="/view/<?= htmlspecialchars(!empty($user['avatar']) ? $user['avatar'] : 'assets/img/default-user.png', ENT_QUOTES, 'UTF-8') ?>"
                                                                onerror="this.onerror=null; this.src='/view/assets/img/default-user.png';"
                                                                alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                                                            <span class="fw-medium">
                                                                <?= htmlspecialchars($nombreCompleto ?? '*username_null*', ENT_QUOTES, 'UTF-8') ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <!--ESTADO-->
                                                    <td>
                                                        <?php
                                                        // Obtenemos el nombre AREA
                                                        $rolUsuario = $user['area_nombre'] ?? $user['area_nombre'] ?? '*sin_rol*';
                                                        // Asignamos la clase de AdminLTE según AREA
                                                        $badgeClass = match ($rolUsuario) {
                                                            'Mesa de Partes' => 'bg-danger text-dark',
                                                            'Dirección' => 'bg-warning text-dark',
                                                            'Secretaría Académica' => 'bg-primary text-dark',
                                                            'Administración' => 'bg-info text-dark',
                                                            'Jefatura de Unidad Académica' => 'bg-info text-dark',
                                                            'Coordinaciones de Área Académica' => 'bg-info text-dark',
                                                            'Bienestar Estudiantil' => 'bg-info text-dark',
                                                            'Archivo' => 'bg-success text-dark',
                                                            default => 'bg-secondary', // Si el rol no coincide con ningún caso
                                                        };
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>">
                                                            <?= htmlspecialchars($rolUsuario, ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    </td>
                                                    <!--ROL-->
                                                    <td>
                                                        <?php
                                                        // Obtenemos el nombre ROL
                                                        $rolUsuario = $user['rol'] ?? $user['rol_nombre'] ?? '*sin_rol*';
                                                        // Asignamos la clase de AdminLTE según el rol
                                                        $badgeClass = match ($rolUsuario) {
                                                            'ADMINISTRADOR' => 'bg-danger text-dark',
                                                            'MESA_DE_PARTES' => 'bg-warning text-dark',
                                                            'RESPONSABLE_AREA' => 'bg-primary text-dark',
                                                            'USUARIO_AREA' => 'bg-info text-dark',
                                                            'CONSULTA' => 'bg-success text-dark',
                                                            default => 'bg-secondary', // Si el rol no coincide con ningún caso
                                                        };
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>">
                                                            <?= htmlspecialchars($rolUsuario, ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    </td>

                                                    <!--ESTADO-->
                                                    <td>
                                                        <?php
                                                        // 1. Obtenemos el estado actual
                                                        $estado = $user['status'] ?? $user['estado'] ?? '*sin_estado*';
                                                        // 2. Evaluamos el color según el texto del estado
                                                        $bgClass = match ($estado) {
                                                            'Activo' => 'success',   // Verde
                                                            'Inactivo' => 'warning',   // Amarillo
                                                            'Suspendido' => 'danger',    // Rojo
                                                            default => 'secondary', // Gris para cualquier otro caso (*sin_estado*, etc.)
                                                        };
                                                        ?>
                                                        <span class="badge bg-<?= $bgClass ?>">
                                                            <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($user['created'] ?? $user['creado_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <!--VER-->
                                                        <a href="/usuarios/<?= (int) ($user['id_usuario'] ?? $user['id'] ?? 0) ?>"
                                                            class="btn btn-sm btn-info" title="Editar">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <!--EDITAR-->
                                                        <a href="/usuarios/<?= (int) ($user['id_usuario'] ?? $user['id'] ?? 0) ?>/editar"
                                                            class="btn btn-sm btn-warning text-white" title="Editar">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                        <!--ELIMNAR-->
                                                        <form
                                                            action="/usuarios/<?= (int) ($user['id_usuario'] ?? $user['id'] ?? 0) ?>/delete"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('¿Está seguro de eliminar este usuario?');">
                                                            <?= \App\Support\Csrf::campo() ?>
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                title="Eliminar">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>


                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
<!-- BARRA DE PAGINACIÓN -->
<?php
$parametrosPaginacion = $filtros ?? [];

$parametrosPaginacion = array_filter(
    $parametrosPaginacion,
    fn($valor) => $valor !== '' && $valor !== 'all' && $valor !== null
);
?>

<nav aria-label="Navegación de páginas" style="margin: 10px;">
    <ul class="pagination pagination-sm m-0 float-end">

        <!-- ANTERIOR -->
        <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
            <a class="page-link"
                href="?<?= http_build_query(array_merge($parametrosPaginacion, ['page' => max(1, $paginaActual - 1)])) ?>">
                &laquo;
            </a>
        </li>

        <!-- NÚMEROS -->
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item <?= ($i === $paginaActual) ? 'active' : '' ?>">
                <a class="page-link"
                    href="?<?= http_build_query(array_merge($parametrosPaginacion, ['page' => $i])) ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>

        <!-- SIGUIENTE -->
        <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
            <a class="page-link"
                href="?<?= http_build_query(array_merge($parametrosPaginacion, ['page' => min($totalPaginas, $paginaActual + 1)])) ?>">
                &raquo;
            </a>
        </li>

    </ul>
</nav>


                        <!--Fin de (barra de paginacion)-->
                    </div>
                </div>
            </div>
            <!--ventana emergente de [👤New user]-->
            <!--ventana emergente de [🗑️eliminarz usuario]👇-->
            <div class="modal fade" id="modal-delete-user" tabindex="-1" aria-labelledby="modal-delete-user-label"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modal-delete-user-label">Delete user</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">
                                Are you sure you want to delete this user? All content owned by the account
                                will be reassigned to the site administrator. This action cannot be undone.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                Delete user
                            </button>

                            <?php if (isset($errors['username'])): ?>
                                <div class="text-danger small mt-1">
                                    <?= $errors['username'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>