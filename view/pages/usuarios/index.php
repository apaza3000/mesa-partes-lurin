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
                                        <!--combo box de ROLES 👇-->
                                        <select id="user-role-filter" class="form-select form-select-sm w-auto"
                                            aria-label="Filter by role">
                                            <option value="all" selected>All roles</option>
                                            <option value="administrator">Administrator</option>
                                            <option value="editor">Editor</option>
                                            <option value="author">Author</option>
                                            <option value="subscriber">Subscriber</option>
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
                                                            <img src="<?= htmlspecialchars( $user['avatar'] ?? '/view/assets/img/default-user.png', ENT_QUOTES, 'UTF-8') ?>"
                                                                alt="Avatar" class="rounded-circle me-2" width="32" height="32">
                                                            <span class="fw-medium">
                                                                <?= htmlspecialchars($user['name'] ?? $user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info text-dark">
                                                            <?= htmlspecialchars($user['rol'] ?? $user['rol_nombre'] ?? 'sin rol', ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-<?= ($user['status'] ?? $user['estado'] ?? '') === 'Activo' ? 'success' : 'secondary' ?>">
                                                            <?= htmlspecialchars($user['status'] ?? $user['estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
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
                            <div class="float-start pt-1 fs-7 text-body-secondary">
                                Showing 1 to 9 of 42 users
                            </div>
                            <!--divicion de registros << 1,2,3,4,5 >>👇📋 -->
                            <ul class="pagination pagination-sm m-0 float-end">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous"> &laquo; </a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">4</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">5</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#" aria-label="Next"> &raquo; </a>
                                </li>
                            </ul>
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