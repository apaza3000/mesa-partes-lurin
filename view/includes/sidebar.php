<?php

use App\Support\Csrf;
use App\Support\Roles;
use Src\Core\Session;

/*
 * Sidebar del layout `app`.
 *
 * SID-01: el rol se lee de la sesión (antes estaba hardcodeado a "admin").
 * SID-02: se usan los nombres reales de `Roles`, no claves inventadas.
 * SID-03: un único árbol de navegación (se retiró el bloque "DASHBOARD" duplicado).
 * SID-05: deny by default — sin `roles`, o con roles que no coinciden, no se muestra.
 * SID-06: "Cerrar sesión" es un POST con token (ya no apunta a `#`).
 * SID-07: el ítem activo se calcula contra `REQUEST_URI`.
 * FAT-04: solo se enlazan rutas que existen en `routes/*.php`.
 */

$rolesUsuario = Roles::delUsuarioActual();

$rutaActual = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$rutaActual = rtrim($rutaActual, '/') ?: '/';
$tokenCsrf = Csrf::token();

// Cada destino debe coincidir con una ruta registrada.
$navigation = [
    ['header' => 'PANEL'],
    [
        'title' => 'Inicio',
        'icon' => 'bi bi-speedometer2',
        'url' => '/dashboard',
        'roles' => Roles::Todos,
    ],

    ['header' => 'MESA DE PARTES'],
    [
        'title' => 'Recepción en ventanilla',
        'icon' => 'bi bi-inbox-fill',
        'url' => '/mesa-partes/registrar',
        'roles' => Roles::MesaDePartes,
    ],
    [
        'title' => 'Bandeja de Mesa de Partes',
        'icon' => 'bi bi-tray-fill',
        'url' => '/mesa-partes/bandeja',
        'roles' => Roles::MesaDePartes,
    ],
    [
        'title' => 'Mesa de Partes Virtual',
        'icon' => 'bi bi-envelope-paper-fill',
        'url' => '/entregas',
        'roles' => Roles::Todos,
    ],

    ['header' => 'GESTIÓN DE ÁREA'],
    [
        'title' => 'Bandeja de entrada',
        'icon' => 'bi bi-collection-fill',
        'url' => '/area/bandeja-entrada',
        'roles' => Roles::AtencionArea,
    ],
    [
        'title' => 'Documentos derivados',
        'icon' => 'bi bi-arrow-right-square-fill',
        'url' => '/area/documentos-derivados',
        'roles' => Roles::AtencionArea,
    ],

    ['header' => 'CONSULTAS'],
    [
        'title' => 'Buscar expediente',
        'icon' => 'bi bi-search',
        'url' => '/consulta',
        'roles' => Roles::Todos,
    ],

    ['header' => 'ADMINISTRACIÓN'],
    [
        'title' => 'Gestión de usuarios',
        'icon' => 'bi bi-people-fill',
        'roles' => Roles::Administracion,
        'children' => [
            [
                'title' => 'Usuarios',
                'icon' => 'bi bi-person-fill',
                'url' => '/usuarios',
                'roles' => Roles::Administracion,
            ],
            [
                'title' => 'Registrar usuario',
                'icon' => 'bi bi-person-plus-fill',
                'url' => '/usuarios/crear',
                'roles' => Roles::Administracion,
            ],
        ],
    ],
    [
        'title' => 'Gestión institucional',
        'icon' => 'bi bi-building-fill',
        'roles' => Roles::Administracion,
        'children' => [
            [
                'title' => 'Áreas',
                'icon' => 'bi bi-diagram-3-fill',
                'url' => '/areas',
                'roles' => Roles::Administracion,
            ],
            [
                'title' => 'Roles y permisos',
                'icon' => 'bi bi-shield-lock-fill',
                'url' => '/roles',
                'roles' => Roles::Administracion,
            ],
        ],
    ],
    [
        'title' => 'Personas remitentes',
        'icon' => 'bi bi-person-vcard-fill',
        'roles' => Roles::Administracion,
        'children' => [
            [
                'title' => 'Padrón de personas',
                'icon' => 'bi bi-people',
                'url' => '/personas',
                'roles' => Roles::Administracion,
            ],
            [
                'title' => 'Registrar persona',
                'icon' => 'bi bi-person-add',
                'url' => '/personas/crear',
                'roles' => Roles::MesaDePartes,
            ],
        ],
    ],

    ['header' => 'SISTEMA'],
    [
        'title' => 'Cerrar sesión',
        'icon' => 'bi bi-box-arrow-right',
        'logout' => true,
        'roles' => Roles::Todos,
    ],
];

/**
 * SID-05: acceso denegado por defecto. `$item['roles']` es obligatorio.
 *
 * @param array<string, mixed> $item
 */
function sidebarPermitido(array $item, array $rolesUsuario): bool
{
    if (!isset($item['roles']) || $rolesUsuario === []) {
        return false;
    }

    return Roles::usuarioTiene($item['roles']);
}

