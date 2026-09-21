<?php
// En el futuro, esta variable se llenara con la consulta SQL
// simulacion de datos
$users = [
    [
        'id' => 1,
        'name' => 'Alexander Pierce',
        'avatar' => '../assets/img/user1-128x128.jpg',
        'email' => 'alexander.pierce@example.com',
        'role' => 'Administrator',
        'status' => 'Active',
        'created' => 'Mar 12, 2025'
    ],
    [
        'id' => 2,
        'name' => 'Sarah Bullock',
        'avatar' => '../assets/img/user3-128x128.jpg',
        'email' => 'sarah.bullock@example.com',
        'role' => 'Editor',
        'status' => 'Active',
        'created' => 'Apr 3, 2025'
    ],
    [
        'id' => 3,
        'name' => 'Daniel Cooper',
        'avatar' => '../assets/img/user6-128x128.jpg',
        'email' => 'daniel.cooper@example.com',
        'role' => 'Author',
        'status' => 'Pending',
        'created' => 'Apr 28, 2025'
    ]
];

// Funciones helper para asignar el color del badge en Bootstrap
function getRoleBadgeClass($role)
{
    switch ($role) {
        case 'Administrator':
            return 'text-bg-danger';
        case 'Editor':
            return 'text-bg-primary';
        case 'Author':
            return 'text-bg-info';
        default:
            return 'text-bg-secondary';
    }
}

function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'Active':
            return 'text-bg-success';
        case 'Pending':
            return 'text-bg-warning';
        case 'Suspended':
            return 'text-bg-danger';
        default:
            return 'text-bg-secondary';
    }
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