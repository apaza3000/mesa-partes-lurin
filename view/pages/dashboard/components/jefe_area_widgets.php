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
<!-- Main content -->
<main class="app-main">
    <section class="content">
        <div class="container-fluid">
            <!--begin::App Content Header-->
            <div class="app-content-header">
                <!--begin::Container-->
                <div class="container-fluid">
                    <!--begin::Row-->
                    <div class="row">
                        <div class="col-sm-6">
                            <h1 class="mb-0 fs-3"><i class="bi bi-house-door"></i> Dashboard</h1>
                        </div>
                        <div class="col-sm-6">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb float-sm-end">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Container-->
            </div>
            <!-- 1. BLOQUE DE BIENVENIDA INSTITUCIONAL -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible shadow-sm border-0 bg-gradient-info">
                        <h5><i class="icon fas fa-shield-alt"></i> ¡Bienvenido al Sistema de Gestión Documentaria,
                            <?= htmlspecialchars($usuario['nombres'] ?? 'Usuario') ?>!
                        </h5>
                        Estás conectado en el sistema del IESTP "Lurín" con el rol activo de:
                        <span class="badge badge-light px-2 py-1 text-uppercase font-weight-bold text-dark">
                            <?= htmlspecialchars($rolActual) ?>
                        </span>
                        Desde aquí podrás supervisar los accesos y el flujo de trámites de la institución.
                    </div>
                </div>
            </div>
            <!--end::App Content Header-->
            <!--begin::App Content-->
            <div class="app-content">
                <!--begin::Container-->
                <div class="container-fluid">
                    <!--begin::Row-->
                    <div class="row">
                        <!--
                ==================================================================
                SMALL BOXES
                ==================================================================
                -->
                        <!--small box 1-->
                        <div class="col-lg-3 col-6">

                            <div class="small-box text-bg-primary">
                                <div class="inner">
                                    <h3>---</h3>

                                    <p>Total documentos registrados</p>
                                </div>
                                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z">
                                    </path>
                                </svg>
                                <a href="#"
                                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                    More info <i class="bi bi-link-45deg"></i>
                                </a>
                            </div>
                        </div>
                        <!--small box 2-->
                        <div class="col-lg-3 col-6">
                            <div class="small-box text-bg-success">
                                <div class="inner">
                                    <h3>---</h3>

                                    <p>Bounce Rate</p>
                                </div>
                                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z">
                                    </path>
                                </svg>
                                <a href="#"
                                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                    More info <i class="bi bi-link-45deg"></i>
                                </a>
                            </div>
                        </div>
                        <!--small box 3-->
                        <div class="col-lg-3 col-6">
                            <div class="small-box text-bg-warning">
                                <div class="inner">
                                    <h3>---</h3>
                                    <p>expedientes en proceso</p>
                                </div>
                                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">

                                    <path
                                        d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z">
                                    </path>
                                </svg>
                                <a href="#"
                                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                    More info <i class="bi bi-link-45deg"></i>
                                </a>
                            </div>
                        </div>
                        <!--small box 4-->
                        <div class="col-lg-3 col-6">
                            <div class="small-box text-bg-danger">
                                <div class="inner">
                                    <h3>---</h3>
                                    <p>Documentos pendientes de atención general</p>
                                </div>
                                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z">
                                    </path>
                                    <path clip-rule="evenodd" fill-rule="evenodd"
                                        d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z">
                                    </path>
                                </svg>
                                <a href="#"
                                    class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                    More info <i class="bi bi-link-45deg"></i>
                                </a>
                            </div>
                            <!--end::Small Box Widget 4-->
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->
                    <!--begin::Row-->
                    <div class="row">
                        <!-- Start col -->
                        <div class="col-lg-7 connectedSortable">

                            <!-- DIRECT CHAT -->
                            <div class="card direct-chat direct-chat-primary mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Direct Chat</h3>

                                    <div class="card-tools">
                                        <span title="3 New Messages" class="badge text-bg-primary"> 3 </span>
                                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"
                                            aria-label="Collapse card">
                                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" title="Contacts"
                                            data-lte-toggle="chat-pane">
                                            <i class="bi bi-chat-text-fill"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"
                                            aria-label="Remove card">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <!-- Conversations are loaded here -->
                                    <div class="direct-chat-messages" role="log" tabindex="0"
                                        aria-label="Chat messages">
                                        <!-- Message. Default to the start -->
                                        <div class="direct-chat-msg">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-start"> Alexander Pierce </span>
                                                <span class="direct-chat-timestamp float-end"> 23 Jan 2:00 pm </span>
                                            </div>
                                            <!-- /.direct-chat-infos -->
                                            <img class="direct-chat-img" src="./assets/img/user1-128x128.jpg"
                                                alt="message user image" />
                                            <!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">
                                                Is this template really for free? That's unbelievable!
                                            </div>
                                            <!-- /.direct-chat-text -->
                                        </div>
                                        <!-- /.direct-chat-msg -->

                                        <!-- Message to the end -->
                                        <div class="direct-chat-msg end">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-end"> Sarah Bullock </span>
                                                <span class="direct-chat-timestamp float-start"> 23 Jan 2:05 pm </span>
                                            </div>
                                            <!-- /.direct-chat-infos -->
                                            <img class="direct-chat-img" src="./assets/img/user3-128x128.jpg"
                                                alt="message user image" />
                                            <!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">You better believe it!</div>
                                            <!-- /.direct-chat-text -->
                                        </div>
                                        <!-- /.direct-chat-msg -->

                                        <!-- Message. Default to the start -->
                                        <div class="direct-chat-msg">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-start"> Alexander Pierce </span>
                                                <span class="direct-chat-timestamp float-end"> 23 Jan 5:37 pm </span>
                                            </div>
                                            <!-- /.direct-chat-infos -->
                                            <img class="direct-chat-img" src="./assets/img/user1-128x128.jpg"
                                                alt="message user image" />
                                            <!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">
                                                Working with AdminLTE on a great new app! Wanna join?
                                            </div>
                                            <!-- /.direct-chat-text -->
                                        </div>
                                        <!-- /.direct-chat-msg -->

                                        <!-- Message to the end -->
                                        <div class="direct-chat-msg end">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-end"> Sarah Bullock </span>
                                                <span class="direct-chat-timestamp float-start"> 23 Jan 6:10 pm </span>
                                            </div>
                                            <!-- /.direct-chat-infos -->
                                            <img class="direct-chat-img" src="./assets/img/user3-128x128.jpg"
                                                alt="message user image" />
                                            <!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">I would love to.</div>
                                            <!-- /.direct-chat-text -->
                                        </div>
                                        <!-- /.direct-chat-msg -->
                                    </div>
                                    <!-- /.direct-chat-messages-->

                                    <!-- Contacts are loaded here -->
                                    <div class="direct-chat-contacts">
                                        <ul class="contacts-list">
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user1-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            Count Dracula
                                                            <small class="contacts-list-date float-end"> 2/28/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> How have you been? I was...
                                                        </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user7-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            Sarah Doe
                                                            <small class="contacts-list-date float-end"> 2/23/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> I will be waiting for...
                                                        </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user3-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            Nadia Jolie
                                                            <small class="contacts-list-date float-end"> 2/20/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> I'll call you back at...
                                                        </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user5-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            Nora S. Vans
                                                            <small class="contacts-list-date float-end"> 2/10/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> Where is your new... </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user6-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            John K.
                                                            <small class="contacts-list-date float-end"> 1/27/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> Can I take a look at... </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user8-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            Kenneth M.
                                                            <small class="contacts-list-date float-end"> 1/4/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> Never mind I found... </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                        </ul>
                                        <!-- /.contacts-list -->
                                    </div>
                                    <!-- /.direct-chat-pane -->
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer">
                                    <form action="#" method="post">
                                        <div class="input-group">
                                            <input type="text" name="message" placeholder="Type Message ..."
                                                class="form-control" />
                                            <span class="input-group-append">
                                                <button type="button" class="btn btn-primary">Send</button>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.card-footer-->
                            </div>
                            <!-- /.direct-chat -->
                        </div>
                        <!-- /.Start col -->

                        <!-- Start col -->
                        <div class="col-lg-5 connectedSortable">
                            <div class="card text-white bg-primary bg-gradient border-primary mb-4">
                                <div class="card-header border-0">
                                    <h3 class="card-title">Sales Value</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-primary btn-sm"
                                            data-lte-toggle="card-collapse" aria-label="Collapse card">
                                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="world-map" style="height: 220px"></div>
                                </div>
                                <div class="card-footer border-0">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <div class="col-4 text-center">
                                            <div class="position-relative" style="height: 50px">
                                                <canvas id="sparkline-1" role="img"
                                                    aria-label="Visitors trend"></canvas>
                                            </div>
                                            <div class="text-white">Visitors</div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="position-relative" style="height: 50px">
                                                <canvas id="sparkline-2" role="img" aria-label="Online trend"></canvas>
                                            </div>
                                            <div class="text-white">Online</div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="position-relative" style="height: 50px">
                                                <canvas id="sparkline-3" role="img" aria-label="Sales trend"></canvas>
                                            </div>
                                            <div class="text-white">Sales</div>
                                        </div>
                                    </div>
                                    <!--end::Row-->
                                </div>
                            </div>
                        </div>
                        <!-- /.Start col -->
                    </div>
                    <!-- /.row (main row) -->
                </div>
                <!--end::Container-->
            </div>
            <!--end::App Content-->
