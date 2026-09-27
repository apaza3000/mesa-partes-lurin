<?php
// Helper para asignar color del badge según el rol de la BD
function getRoleBadgeClass(?string $role): string
{
    return match ($role) {
        'Administrador', 'Administrator', 'admin' => 'text-bg-danger',
        'Editor' => 'text-bg-primary',
        'Autor', 'Author' => 'text-bg-info',
        default => 'text-bg-secondary',
    };
}

// Helper para asignar color del badge según el estado
function getStatusBadgeClass(?string $status): string
{
    return match ($status) {
        'Activo', 'Active' => 'text-bg-success',
        'Pendiente', 'Pending' => 'text-bg-warning',
        'Inactivo', 'Suspendido' => 'text-bg-danger',
        default => 'text-bg-secondary',
    };
}
?>
<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle m-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars($user['avatar']) ?>" alt=""
                                    class="img-size-32 rounded-circle me-2" />
                                <span class="fw-medium">
                                    <?= htmlspecialchars($user['name']) ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <?= htmlspecialchars($user['email']) ?>
                        </td>
                        <td>
                            <span class="badge <?= getRoleBadgeClass($user['role']) ?>">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($user['status']) ?>">
                                <?= htmlspecialchars($user['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($user['created']) ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary"
                                    aria-label="Edit <?= htmlspecialchars($user['name']) ?>">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#modal-delete-user"
                                    aria-label="Delete <?= htmlspecialchars($user['name']) ?>">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>