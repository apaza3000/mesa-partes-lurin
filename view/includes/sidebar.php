<?php
// Configuración de roles
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');
define('ROLE_VIEWER', 'viewer');

// Obtener rol del usuario (ejemplo)
$userRole = "admin";

// Función para verificar si el usuario tiene acceso a un item
function hasAccess($itemRoles, $userRole)
{
  if (empty($itemRoles)) {
    return true; // Si no se especifican roles, todos tienen acceso
  }
  return in_array($userRole, $itemRoles);
}

// Array de navegación con roles por item
$navigation = [
  [
    'title' => 'Dashboard',
    'icon' => 'bi bi-speedometer',
    'roles' => ['admin', 'user', 'viewer'],
    'children' => [
      ['title' => 'Dashboard v1', 'url' => './index.html', 'roles' => ['admin', 'user', 'viewer']],
      ['title' => 'Dashboard v2', 'url' => './index2.html', 'roles' => ['admin', 'user']],
      ['title' => 'Dashboard v3', 'url' => './index3.html', 'roles' => ['admin']],
    ]
  ],
  [
    'title' => 'Starter Page',
    'icon' => 'bi bi-file-earmark',
    'url' => './starter.html',
    'active' => true,
    'roles' => ['admin', 'user', 'viewer']
  ],
  [
    'title' => 'Theme Generate',
    'icon' => 'bi bi-palette',
    'url' => './generate/theme.html',
    'roles' => ['admin']
  ],
  [
    'title' => 'Widgets',
    'icon' => 'bi bi-box-seam-fill',
    'roles' => ['admin', 'user'],
    'children' => [
      ['title' => 'Small Box', 'url' => './widgets/small-box.html', 'roles' => ['admin', 'user']],
      ['title' => 'info Box', 'url' => './widgets/info-box.html', 'roles' => ['admin', 'user']],
      ['title' => 'Cards', 'url' => './widgets/cards.html', 'roles' => ['admin']],
      ['title' => 'Social &amp; Post', 'url' => './widgets/social.html', 'roles' => ['admin']],
    ]
  ],
  [
    'title' => 'Layout Options',
    'icon' => 'bi bi-clipboard-fill',
    'badge' => ['text' => '12', 'class' => 'text-bg-secondary'],
    'roles' => ['admin', 'user'],
    'children' => [
      ['title' => 'Default Sidebar', 'url' => './layout/unfixed-sidebar.html', 'roles' => ['admin', 'user']],
      ['title' => 'Fixed Sidebar', 'url' => './layout/fixed-sidebar.html', 'roles' => ['admin', 'user']],
      ['title' => 'Fixed Header', 'url' => './layout/fixed-header.html', 'roles' => ['admin']],
      ['title' => 'Fixed Footer', 'url' => './layout/fixed-footer.html', 'roles' => ['admin']],
      ['title' => 'Fixed Complete', 'url' => './layout/fixed-complete.html', 'roles' => ['admin']],
      ['title' => 'Layout <small>+ Custom Area </small>', 'url' => './layout/layout-custom-area.html', 'roles' => ['admin']],
      ['title' => 'Sidebar Mini', 'url' => './layout/sidebar-mini.html', 'roles' => ['admin', 'user']],
      ['title' => 'Sidebar Mini <small>+ Collapsed</small>', 'url' => './layout/collapsed-sidebar.html', 'roles' => ['admin']],
      ['title' => 'Sidebar Mini <small>+ Collapsed + No Hover</small>', 'url' => './layout/collapsed-sidebar-without-hover.html', 'roles' => ['admin']],
      ['title' => 'Sidebar Mini <small>+ Logo Switch</small>', 'url' => './layout/logo-switch.html', 'roles' => ['admin']],
      ['title' => 'Top Nav <small>+ No Sidebar</small>', 'url' => './layout/top-nav.html', 'roles' => ['admin', 'user']],
      ['title' => 'Layout RTL', 'url' => './layout/layout-rtl.html', 'roles' => ['admin']],
    ]
  ],
  [
    'title' => 'UI Elements',
    'icon' => 'bi bi-tree-fill',
    'roles' => ['admin', 'user', 'viewer'],
    'children' => [
      ['title' => 'General', 'url' => './UI/general.html', 'roles' => ['admin', 'user']],
      ['title' => 'Icons', 'url' => './UI/icons.html', 'roles' => ['admin', 'user']],
      ['title' => 'Timeline', 'url' => './UI/timeline.html', 'roles' => ['admin']],
      ['title' => 'Ribbons', 'url' => './UI/ribbons.html', 'roles' => ['admin']],
    ]
  ],
  [
    'title' => 'Mailbox',
    'icon' => 'bi bi-envelope',
    'roles' => ['admin', 'user'],
    'children' => [
      ['title' => 'Inbox', 'url' => './mailbox/inbox.html', 'roles' => ['admin', 'user']],
      ['title' => 'Read Message', 'url' => './mailbox/read.html', 'roles' => ['admin']],
      ['title' => 'Compose', 'url' => './mailbox/compose.html', 'roles' => ['admin']],
    ]
  ],
  [
    'title' => 'Forms',
    'icon' => 'bi bi-pencil-square',
    'roles' => ['admin', 'user'],
    'children' => [
      ['title' => 'Elements', 'url' => './forms/elements.html', 'roles' => ['admin', 'user']],
      ['title' => 'Layout', 'url' => './forms/layout.html', 'roles' => ['admin']],
      ['title' => 'Validation', 'url' => './forms/validation.html', 'roles' => ['admin']],
      ['title' => 'Wizard', 'url' => './forms/wizard.html', 'roles' => ['admin']],
      ['title' => 'Advanced Elements', 'url' => './forms/advanced.html', 'roles' => ['admin']],
      ['title' => 'Editors', 'url' => './forms/editors.html', 'roles' => ['admin']],
    ]
  ],
  [
    'title' => 'Tables',
    'icon' => 'bi bi-table',
    'roles' => ['admin', 'user'],
    'children' => [
      ['title' => 'Simple Tables', 'url' => './tables/simple.html', 'roles' => ['admin', 'user']],
      ['title' => 'Data Tables', 'url' => './tables/data.html', 'roles' => ['admin']],
    ]
  ],
  [
    'title' => 'Charts',
    'icon' => 'bi bi-graph-up',
    'roles' => ['admin', 'user'],
    'children' => [
      ['title' => 'ApexCharts', 'url' => './charts/apexcharts.html', 'roles' => ['admin', 'user']],
    ]
  ],
  // PAGES - Header
  [
    'header' => 'PAGES'
  ],
  [
    'title' => 'Pages',
    'icon' => 'bi bi-file-earmark-text',
    'roles' => ['admin', 'user'],
    'children' => [
      ['title' => 'Profile', 'url' => './pages/profile.html', 'roles' => ['admin', 'user']],
      ['title' => 'Settings', 'url' => './pages/settings.html', 'roles' => ['admin']],
      ['title' => 'Invoice', 'url' => './pages/invoice.html', 'roles' => ['admin', 'user']],
      ['title' => 'Calendar', 'url' => './pages/calendar.html', 'roles' => ['admin', 'user']],
      ['title' => 'Kanban', 'url' => './pages/kanban.html', 'roles' => ['admin']],
      ['title' => 'Chat', 'url' => './pages/chat.html', 'roles' => ['admin', 'user']],
      ['title' => 'File Manager', 'url' => './pages/file-manager.html', 'roles' => ['admin']],
      ['title' => 'Projects', 'url' => './pages/projects.html', 'roles' => ['admin', 'user']],
      ['title' => 'Gallery', 'url' => './pages/gallery.html', 'roles' => ['admin', 'user']],
      ['title' => 'Search Results', 'url' => './pages/search-results.html', 'roles' => ['admin', 'user']],
      ['title' => 'Pricing', 'url' => './pages/pricing.html', 'roles' => ['admin']],
      ['title' => 'FAQ', 'url' => './pages/faq.html', 'roles' => ['admin', 'user']],
      [
        'title' => 'Error',
        'roles' => ['admin', 'user'],
        'children' => [
          ['title' => '404', 'url' => './pages/404.html', 'roles' => ['admin', 'user']],
          ['title' => '500', 'url' => './pages/500.html', 'roles' => ['admin']],
          ['title' => 'Maintenance', 'url' => './pages/maintenance.html', 'roles' => ['admin']],
        ]
      ],
    ]
  ],
  [
    'title' => 'Users',
    'icon' => 'bi bi-people',
    'url' => './users.html',
    'roles' => ['admin'] // Solo admin puede ver Users
  ],
  // EXAMPLES - Header
  [
    'header' => 'EXAMPLES'
  ],
  [
    'title' => 'Auth',
    'icon' => 'bi bi-box-arrow-in-right',
    'roles' => ['admin', 'user'],
    'children' => [
      [
        'title' => 'Version 1',
        'roles' => ['admin', 'user'],
        'children' => [
          ['title' => 'Login', 'url' => './examples/login.html', 'roles' => ['admin', 'user', 'viewer']],
          ['title' => 'Register', 'url' => './examples/register.html', 'roles' => ['admin', 'user', 'viewer']],
          ['title' => 'Forgot Password', 'url' => './examples/forgot-password.html', 'roles' => ['admin', 'user', 'viewer']],
        ]
      ],
      [
        'title' => 'Version 2',
        'roles' => ['admin'],
        'children' => [
          ['title' => 'Login', 'url' => './examples/login-v2.html', 'roles' => ['admin']],
          ['title' => 'Register', 'url' => './examples/register-v2.html', 'roles' => ['admin']],
        ]
      ],
      ['title' => 'Lockscreen', 'url' => './examples/lockscreen.html', 'roles' => ['admin']],
    ]
  ],
  // MULTI LEVEL EXAMPLE - Header
  [
    'header' => 'MULTI LEVEL EXAMPLE'
  ],
  [
    'title' => 'Level 1',
    'icon' => 'bi bi-circle-fill',
    'roles' => ['admin', 'user', 'viewer']
  ],
  [
    'title' => 'Level 1',
    'icon' => 'bi bi-circle-fill',
    'roles' => ['admin', 'user'],
    'children' => [
      ['title' => 'Level 2', 'roles' => ['admin', 'user']],
      [
        'title' => 'Level 2',
        'roles' => ['admin'],
        'children' => [
          ['title' => 'Level 3', 'roles' => ['admin']],
          ['title' => 'Level 3', 'roles' => ['admin']],
          ['title' => 'Level 3', 'roles' => ['admin']],
        ]
      ],
      ['title' => 'Level 2', 'roles' => ['admin', 'user']],
    ]
  ],
  [
    'title' => 'Level 1',
    'icon' => 'bi bi-circle-fill',
    'roles' => ['admin', 'user', 'viewer']
  ],
  // LABELS - Header
  [
    'header' => 'LABELS'
  ],
  [
    'title' => 'Important',
    'icon' => 'bi bi-circle text-danger',
    'roles' => ['admin', 'user', 'viewer']
  ],
  [
    'title' => 'Warning',
    'icon' => 'bi bi-circle text-warning',
    'roles' => ['admin', 'user', 'viewer']
  ],
  [
    'title' => 'Informational',
    'icon' => 'bi bi-circle text-info',
    'roles' => ['admin', 'user', 'viewer']
  ],
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

      <!-- Docs CTA (bottom of sidebar) -->
      <div class="p-3 mt-3 border-top border-secondary border-opacity-25">
        <a href="./docs/introduction.html"
          class="btn btn-sm btn-outline-light w-100 d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-book" aria-hidden="true"></i>
          View documentation
        </a>
      </div>
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>