</main>
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Dashboard v2</h1>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard v2</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-primary shadow-sm">
                            <i class="bi bi-gear-fill"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">CPU Traffic</span>
                            <span class="info-box-number">
                                10
                                <small>%</small>
                            </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-danger shadow-sm">
                            <i class="bi bi-hand-thumbs-up-fill"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">Likes</span>
                            <span class="info-box-number">41,410</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->

                <!-- fix for small devices only -->
                <!-- <div class="clearfix hidden-md-up"></div> -->

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-success shadow-sm">
                            <i class="bi bi-cart-fill"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">Sales</span>
                            <span class="info-box-number">760</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-warning shadow-sm">
                            <i class="bi bi-people-fill"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">New Members</span>
                            <span class="info-box-number">2,000</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->

            <!--begin::Row-->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">Monthly Recap Report</h5>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"
                                    aria-label="Collapse card">
                                    <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-tool dropdown-toggle" aria-label="More options"
                                        data-bs-toggle="dropdown">
                                        <i class="bi bi-wrench"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" role="menu">
                                        <a href="#" class="dropdown-item">Action</a>
                                        <a href="#" class="dropdown-item">Another action</a>
                                        <a href="#" class="dropdown-item"> Something else here </a>
                                        <a class="dropdown-divider"></a>
                                        <a href="#" class="dropdown-item">Separated link</a>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"
                                    aria-label="Remove card">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <!--begin::Row-->
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="text-center">
                                        <strong>Sales: 1 Jan, 2023 - 30 Jul, 2023</strong>
                                    </p>

                                    <div class="position-relative" style="height: 180px">
                                        <canvas id="sales-chart" role="img"
                                            aria-label="Area chart of digital goods and electronics sales, January to July 2023"></canvas>
                                    </div>
                                </div>
                                <!-- /.col -->
                                <div class="col-md-4">
                                    <p class="text-center">
                                        <strong>Goal Completion</strong>
                                    </p>

                                    <div class="progress-group">
                                        Add Products to Cart
                                        <span class="float-end"><b>160</b>/200</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar text-bg-primary" style="width: 80%"></div>
                                        </div>
                                    </div>
                                    <!-- /.progress-group -->

                                    <div class="progress-group">
                                        Complete Purchase
                                        <span class="float-end"><b>310</b>/400</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar text-bg-danger" style="width: 75%"></div>
                                        </div>
                                    </div>

                                    <!-- /.progress-group -->
                                    <div class="progress-group">
                                        <span class="progress-text">Visit Premium Page</span>
                                        <span class="float-end"><b>480</b>/800</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar text-bg-success" style="width: 60%"></div>
                                        </div>
                                    </div>

                                    <!-- /.progress-group -->
                                    <div class="progress-group">
                                        Send Inquiries
                                        <span class="float-end"><b>250</b>/500</span>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar text-bg-warning" style="width: 50%"></div>
                                        </div>
                                    </div>
                                    <!-- /.progress-group -->
                                </div>
                                <!-- /.col -->
                            </div>
                            <!--end::Row-->
                        </div>
                        <!-- ./card-body -->
                        <div class="card-footer">
                            <!--begin::Row-->
                            <div class="row">
                                <div class="col-md-3 col-6">
                                    <div class="text-center border-end">
                                        <span class="text-success">
                                            <i class="bi bi-caret-up-fill"></i> 17%
                                        </span>
                                        <h5 class="fw-bold mb-0">$35,210.43</h5>
                                        <span class="text-uppercase">TOTAL REVENUE</span>
                                    </div>
                                </div>

                                <!-- /.col -->
                                <div class="col-md-3 col-6">
                                    <div class="text-center border-end">
                                        <span class="text-info"> <i class="bi bi-caret-left-fill"></i> 0% </span>
                                        <h5 class="fw-bold mb-0">$10,390.90</h5>
                                        <span class="text-uppercase">TOTAL COST</span>
                                    </div>
                                </div>
                                <!-- /.col -->
                                <div class="col-md-3 col-6">
                                    <div class="text-center border-end">
                                        <span class="text-success">
                                            <i class="bi bi-caret-up-fill"></i> 20%
                                        </span>
                                        <h5 class="fw-bold mb-0">$24,813.53</h5>
                                        <span class="text-uppercase">TOTAL PROFIT</span>
                                    </div>
                                </div>
                                <!-- /.col -->
                                <div class="col-md-3 col-6">
                                    <div class="text-center">
                                        <span class="text-danger">
                                            <i class="bi bi-caret-down-fill"></i> 18%
                                        </span>
                                        <h5 class="fw-bold mb-0">1200</h5>
                                        <span class="text-uppercase">GOAL COMPLETIONS</span>
                                    </div>
                                </div>
                            </div>
                            <!--end::Row-->
                        </div>
                        <!-- /.card-footer -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row">
                <!-- Start col -->
                <div class="col-md-8">
                    <!--begin::Row-->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <!-- DIRECT CHAT -->
                            <div class="card direct-chat direct-chat-warning">
                                <div class="card-header">
                                    <h3 class="card-title">Direct Chat</h3>

                                    <div class="card-tools">
                                        <span title="3 New Messages" class="badge text-bg-warning"> 3 </span>
                                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"
                                            aria-label="Collapse card">
                                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" title="Contacts"
                                            data-lte-toggle="chat-pane">
                                            <i class="bi bi-chat-text-fill"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"
                                            aria-label="Remove card">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <!-- Conversations are loaded here -->
                                    <div class="direct-chat-messages" role="log" tabindex="0"
                                        aria-label="Chat messages">
                                        <!-- Message. Default to the start -->
                                        <div class="direct-chat-msg">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-start"> Alexander Pierce </span>
                                                <span class="direct-chat-timestamp float-end"> 23 Jan 2:00 pm </span>
                                            </div>
                                            <!-- /.direct-chat-infos -->
                                            <img class="direct-chat-img" src="./assets/img/user1-128x128.jpg"
                                                alt="message user image" />
                                            <!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">
                                                Is this template really for free? That's unbelievable!
                                            </div>
                                            <!-- /.direct-chat-text -->
                                        </div>
                                        <!-- /.direct-chat-msg -->

                                        <!-- Message to the end -->
                                        <div class="direct-chat-msg end">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-end"> Sarah Bullock </span>
                                                <span class="direct-chat-timestamp float-start">
                                                    23 Jan 2:05 pm
                                                </span>
                                            </div>
                                            <!-- /.direct-chat-infos -->
                                            <img class="direct-chat-img" src="./assets/img/user3-128x128.jpg"
                                                alt="message user image" />
                                            <!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">You better believe it!</div>
                                            <!-- /.direct-chat-text -->
                                        </div>
                                        <!-- /.direct-chat-msg -->

                                        <!-- Message. Default to the start -->
                                        <div class="direct-chat-msg">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-start"> Alexander Pierce </span>
                                                <span class="direct-chat-timestamp float-end"> 23 Jan 5:37 pm </span>
                                            </div>
                                            <!-- /.direct-chat-infos -->
                                            <img class="direct-chat-img" src="./assets/img/user1-128x128.jpg"
                                                alt="message user image" />
                                            <!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">
                                                Working with AdminLTE on a great new app! Wanna join?
                                            </div>
                                            <!-- /.direct-chat-text -->
                                        </div>
                                        <!-- /.direct-chat-msg -->

                                        <!-- Message to the end -->
                                        <div class="direct-chat-msg end">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-end"> Sarah Bullock </span>
                                                <span class="direct-chat-timestamp float-start">
                                                    23 Jan 6:10 pm
                                                </span>
                                            </div>
                                            <!-- /.direct-chat-infos -->
                                            <img class="direct-chat-img" src="./assets/img/user3-128x128.jpg"
                                                alt="message user image" />
                                            <!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">I would love to.</div>
                                            <!-- /.direct-chat-text -->
                                        </div>
                                        <!-- /.direct-chat-msg -->
                                    </div>
                                    <!-- /.direct-chat-messages-->

                                    <!-- Contacts are loaded here -->
                                    <div class="direct-chat-contacts">
                                        <ul class="contacts-list">
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user1-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            Count Dracula
                                                            <small class="contacts-list-date float-end"> 2/28/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg">
                                                            How have you been? I was...
                                                        </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user7-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            Sarah Doe
                                                            <small class="contacts-list-date float-end"> 2/23/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> I will be waiting for...
                                                        </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user3-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            Nadia Jolie
                                                            <small class="contacts-list-date float-end"> 2/20/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> I'll call you back at...
                                                        </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user5-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            Nora S. Vans
                                                            <small class="contacts-list-date float-end"> 2/10/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> Where is your new... </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user6-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            John K.
                                                            <small class="contacts-list-date float-end"> 1/27/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> Can I take a look at... </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                            <li>
                                                <a href="#">
                                                    <img class="contacts-list-img" src="./assets/img/user8-128x128.jpg"
                                                        alt="User Avatar" />

                                                    <div class="contacts-list-info">
                                                        <span class="contacts-list-name">
                                                            Kenneth M.
                                                            <small class="contacts-list-date float-end"> 1/4/2023
                                                            </small>
                                                        </span>
                                                        <span class="contacts-list-msg"> Never mind I found... </span>
                                                    </div>
                                                    <!-- /.contacts-list-info -->
                                                </a>
                                            </li>
                                            <!-- End Contact Item -->
                                        </ul>
                                        <!-- /.contacts-list -->
                                    </div>
                                    <!-- /.direct-chat-pane -->
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer">
                                    <form action="#" method="post">
                                        <div class="input-group">
                                            <input type="text" name="message" placeholder="Type Message ..."
                                                class="form-control" />
                                            <span class="input-group-append">
                                                <button type="button" class="btn btn-warning">Send</button>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.card-footer-->
                            </div>
                            <!-- /.direct-chat -->
                        </div>
                        <!-- /.col -->

                        <div class="col-md-6">
                            <!-- USERS LIST -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Latest Members</h3>

                                    <div class="card-tools">
                                        <span class="badge text-bg-danger"> 8 New Members </span>
                                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"
                                            aria-label="Collapse card">
                                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"
                                            aria-label="Remove card">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                    <div class="row text-center m-1">
                                        <div class="col-3 p-2">
                                            <img class="img-fluid rounded-circle" src="./assets/img/user1-128x128.jpg"
                                                alt="User Image" />
                                            <a class="btn fw-bold fs-7 text-secondary text-truncate w-100 p-0" href="#">
                                                Alexander Pierce
                                            </a>
                                            <div class="fs-8">Today</div>
                                        </div>
                                        <div class="col-3 p-2">
                                            <img class="img-fluid rounded-circle" src="./assets/img/user1-128x128.jpg"
                                                alt="User Image" />
                                            <a class="btn fw-bold fs-7 text-secondary text-truncate w-100 p-0" href="#">
                                                Norman
                                            </a>
                                            <div class="fs-8">Yesterday</div>
                                        </div>
                                        <div class="col-3 p-2">
                                            <img class="img-fluid rounded-circle" src="./assets/img/user7-128x128.jpg"
                                                alt="User Image" />
                                            <a class="btn fw-bold fs-7 text-secondary text-truncate w-100 p-0" href="#">
                                                Jane
                                            </a>
                                            <div class="fs-8">12 Jan</div>
                                        </div>
                                        <div class="col-3 p-2">
                                            <img class="img-fluid rounded-circle" src="./assets/img/user6-128x128.jpg"
                                                alt="User Image" />
                                            <a class="btn fw-bold fs-7 text-secondary text-truncate w-100 p-0" href="#">
                                                John
                                            </a>
                                            <div class="fs-8">12 Jan</div>
                                        </div>
                                        <div class="col-3 p-2">
                                            <img class="img-fluid rounded-circle" src="./assets/img/user2-160x160.jpg"
                                                alt="User Image" />
                                            <a class="btn fw-bold fs-7 text-secondary text-truncate w-100 p-0" href="#">
                                                Alexander
                                            </a>
                                            <div class="fs-8">13 Jan</div>
                                        </div>
                                        <div class="col-3 p-2">
                                            <img class="img-fluid rounded-circle" src="./assets/img/user5-128x128.jpg"
                                                alt="User Image" />
                                            <a class="btn fw-bold fs-7 text-secondary text-truncate w-100 p-0" href="#">
                                                Sarah
                                            </a>
                                            <div class="fs-8">14 Jan</div>
                                        </div>
                                        <div class="col-3 p-2">
                                            <img class="img-fluid rounded-circle" src="./assets/img/user4-128x128.jpg"
                                                alt="User Image" />
                                            <a class="btn fw-bold fs-7 text-secondary text-truncate w-100 p-0" href="#">
                                                Nora
                                            </a>
                                            <div class="fs-8">15 Jan</div>
                                        </div>
                                        <div class="col-3 p-2">
                                            <img class="img-fluid rounded-circle" src="./assets/img/user3-128x128.jpg"
                                                alt="User Image" />
                                            <a class="btn fw-bold fs-7 text-secondary text-truncate w-100 p-0" href="#">
                                                Nadia
                                            </a>
                                            <div class="fs-8">15 Jan</div>
                                        </div>
                                    </div>
                                    <!-- /.users-list -->
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer text-center">
                                    <a href="javascript:"
                                        class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">View
                                        All Users</a>
                                </div>
                                <!-- /.card-footer -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <!--end::Row-->

                    <!--begin::Latest Order Widget-->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Latest Orders</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"
                                    aria-label="Collapse card">
                                    <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"
                                    aria-label="Remove card">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Item</th>
                                            <th>Status</th>
                                            <th>Popularity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <a href="pages/invoice.html"
                                                    class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">OR9842</a>
                                            </td>
                                            <td>Call of Duty IV</td>
                                            <td>
                                                <span class="badge text-bg-success"> Shipped </span>
                                            </td>
                                            <td>
                                                <div class="position-relative" style="width: 150px; height: 30px">
                                                    <canvas id="table-sparkline-1" role="img"
                                                        aria-label="Popularity trend"></canvas>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="pages/invoice.html"
                                                    class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">OR1848</a>
                                            </td>
                                            <td>Samsung Smart TV</td>
                                            <td>
                                                <span class="badge text-bg-warning">Pending</span>
                                            </td>
                                            <td>
                                                <div class="position-relative" style="width: 150px; height: 30px">
                                                    <canvas id="table-sparkline-2" role="img"
                                                        aria-label="Popularity trend"></canvas>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="pages/invoice.html"
                                                    class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">OR7429</a>
                                            </td>
                                            <td>iPhone 6 Plus</td>
                                            <td>
                                                <span class="badge text-bg-danger"> Delivered </span>
                                            </td>
                                            <td>
                                                <div class="position-relative" style="width: 150px; height: 30px">
                                                    <canvas id="table-sparkline-3" role="img"
                                                        aria-label="Popularity trend"></canvas>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="pages/invoice.html"
                                                    class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">OR7429</a>
                                            </td>
                                            <td>Samsung Smart TV</td>
                                            <td>
                                                <span class="badge text-bg-info">Processing</span>
                                            </td>
                                            <td>
                                                <div class="position-relative" style="width: 150px; height: 30px">
                                                    <canvas id="table-sparkline-4" role="img"
                                                        aria-label="Popularity trend"></canvas>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="pages/invoice.html"
                                                    class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">OR1848</a>
                                            </td>
                                            <td>Samsung Smart TV</td>
                                            <td>
                                                <span class="badge text-bg-warning">Pending</span>
                                            </td>
                                            <td>
                                                <div class="position-relative" style="width: 150px; height: 30px">
                                                    <canvas id="table-sparkline-5" role="img"
                                                        aria-label="Popularity trend"></canvas>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="pages/invoice.html"
                                                    class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">OR7429</a>
                                            </td>
                                            <td>iPhone 6 Plus</td>
                                            <td>
                                                <span class="badge text-bg-danger"> Delivered </span>
                                            </td>
                                            <td>
                                                <div class="position-relative" style="width: 150px; height: 30px">
                                                    <canvas id="table-sparkline-6" role="img"
                                                        aria-label="Popularity trend"></canvas>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="pages/invoice.html"
                                                    class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">OR9842</a>
                                            </td>
                                            <td>Call of Duty IV</td>
                                            <td>
                                                <span class="badge text-bg-success">Shipped</span>
                                            </td>
                                            <td>
                                                <div class="position-relative" style="width: 150px; height: 30px">
                                                    <canvas id="table-sparkline-7" role="img"
                                                        aria-label="Popularity trend"></canvas>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer clearfix">
                            <a href="javascript:void(0)" class="btn btn-sm btn-primary float-start">
                                Place New Order
                            </a>
                            <a href="javascript:void(0)" class="btn btn-sm btn-secondary float-end">
                                View All Orders
                            </a>
                        </div>
                        <!-- /.card-footer -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->

                <div class="col-md-4">
                    <!-- Info Boxes Style 2 -->
                    <div class="info-box mb-3 text-bg-warning">
                        <span class="info-box-icon">
                            <i class="bi bi-tag-fill"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">Inventory</span>
                            <span class="info-box-number">5,200</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                    <div class="info-box mb-3 text-bg-success">
                        <span class="info-box-icon">
                            <i class="bi bi-heart-fill"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">Mentions</span>
                            <span class="info-box-number">92,050</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                    <div class="info-box mb-3 text-bg-danger">
                        <span class="info-box-icon">
                            <i class="bi bi-cloud-download"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">Downloads</span>
                            <span class="info-box-number">114,381</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                    <div class="info-box mb-3 text-bg-info">
                        <span class="info-box-icon">
                            <i class="bi bi-chat-fill"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">Direct Messages</span>
                            <span class="info-box-number">163,921</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->

                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Browser Usage</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"
                                    aria-label="Collapse card">
                                    <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"
                                    aria-label="Remove card">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <!--begin::Row-->
                            <div class="row">
                                <div class="col-12">
                                    <div class="position-relative" style="height: 350px">
                                        <canvas id="pie-chart" role="img"
                                            aria-label="Donut chart of browser usage"></canvas>
                                    </div>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!--end::Row-->
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        United States of America
                                        <span class="float-end text-danger">
                                            <i class="bi bi-arrow-down fs-7"></i>
                                            12%
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        India
                                        <span class="float-end text-success">
                                            <i class="bi bi-arrow-up fs-7"></i> 4%
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        China
                                        <span class="float-end text-info">
                                            <i class="bi bi-arrow-left fs-7"></i> 0%
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- /.footer -->
                    </div>
                    <!-- /.card -->

                    <!-- PRODUCT LIST -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recently Added Products</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"
                                    aria-label="Collapse card">
                                    <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"
                                    aria-label="Remove card">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body p-0">
                            <div class="px-2">
                                <div class="d-flex border-top py-2 px-1">
                                    <div class="col-2">
                                        <img src="./assets/img/default-150x150.png" alt="Product Image"
                                            class="img-size-50" />
                                    </div>
                                    <div class="col-10">
                                        <a href="javascript:void(0)" class="fw-bold">
                                            Samsung TV
                                            <span class="badge text-bg-warning float-end"> $1800 </span>
                                        </a>
                                        <div class="text-truncate">Samsung 32" 1080p 60Hz LED Smart HDTV.</div>
                                    </div>
                                </div>
                                <!-- /.item -->
                                <div class="d-flex border-top py-2 px-1">
                                    <div class="col-2">
                                        <img src="./assets/img/default-150x150.png" alt="Product Image"
                                            class="img-size-50" />
                                    </div>
                                    <div class="col-10">
                                        <a href="javascript:void(0)" class="fw-bold">
                                            Bicycle
                                            <span class="badge text-bg-info float-end"> $700 </span>
                                        </a>
                                        <div class="text-truncate">
                                            26" Mongoose Dolomite Men's 7-speed, Navy Blue.
                                        </div>
                                    </div>
                                </div>
                                <!-- /.item -->
                                <div class="d-flex border-top py-2 px-1">
                                    <div class="col-2">
                                        <img src="./assets/img/default-150x150.png" alt="Product Image"
                                            class="img-size-50" />
                                    </div>
                                    <div class="col-10">
                                        <a href="javascript:void(0)" class="fw-bold">
                                            Xbox One
                                            <span class="badge text-bg-danger float-end"> $350 </span>
                                        </a>
                                        <div class="text-truncate">
                                            Xbox One Console Bundle with Halo Master Chief Collection.
                                        </div>
                                    </div>
                                </div>
                                <!-- /.item -->
                                <div class="d-flex border-top py-2 px-1">
                                    <div class="col-2">
                                        <img src="./assets/img/default-150x150.png" alt="Product Image"
                                            class="img-size-50" />
                                    </div>
                                    <div class="col-10">
                                        <a href="javascript:void(0)" class="fw-bold">
                                            PlayStation 4
                                            <span class="badge text-bg-success float-end"> $399 </span>
                                        </a>
                                        <div class="text-truncate">PlayStation 4 500GB Console (PS4)</div>
                                    </div>
                                </div>
                                <!-- /.item -->
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" class="uppercase"> View All Products </a>
                        </div>
                        <!-- /.card-footer -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
