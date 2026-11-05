<?php

use App\Models\Notificacion;
use App\Support\Roles;
use Src\Core\Session;

/*
 * Barra superior (header) del layout `app`.
 *
 * SID-08 / SEC-07: todo dato de sesión o de BD se escapa; ya no se imprimen
 * contadores inventados ni perfiles de demostración, y el cierre de sesión se
 * envía por POST con token CSRF (antes era un enlace GET, falsificable).
 */

$user = Session::user() ?? [];

$nombres = trim(
    (string) ($user['nombres'] ?? '') . ' ' .
    (string) ($user['apellido_paterno'] ?? '') . ' ' .
    (string) ($user['apellido_materno'] ?? '')
);

if ($nombres === '') {
    $nombres = (string) ($user['username'] ?? 'Usuario');
}

// MEN-03: `roles` llegó a ser una cadena; hoy es array. Se soportan ambas formas.
$rolesCrudos = Roles::normalize($user['roles'] ?? ($user['rol'] ?? null));
$rolesEtiquetas = array_map(static fn($r) => Roles::etiqueta($r), $rolesCrudos);
$rolesTexto = $rolesEtiquetas !== [] ? implode(' · ', $rolesEtiquetas) : 'Sin rol asignado';

$idUsuario = (int) ($user['id_usuario'] ?? Session::get('usuario_id') ?? 0);

// SID-08: conteos reales (con tolerancia a BD caída o tabla vacía).
try {
    $notificador = new Notificacion();
    $sinLeer = $notificador->sinLeer($idUsuario);
    $listaNotificaciones = $notificador->ultimas($idUsuario, 5);
} catch (Throwable $e) {
    $sinLeer = 0;
    $listaNotificaciones = [];
}

$token = App\Support\Csrf::token();
?>

<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <!-- Notificaciones reales del usuario autenticado -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#"
                    aria-label="Notificaciones: <?= $sinLeer ?> sin leer">
                    <i class="bi bi-bell-fill"></i>
                    <?php if ($sinLeer > 0): ?>
                        <span class="navbar-badge badge text-bg-danger"><?= (int) $sinLeer ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <span class="dropdown-item dropdown-header">
                        <?= $sinLeer ?> notificaci<?= $sinLeer === 1 ? 'ón' : 'ones' ?> sin leer
                    </span>
                    <div class="dropdown-divider"></div>
                    <?php if ($listaNotificaciones === []): ?>
                        <a href="#" class="dropdown-item text-muted">No tiene notificaciones pendientes.</a>
                    <?php else: ?>
                        <?php foreach ($listaNotificaciones as $notificacion): ?>
                            <a href="#" class="dropdown-item">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="dropdown-item-title">
                                            <?= htmlspecialchars((string) ($notificacion['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <p class="fs-7">
                                            <?= htmlspecialchars(mb_strimwidth((string) ($notificacion['mensaje'] ?? ''), 0, 90, '…'), ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <p class="fs-7 text-secondary">
                                            <i class="bi bi-clock-fill me-1"></i>
                                            <?= htmlspecialchars((string) ($notificacion['creado_en'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </li>

            <!-- Alternancia de esquema de color (control del template) -->
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" id="bd-theme" aria-label="Toggle color scheme" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bi bi-sun-fill" data-lte-theme-icon="light"></i>
                    <i class="bi bi-moon-fill d-none" data-lte-theme-icon="dark"></i>
                    <i class="bi bi-circle-half d-none" data-lte-theme-icon="auto"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme"
                    style="--bs-dropdown-min-width: 8rem">
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center"
                            data-bs-theme-value="light" aria-pressed="false">
                            <i class="bi bi-sun-fill me-2"></i>
                            Light
                            <i class="bi bi-check-lg ms-auto d-none"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark"
                            aria-pressed="false">
                            <i class="bi bi-moon-fill me-2"></i>
                            Dark
                            <i class="bi bi-check-lg ms-auto d-none"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center active"
                            data-bs-theme-value="auto" aria-pressed="true">
                            <i class="bi bi-circle-half me-2"></i>
                            Auto
                            <i class="bi bi-check-lg ms-auto d-none"></i>
                        </button>
                    </li>
                </ul>
            </li>

            <!-- Menú de usuario -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="/view/assets/img/user2-160x160.jpg" class="user-image rounded-circle shadow"
                        alt="Avatar del usuario" />
                    <span class="d-none d-md-inline"><?= htmlspecialchars($nombres, ENT_QUOTES, 'UTF-8') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <li class="user-header text-bg-primary">
                        <img src="/view/assets/img/user2-160x160.jpg" class="rounded-circle shadow"
                            alt="Avatar del usuario" />
                        <p>
                            <?= htmlspecialchars($nombres, ENT_QUOTES, 'UTF-8') ?>
                            <small><?= htmlspecialchars($rolesTexto, ENT_QUOTES, 'UTF-8') ?></small>
                        </p>
                    </li>
                    <li class="user-footer">
                        <?php if (Roles::usuarioTiene(Roles::Administracion)): ?>
                            <a href="/usuarios/<?= $idUsuario ?>" class="btn btn-outline-secondary">Mi usuario</a>
                        <?php else: ?>
                            <a href="/dashboard" class="btn btn-outline-secondary">Mi panel</a>
                        <?php endif; ?>

                        <!-- Logout por POST + CSRF: un <img> externo ya no puede cerrar la sesión. -->
                        <form action="/logout" method="post" class="d-inline float-end">
                            <input type="hidden" name="csrf_token"
                                value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn btn-outline-danger">Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
