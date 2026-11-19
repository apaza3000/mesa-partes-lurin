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
            <!-- TAREA E08-1: notificaciones reales del usuario autenticado -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" id="notif-toggle"
                    aria-label="Notificaciones: <?= $sinLeer ?> sin leer">
                    <i class="bi bi-bell-fill"></i>
                    <span id="notif-badge" class="navbar-badge badge text-bg-danger <?= $sinLeer > 0 ? '' : 'd-none' ?>"><?= (int) $sinLeer ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end" id="notif-menu">
                    <span class="dropdown-item dropdown-header" id="notif-header">
                        <?= $sinLeer ?> notificaci<?= $sinLeer === 1 ? 'ón' : 'ones' ?> sin leer
                    </span>
                    <div class="dropdown-divider"></div>
                    <div id="notif-lista">
                        <?php if ($listaNotificaciones === []): ?>
                            <a href="#" class="dropdown-item text-muted" id="notif-vacio">No tiene notificaciones pendientes.</a>
                        <?php else: ?>
                            <?php foreach ($listaNotificaciones as $notificacion): ?>
                                <?php
                                $codigoNotif = (string) ($notificacion['codigo_expediente'] ?? '');
                                $enlaceNotif = $codigoNotif !== ''
                                    ? '/consulta?codigo=' . rawurlencode($codigoNotif)
                                    : '/consulta';
                                ?>
                                <div class="dropdown-item d-flex align-items-start gap-2 notif-item"
                                     data-id="<?= (int) $notificacion['id_notificacion'] ?>">
                                    <a href="<?= htmlspecialchars($enlaceNotif, ENT_QUOTES, 'UTF-8') ?>"
                                       class="flex-grow-1 text-decoration-none text-reset">
                                        <p class="dropdown-item-title mb-1 fw-semibold">
                                            <?= htmlspecialchars((string) ($notificacion['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <p class="fs-7 mb-1">
                                            <?= htmlspecialchars(mb_strimwidth((string) ($notificacion['mensaje'] ?? ''), 0, 110, '…'), ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <p class="fs-7 text-secondary mb-0">
                                            <i class="bi bi-clock-fill me-1"></i>
                                            <?= htmlspecialchars((string) ($notificacion['creado_en'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary notif-leer"
                                            data-id="<?= (int) $notificacion['id_notificacion'] ?>"
                                            title="Marcar como leída" aria-label="Marcar como leída">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </div>
                                <div class="dropdown-divider"></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="dropdown-footer text-center">
                        <button type="button" class="btn btn-sm btn-link" id="notif-marcar-todas">
                            Marcar todas como leídas
                        </button>
                    </div>
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

<script>
(function () {
    'use strict';

    const token = <?= json_encode($token, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const badge = document.getElementById('notif-badge');
    const header = document.getElementById('notif-header');
    const lista = document.getElementById('notif-lista');
    const marcarTodas = document.getElementById('notif-marcar-todas');

    if (!badge || !header || !lista) {
        return;
    }

    function actualizarContador(total) {
        const n = Math.max(0, parseInt(total, 10) || 0);
        badge.textContent = String(n);
        badge.classList.toggle('d-none', n === 0);
        header.textContent = n + (n === 1 ? ' notificación sin leer' : ' notificaciones sin leer');
        const toggle = document.getElementById('notif-toggle');
        if (toggle) {
            toggle.setAttribute('aria-label', 'Notificaciones: ' + n + ' sin leer');
        }
    }

    function mostrarVacio() {
        if (lista.querySelector('.notif-item')) {
            return;
        }
        let vacio = document.getElementById('notif-vacio');
        if (!vacio) {
            vacio = document.createElement('a');
            vacio.id = 'notif-vacio';
            vacio.href = '#';
            vacio.className = 'dropdown-item text-muted';
            vacio.textContent = 'No tiene notificaciones pendientes.';
            lista.appendChild(vacio);
        }
    }

    function quitarItem(boton) {
        const item = boton.closest('.notif-item');
        if (!item) {
            return;
        }
        const separador = item.nextElementSibling;
        if (separador && separador.classList.contains('dropdown-divider')) {
            separador.remove();
        }
        item.remove();
        mostrarVacio();
    }

    function peticion(url, cuerpo) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: cuerpo
        }).then(function (respuesta) {
            return respuesta.json().catch(function () {
                return { success: false, sin_leer: null };
            });
        });
    }

    lista.addEventListener('click', function (evento) {
        const boton = evento.target.closest('.notif-leer');
        if (!boton) {
            return;
        }
        evento.preventDefault();
        evento.stopPropagation();

        const id = boton.getAttribute('data-id');
        boton.disabled = true;

        peticion('/notificaciones/leer', 'csrf_token=' + encodeURIComponent(token) + '&id=' + encodeURIComponent(id))
            .then(function (datos) {
                if (!datos.success) {
                    boton.disabled = false;
                    return;
                }
                quitarItem(boton);
                actualizarContador(datos.sin_leer);
            })
            .catch(function () {
                boton.disabled = false;
            });
    });

    if (marcarTodas) {
        marcarTodas.addEventListener('click', function () {
            marcarTodas.disabled = true;
            peticion('/notificaciones/leer-todas', 'csrf_token=' + encodeURIComponent(token))
                .then(function (datos) {
                    if (datos.success) {
                        lista.querySelectorAll('.notif-item').forEach(function (item) {
                            item.remove();
                        });
                        lista.querySelectorAll('.dropdown-divider').forEach(function (d) {
                            d.remove();
                        });
                        mostrarVacio();
                        actualizarContador(0);
                    }
                    marcarTodas.disabled = false;
                })
                .catch(function () {
                    marcarTodas.disabled = false;
                });
        });
    }

    // E08-1.8: refresco del contador cada 60 segundos, sin recargar la página.
    setInterval(function () {
        fetch('/notificaciones/contador', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (datos) {
                if (datos && datos.success) {
                    actualizarContador(datos.sin_leer);
                }
            })
            .catch(function () { /* silencio: el header no debe romper la pantalla */ });
    }, 60000);
})();
</script>