</main>

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"></script>
<!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
<!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
<script src="/assets/js/adminlte.js"></script>
<!--end::Required Plugin(AdminLTE)-->
<!--begin::OverlayScrollbars Configure-->
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
<!--end::OverlayScrollbars Configure-->

<!--begin::Color Mode Toggle-->
<!-- The light/dark/auto switcher ships in adminlte.js as the ColorMode
     module (since 4.1) — no page script needed. Only the no-flash snippet
     in <head> stays inline, because it must run before first paint. -->
<!--end::Color Mode Toggle-->

<!-- OPTIONAL SCRIPTS -->

<!-- Chart.js + AdminLTE chart theme -->
<!--begin::Third Party Plugin(Chart.js)-->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"
    integrity="sha256-SERKgtTty1vsDxll+qzd4Y2cF9swY9BCq62i9wXJ9Uo=" crossorigin="anonymous"></script>
<!--end::Third Party Plugin(Chart.js)-->
<!--begin::Chart.js theme preset-->
<script>
    // Styles every Chart.js chart on the page from AdminLTE's CSS variables —
    // font, text, gridlines, tooltip and legend — and keeps them in step with the
    // colour mode (ColorMode, the OS in auto mode, or your own code) and with
    // `dir="rtl"`. Canvas can't read CSS, so the values are resolved here and
    // re-applied to every chart whenever <html data-bs-theme> or dir changes.
    (() => {
        'use strict';
        const { Chart } = globalThis;
        if (!Chart) {
            return;
        }

        const root = document.documentElement;
        const cssVar = (name, fallback = '') =>
            getComputedStyle(root).getPropertyValue(name).trim() || fallback;

        // "#0d6efd" (or "#06f") + 0.3 -> "rgba(13, 110, 253, 0.3)"; anything else is returned as is.
        const alpha = (color, opacity) => {
            let hex = String(color).trim().replace(/^#/, '');
            if (!/^[\da-f]{3}([\da-f]{3})?$/i.test(hex)) {
                return color;
            }

            if (hex.length === 3) {
                hex = [...hex].map((c) => c + c).join('');
            }

            const [r, g, b] = [0, 2, 4].map((i) => Number.parseInt(hex.slice(i, i + 2), 16));
            return `rgba(${r}, ${g}, ${b}, ${opacity})`;
        };

        // Scriptable backgroundColor for area charts: the line colour fading to transparent.
        const areaFill =
            (color, from = 0.35, to = 0) =>
                (context) => {
                    const { ctx, chartArea } = context.chart;
                    if (!chartArea) {
                        return alpha(color, from);
                    }

                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, alpha(color, from));
                    gradient.addColorStop(1, alpha(color, to));
                    return gradient;
                };

        // The theme colours currently in Chart.defaults.
        let applied = [];

        const apply = () => {
            const text = cssVar('--bs-secondary-color', '#6c757d');
            const body = cssVar('--bs-body-color', '#212529');
            const surface = cssVar('--bs-body-bg', '#fff');
            const grid = `rgba(${cssVar('--bs-emphasis-color-rgb', '0, 0, 0')}, 0.08)`;
            const rtl = root.getAttribute('dir') === 'rtl';
            const d = Chart.defaults;
            applied = [text, grid];

            d.font.family = cssVar('--bs-body-font-family', d.font.family);
            d.font.size = 12;
            d.color = text;
            d.borderColor = grid;
            d.maintainAspectRatio = false;

            d.scale.grid.color = grid;
            d.scale.border.color = grid;
            d.scale.ticks.padding = 8;
            // Value axes float, category axes keep only their baseline.
            d.set('scales.linear', { border: { display: false } });
            d.set('scales.category', { grid: { display: false } });

            d.elements.line.borderWidth = 2;
            d.elements.point.radius = 0;
            d.elements.point.hoverRadius = 5;
            d.elements.point.hitRadius = 8;
            d.elements.point.hoverBorderWidth = 2;
            d.elements.point.borderColor = surface;
            d.elements.bar.borderRadius = 4;
            d.elements.arc.borderColor = surface;
            d.elements.arc.borderWidth = 2;

            const { legend, tooltip } = d.plugins;
            legend.position = 'bottom';
            legend.rtl = rtl;
            Object.assign(legend.labels, {
                color: body,
                usePointStyle: true,
                pointStyle: 'circle',
                boxWidth: 8,
                boxHeight: 8,
                padding: 16,
            });

            Object.assign(tooltip, {
                rtl,
                backgroundColor: surface,
                borderColor: cssVar('--bs-border-color', '#dee2e6'),
                borderWidth: 1,
                titleColor: cssVar('--bs-emphasis-color', '#000'),
                bodyColor: body,
                titleFont: { weight: '600' },
                padding: 10,
                cornerRadius: 6,
                caretSize: 5,
                boxPadding: 4,
                usePointStyle: true,
            });

            // Line and bar charts share one tooltip per x value, like a dashboard should.
            for (const type of ['line', 'bar']) {
                Chart.overrides[type].interaction = { mode: 'index', intersect: false };
            }
        };

        // Chart.js copies the scale defaults (tick, grid, border and title colours)
        // into each chart's config when the chart is created, so new defaults alone
        // never reach an existing chart's axes. Drop the copies that still hold the
        // previous theme's colours; update() then copies the new ones in. Colours a
        // chart set for itself are left alone.
        const forgetThemeColors = (options, stale) => {
            for (const [key, value] of Object.entries(options)) {
                if (value && typeof value === 'object' && !Array.isArray(value)) {
                    forgetThemeColors(value, stale);
                } else if (/color$/i.test(key) && stale.includes(value)) {
                    delete options[key];
                }
            }
        };

        const refresh = () => {
            const stale = applied;
            apply();
            for (const chart of Object.values(Chart.instances)) {
                forgetThemeColors(chart.config.options.scales || {}, stale);
                chart.update('none');
            }
        };

        apply();
        new MutationObserver(refresh).observe(root, {
            attributes: true,
            attributeFilter: ['data-bs-theme', 'dir'],
        });
        // The web font may arrive after the first draw; redraw so labels use it.
        document.fonts?.addEventListener('loadingdone', refresh);

        // Helpers for the page's own chart code.
        // eslint-disable-next-line unicorn/no-global-object-property-assignment
        globalThis.lteChartTheme = { cssVar, alpha, areaFill, refresh };
    })();
