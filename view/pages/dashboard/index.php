<!-- Main content -->
<main class="app-main">
    <section class="content">
        <div class="container-fluid">
            <!--begin::App Content Header-->
            <div class="app-content-header">
                <div class="container-fluid">
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
                </div>
            </div>

            <!-- 1. BLOQUE DE BIENVENIDA INSTITUCIONAL -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info alert-dismissible shadow-sm border-0 bg-gradient-info">
                        <h5><i class="icon fas fa-shield-alt"></i> ¡Bienvenido,
                            <?= htmlspecialchars($usuario['nombres'] ?? 'Usuario') ?>!</h5>
                        Estás conectado con el rol: <span
                            class="badge badge-light px-2 py-1 text-uppercase font-weight-bold "><?= htmlspecialchars($rolActual) ?></span>
                        <br>
                        Área asignada:
                        <span class="badge badge-light px-2 py-1 font-weight-bold">
                            <?= isset($usuario['nombre_area']) && $usuario['nombre_area'] !== null ? htmlspecialchars($usuario['nombre_area']) : 'Sin área asignada (Acceso Global)' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. SMALL BOXES (TARJETAS ESTADÍSTICAS) -->
            <div class="container-fluid">
                <div class="row">

                    <!-- Small box 1: Total Documentos -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-primary">
                            <div class="inner">
                                <h3><?= htmlspecialchars($totalDocumentosRegistrados ?? "0") ?></h3>
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
                                Más info <i class="bi bi-link-45deg"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Small box 2: Documentos Pendientes -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-danger">
                            <div class="inner">
                                <h3><?= htmlspecialchars($expedientesRegistrados ?? "0") ?></h3>
                                <p>Expedientes pendientes en general</p>
                            </div>
                            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path
                                    d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z">
                                </path>
                            </svg>
                            <a href="#"
                                class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                Más info <i class="bi bi-link-45deg"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Small box 3: Expedientes en Proceso -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-warning">
                            <div class="inner">
                                <h3><?= htmlspecialchars($expedientesEnProceso ?? "0") ?></h3>
                                <p>Expedientes en proceso</p>
                            </div>
                            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path
                                    d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z">
                                </path>
                            </svg>
                            <a href="#"
                                class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                                Más info <i class="bi bi-link-45deg"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Small box 4: Trámites Atendidos / Archivados -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-success">
                            <div class="inner">
                                <h3><?= htmlspecialchars($tramitesAtendidos_Archivados ?? "0") ?></h3>
                                <p>Trámites Atendidos / Archivados</p>
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
                                Más info <i class="bi bi-link-45deg"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 3. GRÁFICO CIRCULAR (DONUT CHART) CONECTADO A LOS DATOS -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold"><i class="bi bi-pie-chart"></i> Distribución de
                                Estados de Expedientes</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"
                                    aria-label="Collapse card">
                                    <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                    <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="position-relative" style="height: 300px">
                                <canvas id="pie-chart" role="img"
                                    aria-label="Gráfico de distribución de expedientes"></canvas>
                            </div>
                        </div>
                        <div class="card-footer p-0">
                            <ul class="nav nav-pills flex-column p-2">
                                <li class="nav-item d-flex justify-content-between px-3 py-2 border-bottom">
                                    <span><i class="fas fa-circle text-danger mr-2"></i> Pendientes (Registrados)</span>
                                    <span
                                        class="font-weight-bold"><?= htmlspecialchars($expedientesRegistrados ?? 0) ?></span>
                                </li>
                                <li class="nav-item d-flex justify-content-between px-3 py-2 border-bottom">
                                    <span><i class="fas fa-circle text-warning mr-2"></i> En Proceso</span>
                                    <span
                                        class="font-weight-bold"><?= htmlspecialchars($expedientesEnProceso ?? 0) ?></span>
                                </li>
                                <li class="nav-item d-flex justify-content-between px-3 py-2">
                                    <span><i class="fas fa-circle text-success mr-2"></i> Atendidos / Archivados</span>
                                    <span
                                        class="font-weight-bold"><?= htmlspecialchars($tramitesAtendidos_Archivados ?? 0) ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 4. ACCESOS RÁPIDOS Y ESTADO DEL SISTEMA -->
                <div class="col-md-6">
                    <div class="card card-outline card-primary shadow-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                <i class="bi bi-lightning-charge text-warning"></i> Accesos Rápidos Frecuentes
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Selecciona una opción de acuerdo a tus funciones permitidas:</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="/documentos/crear"
                                        class="btn btn-app bg-light border shadow-sm w-100 py-3 text-left">
                                        <i class="bi bi-file-earmark-plus text-primary fa-2x mb-1"></i>
                                        <span class="font-weight-bold d-block text-dark">Registrar Trámite</span>
                                        <small class="text-muted">Nueva entrada en Mesa de Partes</small>
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="/seguimiento/trazabilidad"
                                        class="btn btn-app bg-light border shadow-sm w-100 py-3 text-left">
                                        <i class="bi bi-diagram-3 text-success fa-2x mb-1"></i>
                                        <span class="font-weight-bold d-block text-dark">Trazabilidad</span>
                                        <small class="text-muted">Seguir expediente por código</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Estado del sistema-->
                    <div class="card card-outline card-secondary shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                <i class="bi bi-info-circle"></i> Estado del Sistema
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-2 pr-2">

                                <!-- Estado de la Base de Datos Dinámico -->
                                <li class="item py-2">
                                    <div class="product-info ml-0">
                                        <?php if (!empty($dbConectada)): ?>
                                            <span class="text-success font-weight-bold">
                                                <i class="fas fa-circle fa-xs"></i> Base de Datos
                                            </span>
                                            <span class="product-description text-muted small">Conectada y sincronizada
                                                correctamente.</span>
                                        <?php else: ?>
                                            <span class="text-danger font-weight-bold">
                                                <i class="fas fa-times-circle fa-xs"></i> Base de Datos
                                            </span>
                                            <span class="product-description text-danger small">Error de conexión con el
                                                servidor SQL.</span>
                                        <?php endif; ?>
                                    </div>
                                </li>

                                <!-- Estado de la Sesión / Token Dinámico -->
                                <li class="item py-2">
                                    <div class="product-info ml-0">
                                        <?php if (!empty($sesionActiva)): ?>
                                            <span class="text-primary font-weight-bold">
                                                <i class="fas fa-shield-alt fa-xs"></i> Sesión Segura
                                            </span>
                                            <span class="product-description text-muted small">Token activo bajo protocolo
                                                MVC.</span>
                                        <?php else: ?>
                                            <span class="text-warning font-weight-bold">
                                                <i class="fas fa-exclamation-triangle fa-xs"></i> Sesión Segura
                                            </span>
                                            <span class="product-description text-warning small">Advertencia: Sesión próxima
                                                a expirar.</span>
                                        <?php endif; ?>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                    <!--Fin estado del sistema-->
                </div>
            </div>

        </div>
    </section>
</main>

<!-- SCRIPTS PARA RENDERIZAR EL GRÁFICO CON CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('pie-chart');
        if (ctx) {
            // Pasamos los valores dinámicos de PHP hacia JavaScript de forma segura
            const pendientes = <?= json_encode((int) ($expedientesRegistrados ?? 0)) ?>;
            const enProceso = <?= json_encode((int) ($expedientesEnProceso ?? 0)) ?>;
            const atendidosArchivados = <?= json_encode((int) ($tramitesAtendidos_Archivados ?? 0)) ?>;

            new Chart(ctx, {
                type: 'doughnut', // o 'bar' según prefieras
                data: {
                    labels: ['Pendientes (Registrados)', 'En Proceso', 'Atendidos / Archivados', 'Mis pendientes'],
                    datasets: [{
                        data: [pendientes, enProceso, atendidosArchivados],
                        backgroundColor: ['#dc3545', '#ffc107', '#198754', '#fd610d'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,  // Obligatorio para transformar rectángulos en la forma elegida
                                pointStyle: 'circle', // Cambia el marcador a un punto redondo
                                boxWidth: 10,         // Tamaño del punto
                                boxHeight: 10
                            }
                        }
                    }
                }
            });

        }
    });
</script>