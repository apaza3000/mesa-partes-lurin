<?php

define('ROLE_ADMINISTRADOR', 'admin');
define('ROLE_MESA_DE_PARTES', 'mesaParte');
define('ROLE_PERSONAL_ADMINISTRATIVO', 'jefeArea');
define('ROLE_CONSULTOR', 'consultor');

// Obtener rol del usuario (ejemplo)
$userRole = "admin";

function hasAccess($itemRoles, $userRole)
{
  if (empty($itemRoles)) {
    return true;
  }

  return in_array($userRole, $itemRoles);
}


// ============================================================
// NAVEGACIÓN DEL SISTEMA
// ============================================================

$navigation = [

  // ========================================================
  // ADMINISTRADOR
  // ========================================================

  [
    'header' => 'ADMINISTRACIÓN'
  ],

  [
    'title' => 'Gestión de usuarios',
    'icon' => 'bi bi-people-fill',
    'roles' => ['admin'],
    'children' => [

      [
        'title' => 'Usuarios',
        'icon' => 'bi bi-person-fill',
        'url' => '/view/pages/users.php',
        'roles' => ['admin']
      ],

      [
        'title' => 'Roles y permisos',
        'icon' => 'bi bi-shield-lock-fill',
        'url' => '#',
        'roles' => ['admin']
      ]

    ]
  ],

  [
    'title' => 'Gestión institucional',
    'icon' => 'bi bi-building-fill',
    'roles' => ['admin'],
    'children' => [

      [
        'title' => 'Áreas',
        'icon' => 'bi bi-diagram-3-fill',
        'url' => '#',
        'roles' => ['admin']
      ],

      [
        'title' => 'Tipos de documentos',
        'icon' => 'bi bi-file-earmark-text-fill',
        'url' => '#',
        'roles' => ['admin']
      ],

      [
        'title' => 'Estados',
        'icon' => 'bi bi-list-check',
        'url' => '#',
        'roles' => ['admin']
      ]

    ]
  ],

  [
    'title' => 'Gestión documentaria',
    'icon' => 'bi bi-folder-fill',
    'roles' => ['admin'],
    'children' => [

      [
        'title' => 'Documentos',
        'icon' => 'bi bi-file-earmark-text',
        'url' => '#',
        'roles' => ['admin']
      ],

      [
        'title' => 'Seguimiento documentario',
        'icon' => 'bi bi-clock-history',
        'url' => '#',
        'roles' => ['admin']
      ]

    ]
  ],

  [
    'title' => 'Reportes',
    'icon' => 'bi bi-bar-chart-fill',
    'roles' => ['admin'],
    'children' => [

      [
        'title' => 'Reportes documentarios',
        'icon' => 'bi bi-file-earmark-bar-graph-fill',
        'url' => '#',
        'roles' => ['admin']
      ]

    ]
  ],


  // ========================================================
  // MESA DE PARTES
  // ========================================================

  [
    'header' => 'MESA DE PARTES'
  ],

  [
    'title' => 'Recepción',
    'icon' => 'bi bi-inbox-fill',
    'roles' => ['mesaParte'],
    'children' => [

      [
        'title' => 'Registrar documento',
        'icon' => 'bi bi-file-earmark-plus-fill',
        'url' => '#',
        'roles' => ['mesaParte']
      ],

      [
        'title' => 'Documentos recibidos',
        'icon' => 'bi bi-envelope-arrow-down-fill',
        'url' => '#',
        'roles' => ['mesaParte']
      ],

      [
        'title' => 'Documentos enviados',
        'icon' => 'bi bi-envelope-arrow-up-fill',
        'url' => '#',
        'roles' => ['mesaParte']
      ]

    ]
  ],

  [
    'title' => 'Derivación',
    'icon' => 'bi bi-arrow-left-right',
    'roles' => ['mesaParte'],
    'children' => [

      [
        'title' => 'Derivar documento',
        'icon' => 'bi bi-send-fill',
        'url' => '#',
        'roles' => ['mesaParte']
      ],

      [
        'title' => 'Historial de derivaciones',
        'icon' => 'bi bi-clock-history',
        'url' => '#',
        'roles' => ['mesaParte']
      ]

    ]
  ],

  [
    'title' => 'Seguimiento',
    'icon' => 'bi bi-search',
    'roles' => ['mesaParte'],
    'children' => [

      [
        'title' => 'Seguimiento documentario',
        'icon' => 'bi bi-activity',
        'url' => '#',
        'roles' => ['mesaParte']
      ],

      [
        'title' => 'Historial de movimientos',
        'icon' => 'bi bi-list-ul',
        'url' => '#',
        'roles' => ['mesaParte']
      ]

    ]
  ],

  [
    'title' => 'Archivo',
    'icon' => 'bi bi-archive-fill',
    'url' => '#',
    'roles' => ['mesaParte']
  ],

  [
    'title' => 'Consultas',
    'icon' => 'bi bi-search',
    'url' => '#',
    'roles' => ['mesaParte']
  ],


  // ========================================================
  // PERSONAL ADMINISTRATIVO / JEFE DE ÁREA
  // ========================================================

  [
    'header' => 'ÁREA ADMINISTRATIVA'
  ],

  [
    'title' => 'Bandeja',
    'icon' => 'bi bi-inbox-fill',
    'roles' => ['jefeArea'],
    'children' => [

      [
        'title' => 'Documentos recibidos',
        'icon' => 'bi bi-envelope-fill',
        'url' => '#',
        'roles' => ['jefeArea']
      ],

      [
        'title' => 'Documentos atendidos',
        'icon' => 'bi bi-check2-square',
        'url' => '#',
        'roles' => ['jefeArea']
      ]

    ]
  ],

  [
    'title' => 'Gestión documentaria',
    'icon' => 'bi bi-folder-fill',
    'roles' => ['jefeArea'],
    'children' => [

      [
        'title' => 'Documentos derivados',
        'icon' => 'bi bi-arrow-down-square-fill',
        'url' => '#',
        'roles' => ['jefeArea']
      ],

      [
        'title' => 'Actualizar estado',
        'icon' => 'bi bi-arrow-repeat',
        'url' => '#',
        'roles' => ['jefeArea']
      ]

    ]
  ],

  [
    'title' => 'Observaciones',
    'icon' => 'bi bi-chat-left-text-fill',
    'roles' => ['jefeArea'],
    'children' => [

      [
        'title' => 'Registrar observación',
        'icon' => 'bi bi-pencil-square',
        'url' => '#',
        'roles' => ['jefeArea']
      ]

    ]
  ],

  [
    'title' => 'Consultas',
    'icon' => 'bi bi-search',
    'roles' => ['jefeArea'],
    'children' => [

      [
        'title' => 'Buscar documentos',
        'icon' => 'bi bi-search',
        'url' => '#',
        'roles' => ['jefeArea']
      ],

      [
        'title' => 'Historial',
        'icon' => 'bi bi-clock-history',
        'url' => '#',
        'roles' => ['jefeArea']
      ]

    ]
  ],


  // ========================================================
  // CONSULTOR
  // ========================================================

  [
    'header' => 'CONSULTAS'
  ],

  [
    'title' => 'Inicio',
    'icon' => 'bi bi-house-fill',
    'url' => '#',
    'roles' => ['consultor']
  ],

  [
    'title' => 'Consulta de expedientes',
    'icon' => 'bi bi-search',
    'roles' => ['consultor'],
    'children' => [

      [
        'title' => 'Consultar expediente',
        'icon' => 'bi bi-file-earmark-search-fill',
        'url' => '#',
        'roles' => ['consultor']
      ],

      [
        'title' => 'Buscar documentos',
        'icon' => 'bi bi-search',
        'url' => '#',
        'roles' => ['consultor']
      ]

    ]
  ],

  [
    'title' => 'Seguimiento',
    'icon' => 'bi bi-activity',
    'roles' => ['consultor'],
    'children' => [

      [
        'title' => 'Trazabilidad documentaria',
        'icon' => 'bi bi-diagram-3',
        'url' => '#',
        'roles' => ['consultor']
      ],

      [
        'title' => 'Historial',
        'icon' => 'bi bi-clock-history',
        'url' => '#',
        'roles' => ['consultor']
      ]

    ]
  ],


  // ========================================================
  // OPCIONES GENERALES
  // ========================================================

  [
    'header' => 'SISTEMA'
  ],

  [
    'title' => 'Mi perfil',
    'icon' => 'bi bi-person-circle',
    'url' => '#',
    'roles' => ['admin', 'mesaParte', 'jefeArea', 'consultor']
  ],

  [
    'title' => 'Configuración',
    'icon' => 'bi bi-gear-wide',
    'url' => '#',
    'roles' => ['admin', 'mesaParte', 'jefeArea', 'consultor']
  ],

  [
    'title' => 'Cerrar sesión',
    'icon' => 'bi bi-box-arrow-right',
    'url' => '#',
    'roles' => ['admin', 'mesaParte', 'jefeArea', 'consultor']
  ],

  //fin de menu definitivo_________________________________
];