</script>
<!--end::Chart.js theme preset-->

<script>
    // NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
    // IT'S ALL JUST JUNK FOR DEMO
    // ++++++++++++++++++++++++++++++++++++++++++

    /* Chart.js
     * --------
     * Here we will create a few charts using Chart.js
     */

    const { cssVar, areaFill } = globalThis.lteChartTheme;

    //-----------------------
    // - MONTHLY SALES CHART -
    //-----------------------

    new Chart(document.querySelector('#sales-chart'), {
        type: 'line',
        data: {
            labels: ["Jan '23", "Feb '23", "Mar '23", "Apr '23", "May '23", "Jun '23", "Jul '23"],
            datasets: [
                {
                    label: 'Digital Goods',
                    data: [28, 48, 40, 19, 86, 27, 90],
                    borderColor: '#0d6efd',
                    backgroundColor: areaFill('#0d6efd'),
                    pointBackgroundColor: '#0d6efd',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Electronics',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    borderColor: '#20c997',
                    backgroundColor: areaFill('#20c997'),
                    pointBackgroundColor: '#20c997',
                    fill: true,
                    tension: 0.4,
                },
            ],
        },
        options: {
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        title: ([item]) =>
                            `${new Date(2023, item.dataIndex).toLocaleString('en', { month: 'long' })} 2023`,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });

    //---------------------------
    // - END MONTHLY SALES CHART -
    //---------------------------

    function createSparklineChart(selector, data) {
        const primary = cssVar('--bs-primary', '#0d6efd');

        return new Chart(document.querySelector(selector), {
            type: 'line',
            data: {
                labels: data.map((value, index) => index + 1),
                datasets: [
                    {
                        data,
                        borderColor: primary,
                        backgroundColor: primary,
                        pointHoverRadius: 3,
                    },
                ],
            },
            options: {
                layout: {
                    padding: 3,
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        displayColors: false,
                        padding: 6,
                        callbacks: {
                            title: () => '',
                        },
                    },
                },
                scales: {
                    x: {
                        display: false,
                    },
                    y: {
                        display: false,
                    },
                },
            },
        });
    }

    const table_sparkline_1_data = [25, 66, 41, 89, 63, 25, 44, 12, 36, 9, 54];
    const table_sparkline_2_data = [12, 56, 21, 39, 73, 45, 64, 52, 36, 59, 44];
    const table_sparkline_3_data = [15, 46, 21, 59, 33, 15, 34, 42, 56, 19, 64];
    const table_sparkline_4_data = [30, 56, 31, 69, 43, 35, 24, 32, 46, 29, 64];
    const table_sparkline_5_data = [20, 76, 51, 79, 53, 35, 54, 22, 36, 49, 64];
    const table_sparkline_6_data = [5, 36, 11, 69, 23, 15, 14, 42, 26, 19, 44];
    const table_sparkline_7_data = [12, 56, 21, 39, 73, 45, 64, 52, 36, 59, 74];

    createSparklineChart('#table-sparkline-1', table_sparkline_1_data);
    createSparklineChart('#table-sparkline-2', table_sparkline_2_data);
    createSparklineChart('#table-sparkline-3', table_sparkline_3_data);
    createSparklineChart('#table-sparkline-4', table_sparkline_4_data);
    createSparklineChart('#table-sparkline-5', table_sparkline_5_data);
    createSparklineChart('#table-sparkline-6', table_sparkline_6_data);
    createSparklineChart('#table-sparkline-7', table_sparkline_7_data);

    //-------------
    // - PIE CHART -
    //-------------

    new Chart(document.querySelector('#pie-chart'), {
        type: 'doughnut',
        data: {
            labels: ['Chrome', 'Edge', 'FireFox', 'Safari', 'Opera', 'IE'],
            datasets: [
                {
                    label: 'Users',
                    data: [700, 500, 400, 600, 300, 100],
                    backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#d63384', '#6f42c1', '#adb5bd'],
                },
            ],
        },
        options: {
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'right',
                },
            },
        },
    });

    //-----------------
    // - END PIE CHART -
    //-----------------


