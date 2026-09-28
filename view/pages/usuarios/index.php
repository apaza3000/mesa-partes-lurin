<!--end::Sidebar-->
<!--begin::App Main-->
<main class="app-main">
    <!--Header (Users    Home/Users)-->
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
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Users</li>
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
                        <!--begin::Card Header-->
                        <div class="card-header">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-4">
                                    <h3 class="card-title">User Directory</h3>
                                </div>
                                <div class="col-12 col-md-8">
                                    <div class="d-flex flex-wrap justify-content-md-end gap-2">
                                        <div class="input-group input-group-sm w-auto">
                                            <span class="input-group-text">
                                                <i class="bi bi-search" aria-hidden="true"></i>
                                            </span>
                                            <input type="search" id="user-search" class="form-control"
                                                placeholder="Search users" aria-label="Search users"
                                                style="width: 180px" />
                                        </div>
                                        <!-- Combo box de ROLES 👇 -->
                                        <!-- En tu vista usuarios.index.php -->
                                        <select id="user-role-filter" name="role_id"
                                            class="form-select form-select-sm w-auto">
                                            <option value="all">TODOS LOS ROLES</option>

                                            <?php if (empty($roles)): ?>
                                                <option value="" disabled>No hay roles registrados</option>
                                            <?php else: ?>
                                                <?php foreach ($roles as $rol): ?>
                                                    <option value="<?= (int) $rol['id_rol'] ?>">
                                                        <?= htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <!--Boton nuevo usuario 👇-->
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modal-add-user">
                                            <i class="bi bi-person-plus-fill me-1" aria-hidden="true"> </i>
                                            New user
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--tabla-->
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle m-0">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>User</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                            <th>Created</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!$usuarios): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">No hay usuarios registrados.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($usuarios as $indice => $user): ?>
                                                <tr>
                                                    <td><?= $indice + 1 ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?= htmlspecialchars($user['avatar'] ?? '/view/assets/img/default-user.png', ENT_QUOTES, 'UTF-8') ?>"
                                                                alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                                                            <span class="fw-medium">
                                                                <?= htmlspecialchars($user['username'] ?? '*username_null*', ENT_QUOTES, 'UTF-8') ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        // Obtenemos el nombre o ID del rol
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
                                                        <a href="/usuarios/<?= (int) ($user['id_usuario'] ?? $user['id'] ?? 0) ?>/editar"
                                                            class="btn btn-sm btn-warning text-white" title="Editar">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                        <form
                                                            action="/usuarios/<?= (int) ($user['id_usuario'] ?? $user['id'] ?? 0) ?>/delete"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('¿Está seguro de eliminar este usuario?');">
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
                        <!--modificar en un futuro la barra de muestra para funcionar con SQL👇-->
                        <!--barra de muestra👇-->

                        <div class="card-footer clearfix">
                            <nav aria-label="Navegación de usuarios">
                                <ul class="pagination pagination-sm m-0 float-end">

                                    <!-- Botón Anterior («) -->
                                    <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $paginaActual - 1 ?>">&laquo;</a>
                                    </li>
                                    <!-- Botones Numéricos de Páginas -->
                                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                        <li class="page-item <?= ($i === $paginaActual) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <!-- Botón Siguiente (») -->
                                    <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $paginaActual + 1 ?>">&raquo;</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!--ventana emergente de [👤New user]👇-->
            <div class="modal fade" id="modal-add-user" tabindex="-1" aria-labelledby="modal-add-user-label"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form>
                            <div class="modal-header">
                                <h5 class="modal-title" id="modal-add-user-label">Add new user</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="new-user-name" class="form-label"> Full name </label>
                                    <input type="text" class="form-control" id="new-user-name"
                                        placeholder="e.g. Jane Doe" required />
                                </div>
                                <div class="mb-3">
                                    <label for="new-user-email" class="form-label"> Email address </label>
                                    <input type="email" class="form-control" id="new-user-email"
                                        placeholder="name@example.com" required />
                                    <div class="form-text">The invitation will be sent to this address.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="new-user-role" class="form-label"> Role </label>
                                    <select id="new-user-role" class="form-select">
                                        <option selected>Subscriber</option>
                                        <option>Author</option>
                                        <option>Editor</option>
                                        <option>Administrator</option>
                                    </select>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="new-user-welcome" checked />
                                    <label class="form-check-label" for="new-user-welcome">
                                        Send a welcome email with login details
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">Create user</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!--ventana emergente de [🗑️eliminar usuario]👇-->
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>