/**
 * SID-07: ¿el ítem corresponde a la URL actual? `/` solo coincide en exacta;
 * el resto también coincide con sus subrutas (`/usuarios/12/editar` activa `/usuarios`).
 *
 * @param array<string, mixed> $item
 */
function sidebarActivo(array $item, string $rutaActual): bool
{
    $url = (string) ($item['url'] ?? '');

    if ($url === '' || $url === '#') {
        return false;
    }

    $url = rtrim($url, '/') ?: '/';

    if ($url === '/') {
        return $rutaActual === '/';
    }

    return $rutaActual === $url || str_starts_with($rutaActual, $url . '/');
}

/**
 * @param array<string, mixed> $item
 */
function sidebarRender(array $item, array $rolesUsuario, string $rutaActual, string $tokenCsrf): string
{
    if (isset($item['header'])) {
        return '<li class="nav-header">' . htmlspecialchars($item['header'], ENT_QUOTES, 'UTF-8') . '</li>';
    }

    if (!sidebarPermitido($item, $rolesUsuario)) {
        return '';
    }

    // SID-05: un padre con submenú no debe mostrarse si ninguno de sus hijos
    // es permitido (si no, el CONSULTA vería «Padrón de personas» vacío).
    if (!empty($item['children'])) {
        $visibles = array_filter(
            $item['children'],
            static fn(array $h): bool => sidebarPermitido($h, $rolesUsuario)
        );
        if ($visibles === []) {
            return '';
        }
    }

    $icono = isset($item['icon'])
        ? '<i class="nav-icon ' . htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') . '"></i>'
        : '';
    $titulo = htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8');

    // SID-06: el cierre de sesión viaja en un formulario POST con token.
    if (!empty($item['logout'])) {
        return '
        <li class="nav-item">
            <form action="/logout" method="post" class="m-0">
                <input type="hidden" name="csrf_token" value="' . htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') . '">
                <button type="submit" class="nav-link border-0 bg-transparent text-start w-100">'
            . $icono . '<p>' . $titulo . '</p></button>
            </form>
        </li>';
    }

    $hijos = $item['children'] ?? [];

    if (is_array($hijos) && $hijos !== []) {
        $htmlHijos = '';
        $activo = false;

        foreach ($hijos as $hijo) {
            $htmlHijos .= sidebarRender($hijo, $rolesUsuario, $rutaActual, $tokenCsrf);

            if (sidebarPermitido($hijo, $rolesUsuario) && sidebarActivo($hijo, $rutaActual)) {
                $activo = true;
            }
        }

        // Un padre cuyos hijos quedaron todos filtrados no debe aparecer.
        if (trim($htmlHijos) === '') {
            return '';
        }

        $claseActiva = $activo ? ' active' : '';

        return '
        <li class="nav-item menu-open' . $claseActiva . '">
            <a href="#" class="nav-link' . $claseActiva . '">
                ' . $icono . '
                <p>' . $titulo . '<i class="nav-arrow bi bi-chevron-right"></i></p>
            </a>
            <ul class="nav nav-treeview">' . $htmlHijos . '</ul>
        </li>';
    }

    $activo = sidebarActivo($item, $rutaActual) ? ' active' : '';
    $url = htmlspecialchars((string) ($item['url'] ?? '#'), ENT_QUOTES, 'UTF-8');

    return '
        <li class="nav-item">
            <a href="' . $url . '" class="nav-link' . $activo . '">
                ' . $icono . '
                <p>' . $titulo . '</p>
            </a>
        </li>';
}

$htmlNavegacion = '';
// SID-01: los encabezados («ADMINISTRACIÓN») solo se imprimen si el grupo
// tiene al menos un ítem visible para el rol; evita secciones huérfanas.
$grupoPendiente = null; // string de header aguardando su primer ítem visible
$renderizarGrupo = static function (?string $header) use (&$htmlNavegacion): void {
    if ($header !== null) {
        $htmlNavegacion .= '<li class="nav-header">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</li>';
    }
};
foreach ($navigation as $item) {
    if (isset($item['header'])) {
        $grupoPendiente = (string) $item['header'];
        continue;
    }
    $html = sidebarRender($item, $rolesUsuario, $rutaActual, $tokenCsrf);
    if ($html !== '') {
        $renderizarGrupo($grupoPendiente);
        $grupoPendiente = null;
        $htmlNavegacion .= $html;
    }
}
?>

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <div class="sidebar-brand">
    <a href="/dashboard" class="brand-link">
      <img src="/view/assets/img/AdminLTELogo.png" alt="Logo de la institución" class="brand-image opacity-75 shadow" />
      <span class="brand-text fw-light">Mesa de Partes</span>
    </a>
  </div>

  <div class="sidebar-wrapper">
    <nav class="mt-2" aria-label="Main navigation">
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false" id="navigation">
        <?= $htmlNavegacion ?>
      </ul>
    </nav>
  </div>
</aside>