// Función recursiva para renderizar los items del menú
function renderNavItem($item, $userRole)
{
  // Si es un header
  if (isset($item['header'])) {
    return '<li class="nav-header">' . htmlspecialchars($item['header']) . '</li>';
  }

  // Verificar si el usuario tiene acceso al item
  if (!hasAccess($item['roles'] ?? [], $userRole)) {
    return '';
  }

  // Verificar si tiene hijos
  $hasChildren = isset($item['children']) && !empty($item['children']);

  // Clase active
  $activeClass = isset($item['active']) && $item['active'] ? ' active' : '';

  // Badge
  $badgeHtml = '';
  if (isset($item['badge'])) {
    $badgeHtml = '<span class="nav-badge badge ' . $item['badge']['class'] . ' me-3">' . $item['badge']['text'] . '</span>';
  }

  // Icon
  $iconHtml = isset($item['icon']) ? '<i class="nav-icon ' . $item['icon'] . '"></i>' : '';

  // Flecha para items con hijos
  $arrowHtml = $hasChildren ? '<i class="nav-arrow bi bi-chevron-right"></i>' : '';

  // Si tiene hijos, renderizar como acordeón
  if ($hasChildren) {
    $childrenHtml = '';
    foreach ($item['children'] as $child) {
      $childrenHtml .= renderNavItem($child, $userRole);
    }

    // Si todos los hijos están ocultos por roles, ocultar el padre también
    if (empty(trim($childrenHtml))) {
      return '';
    }

    // Verificar si el primer nivel del children tiene children (para mantener compatibilidad con iconos)
    $hasGrandChildren = false;
    foreach ($item['children'] as $child) {
      if (isset($child['children']) && !empty($child['children'])) {
        $hasGrandChildren = true;
        break;
      }
    }

    // Para items con hijos que a su vez tienen hijos (nivel 3+), mantener el icono bi bi-circle
    // Para items con hijos directos que no tienen más hijos, usar bi bi-box-arrow-in-right o el icono correspondiente
    $childIcon = 'bi bi-circle';
    if (isset($item['icon']) && strpos($item['icon'], 'box-arrow-in-right') !== false) {
      $childIcon = 'bi bi-box-arrow-in-right';
    }

    // Modificar los hijos para que usen el icono correcto
    if ($hasGrandChildren) {
      // Para los items que tienen nietos, mantener el icono original
      $childrenHtml = '';
      foreach ($item['children'] as $child) {
        // Si el child tiene children, no modificar su icono
        if (isset($child['children']) && !empty($child['children'])) {
          $childrenHtml .= renderNavItem($child, $userRole);
        } else {
          // Si no tiene children, asegurar que tenga el icono correcto
          if (!isset($child['icon'])) {
            $child['icon'] = 'bi bi-circle';
          }
          $childrenHtml .= renderNavItem($child, $userRole);
        }
      }
    }

    // Re-renderizar children si es necesario
    $childrenHtml = '';
    foreach ($item['children'] as $child) {
      $childrenHtml .= renderNavItem($child, $userRole);
    }

    return '
        <li class="nav-item">
            <a href="#" class="nav-link' . $activeClass . '">
                ' . $iconHtml . '
                <p>
                    ' . htmlspecialchars($item['title']) . '
                    ' . $badgeHtml . '
                    ' . $arrowHtml . '
                </p>
            </a>
            <ul class="nav nav-treeview">
                ' . $childrenHtml . '
            </ul>
        </li>';
  } else {
    // Item simple con URL
    $url = $item['url'] ?? '#';
    return '
        <li class="nav-item">
            <a href="' . htmlspecialchars($url) . '" class="nav-link' . $activeClass . '">
                ' . $iconHtml . '
                <p>' . htmlspecialchars($item['title']) . '</p>
            </a>
        </li>';
  }
}