</script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"></script>
<!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
<!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
<script src="/assets/js/adminlte.js"></script>
<!--end::Required Plugin(AdminLTE)-->
<!--begin::OverlayScrollbars Configure-->
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
<!--end::OverlayScrollbars Configure-->

<!--begin::Color Mode Toggle-->
<!-- The light/dark/auto switcher ships in adminlte.js as the ColorMode
     module (since 4.1) — no page script needed. Only the no-flash snippet
     in <head> stays inline, because it must run before first paint. -->
<!--end::Color Mode Toggle-->

<!-- OPTIONAL SCRIPTS -->

<!-- sortablejs -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" crossorigin="anonymous"></script>
<!-- sortablejs -->
<script>
    new Sortable(document.querySelector('.connectedSortable'), {
        group: 'shared',
        handle: '.card-header',
    });

    const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
    cardHeaders.forEach((cardHeader) => {
        cardHeader.style.cursor = 'move';
    });
</script>
<!-- Chart.js + AdminLTE chart theme -->
<!--begin::Third Party Plugin(Chart.js)-->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"
    integrity="sha256-SERKgtTty1vsDxll+qzd4Y2cF9swY9BCq62i9wXJ9Uo=" crossorigin="anonymous"></script>
<!--end::Third Party Plugin(Chart.js)-->
<!--begin::Chart.js theme preset-->
<script>
    // Styles every Chart.js chart on the page from AdminLTE's CSS variables —
    // font, text, gridlines, tooltip and legend — and keeps them in step with the
    // colour mode (ColorMode, the OS in auto mode, or your own code) and with
    // `dir="rtl"`. Canvas can't read CSS, so the values are resolved here and
    // re-applied to every chart whenever <html data-bs-theme> or dir changes.
    (() => {
        'use strict';
        const { Chart } = globalThis;
        if (!Chart) {
            return;
        }

        const root = document.documentElement;
        const cssVar = (name, fallback = '') =>
            getComputedStyle(root).getPropertyValue(name).trim() || fallback;

        // "#0d6efd" (or "#06f") + 0.3 -> "rgba(13, 110, 253, 0.3)"; anything else is returned as is.
        const alpha = (color, opacity) => {
            let hex = String(color).trim().replace(/^#/, '');
            if (!/^[\da-f]{3}([\da-f]{3})?$/i.test(hex)) {
                return color;
            }

            if (hex.length === 3) {
                hex = [...hex].map((c) => c + c).join('');
            }

            const [r, g, b] = [0, 2, 4].map((i) => Number.parseInt(hex.slice(i, i + 2), 16));
            return `rgba(${r}, ${g}, ${b}, ${opacity})`;
        };

        // Scriptable backgroundColor for area charts: the line colour fading to transparent.
        const areaFill =
            (color, from = 0.35, to = 0) =>
                (context) => {
                    const { ctx, chartArea } = context.chart;
                    if (!chartArea) {
                        return alpha(color, from);
                    }

                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, alpha(color, from));
                    gradient.addColorStop(1, alpha(color, to));
                    return gradient;
                };

        // The theme colours currently in Chart.defaults.
        let applied = [];

        const apply = () => {
            const text = cssVar('--bs-secondary-color', '#6c757d');
            const body = cssVar('--bs-body-color', '#212529');
            const surface = cssVar('--bs-body-bg', '#fff');
            const grid = `rgba(${cssVar('--bs-emphasis-color-rgb', '0, 0, 0')}, 0.08)`;
            const rtl = root.getAttribute('dir') === 'rtl';
            const d = Chart.defaults;
            applied = [text, grid];

            d.font.family = cssVar('--bs-body-font-family', d.font.family);
            d.font.size = 12;
            d.color = text;
            d.borderColor = grid;
            d.maintainAspectRatio = false;

            d.scale.grid.color = grid;
            d.scale.border.color = grid;
            d.scale.ticks.padding = 8;
            // Value axes float, category axes keep only their baseline.
            d.set('scales.linear', { border: { display: false } });
            d.set('scales.category', { grid: { display: false } });

            d.elements.line.borderWidth = 2;
            d.elements.point.radius = 0;
            d.elements.point.hoverRadius = 5;
            d.elements.point.hitRadius = 8;
            d.elements.point.hoverBorderWidth = 2;
            d.elements.point.borderColor = surface;
            d.elements.bar.borderRadius = 4;
            d.elements.arc.borderColor = surface;
            d.elements.arc.borderWidth = 2;

            const { legend, tooltip } = d.plugins;
            legend.position = 'bottom';
            legend.rtl = rtl;
            Object.assign(legend.labels, {
                color: body,
                usePointStyle: true,
                pointStyle: 'circle',
                boxWidth: 8,
                boxHeight: 8,
                padding: 16,
            });

            Object.assign(tooltip, {
                rtl,
                backgroundColor: surface,
                borderColor: cssVar('--bs-border-color', '#dee2e6'),
                borderWidth: 1,
                titleColor: cssVar('--bs-emphasis-color', '#000'),
                bodyColor: body,
                titleFont: { weight: '600' },
                padding: 10,
                cornerRadius: 6,
                caretSize: 5,
                boxPadding: 4,
                usePointStyle: true,
            });

            // Line and bar charts share one tooltip per x value, like a dashboard should.
            for (const type of ['line', 'bar']) {
                Chart.overrides[type].interaction = { mode: 'index', intersect: false };
            }
        };

        // Chart.js copies the scale defaults (tick, grid, border and title colours)
        // into each chart's config when the chart is created, so new defaults alone
        // never reach an existing chart's axes. Drop the copies that still hold the
        // previous theme's colours; update() then copies the new ones in. Colours a
        // chart set for itself are left alone.
        const forgetThemeColors = (options, stale) => {
            for (const [key, value] of Object.entries(options)) {
                if (value && typeof value === 'object' && !Array.isArray(value)) {
                    forgetThemeColors(value, stale);
                } else if (/color$/i.test(key) && stale.includes(value)) {
                    delete options[key];
                }
            }
        };

        const refresh = () => {
            const stale = applied;
            apply();
            for (const chart of Object.values(Chart.instances)) {
                forgetThemeColors(chart.config.options.scales || {}, stale);
                chart.update('none');
            }
        };

        apply();
        new MutationObserver(refresh).observe(root, {
            attributes: true,
            attributeFilter: ['data-bs-theme', 'dir'],
        });
        // The web font may arrive after the first draw; redraw so labels use it.
        document.fonts?.addEventListener('loadingdone', refresh);

        // Helpers for the page's own chart code.
        // eslint-disable-next-line unicorn/no-global-object-property-assignment
        globalThis.lteChartTheme = { cssVar, alpha, areaFill, refresh };
    })();
</script>
<!--end::Chart.js theme preset-->

<!-- jsvectormap -->
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
    integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
    integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY=" crossorigin="anonymous"></script>
<!-- jsvectormap -->
<script>
    // World map by jsVectorMap
    new jsVectorMap({
        selector: '#world-map',
        map: 'world',
    });

    // Sparkline charts
    const sparkline = (selector, data) =>
        new Chart(document.querySelector(selector), {
            type: 'line',
            data: {
                labels: data.map((value, index) => index + 1),
                datasets: [
                    {
                        data,
                        borderColor: '#DCE6EC',
                        backgroundColor: 'rgba(220, 230, 236, 0.3)',
                        pointBackgroundColor: '#DCE6EC',
                        fill: true,
                    },
                ],
            },
            options: {
                layout: { padding: { top: 2 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        displayColors: false,
                        callbacks: { title: () => '' },
                    },
                },
                scales: {
                    x: { display: false },
                    y: { display: false, min: 0 },
                },
            },
        });

    sparkline('#sparkline-1', [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021]);
    sparkline('#sparkline-2', [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921]);
    sparkline('#sparkline-3', [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21]);
</script>
<!--end::Script-->