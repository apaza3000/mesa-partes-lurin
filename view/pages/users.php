<!doctype html>
<html lang="en">
<!--begin::Head-->

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>AdminLTE 4 | Users</title>

  <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
  <script>
    (() => {
      'use strict';
      const root = document.documentElement;

      // Applications with their own theming opt out of AdminLTE's color mode
      // entirely, here as well as in the bundle.
      if (root.getAttribute('data-lte-color-mode') === 'off') {
        return;
      }

      const STORAGE_KEY = 'lte-theme';
      let stored = null;
      try {
        stored = localStorage.getItem(STORAGE_KEY);
      } catch {
        // localStorage may be unavailable (private mode, sandboxed iframe).
      }
      // Mirror the precedence in color-mode.ts: the visitor's stored choice
      // wins, then a theme this page declared itself, then the OS preference.
      const authored = root.getAttribute('data-bs-theme');
      let resolved = 'light';
      if (stored === 'dark' || stored === 'light') {
        resolved = stored;
      } else if (authored === 'dark' || authored === 'light') {
        resolved = authored;
      } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
        resolved = 'dark';
      }
      root.setAttribute('data-bs-theme', resolved);
      root.style.colorScheme = resolved;
      // Flag values computed here, so the bundle does not mistake them for a
      // theme the page declared and stop following the OS preference.
      if (resolved !== authored) {
        root.setAttribute('data-lte-theme-resolved', '');
      }
    })();
  </script>
  <!--end::Theme Init-->

  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <!--end::Accessibility Meta Tags-->

  <!--begin::Primary Meta Tags-->
  <meta name="title" content="AdminLTE 4 | Users" />
  <meta name="author" content="ColorlibHQ" />
  <meta name="description"
    content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind." />
  <meta name="keywords"
    content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel" />
  <!--end::Primary Meta Tags-->

  <!--begin::Accessibility Features-->
  <!-- Skip links will be dynamically added by accessibility.js -->
  <meta name="supported-color-schemes" content="light dark" />
  <link rel="preload" href="./../assets/css/admin-lte/adminlte.css" as="style" />
  <!--end::Accessibility Features-->

  <!--begin::Fonts-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print"
    onload="this.media = 'all'" />
  <!--end::Fonts-->

  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(OverlayScrollbars)-->

  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(Bootstrap Icons)-->

  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="./../assets/css/admin-lte/adminlte.css" />
  <!--end::Required Plugin(AdminLTE)-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <!--begin::App Wrapper-->
  <div class="app-wrapper">

    <?php
    require_once("./../includes/heder.php");
    require_once("./../includes/sidebar.php");
    ?>

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
                          <input type="search" id="user-search" class="form-control" placeholder="Search users"
                            aria-label="Search users" style="width: 180px" />
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


                <!--tablero de usuarios 👇-->
                <?php
                require_once("./../includes/renderizarUsuarios.php");
                ?>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label for="new-user-name" class="form-label"> Full name </label>
                      <input type="text" class="form-control" id="new-user-name" placeholder="e.g. Jane Doe" required />
                    </div>
                    <div class="mb-3">
                      <label for="new-user-email" class="form-label"> Email address </label>
                      <input type="email" class="form-control" id="new-user-email" placeholder="name@example.com"
                        required />
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
    <!--Footer👇-->
    <?php
    require_once("./../includes/footer.php");
    ?>

  </div>
  <!--end::App Wrapper-->
  <!--begin::Script-->
  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"></script>
  <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
  <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
  <script src="../assets/js/adminlte.js"></script>
  <!--end::Required Plugin(AdminLTE)-->
  <!--begin::OverlayScrollbars Configure-->
  <!-- AdminLTE versión clásica -->
  <script>
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
      scrollbarTheme: 'os-theme-light',
      scrollbarAutoHide: 'leave',
      scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function () {
      const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

      // Disable OverlayScrollbars on mobile devices to prevent touch interference
      const isMobile = window.innerWidth <= 992;

      if (
        sidebarWrapper &&
        OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
        !isMobile
      ) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
          scrollbars: {
            theme: Default.scrollbarTheme,
            autoHide: Default.scrollbarAutoHide,
            clickScroll: Default.scrollbarClickScroll,
          },
        });
      }
    });
  </script>

</body>
<!--end::Body-->

</html>