// Renderizar el sidebar completo
function renderSidebar($nav, $userRole)
{
  $html = '';
  foreach ($nav as $item) {
    $html .= renderNavItem($item, $userRole);
  }
  return $html;
}
?>

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand">
    <!--begin::Brand Link-->
    <a href="./index.html" class="brand-link">
      <!--begin::Brand Image-->
      <img src="/view/assets/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow" />
      <!--end::Brand Image-->
      <!--begin::Brand Text-->
      <span class="brand-text fw-light">AdminLTE 4</span>
      <!--end::Brand Text-->
    </a>
    <!--end::Brand Link-->
  </div>
  <!--end::Sidebar Brand-->
  <!--begin::Sidebar Search-->
  <div class="sidebar-search" role="search">
    <label for="sidebar-search-input" class="visually-hidden">Filter menu</label>
    <input type="search" id="sidebar-search-input" class="form-control form-control-sm" placeholder="Filter menu…"
      autocomplete="off" data-lte-toggle="sidebar-search" data-lte-target="#navigation" />
    <p class="fs-7 text-secondary mt-2 mb-0" data-lte-search-empty role="status" hidden>
      No matching pages.
    </p>
  </div>
  <!--end::Sidebar Search-->
  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper">
    <nav class="mt-2" aria-label="Main navigation">
      <!--begin::Sidebar Menu-->
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false" id="navigation">
        <?php echo renderSidebar($navigation, $userRole); ?>
      </ul>
      <!--end::Sidebar Menu-->

      <!-- Docs CTA (bottom of sidebar) 
      <div class="p-3 mt-3 border-top border-secondary border-opacity-25">
        <a href="./docs/introduction.html"
          class="btn btn-sm btn-outline-light w-100 d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-book" aria-hidden="true"></i>
          View documentation
        </a>
      </div>-->
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>