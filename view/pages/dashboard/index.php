<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Panel Principal</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Inicio</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Mensaje de bienvenida institucional -->
            <div class="alert alert-secondary alert-dismissible shadow-sm border-0">
                <h5><i class="icon fas fa-user-shield"></i> ¡Bienvenido,
                    <?= htmlspecialchars($usuario['nombres'] ?? 'Usuario') ?>!</h5>
                Estás conectado en el sistema de gestión documentaria del IESTP "Lurín" con el rol de acceso:
                <span class="badge badge-primary px-2 py-1 text-uppercase"><?= htmlspecialchars($rolActual) ?></span>
            </div>

            <!-- Carga de widgets modulares según el rol -->
            <?php
            // Normalizamos el rol a minúsculas o evaluamos según lo que recibas en sesión
            $rol = strtolower(trim($rolActual));

            switch ($rol) {
                case 'admin':
                case 'administrador':
                    @include __DIR__ . '/components/admin_widgets.php';
                    break;

                case 'mesaparte':
                case 'mesa_parte':
                    @include __DIR__ . '/components/mesa_parte_widgets.php';
                    break;

                case 'jefearea':
                case 'jefe_area':
                    @include __DIR__ . '/components/jefe_area_widgets.php';
                    break;

                case 'consultor':
                default:
                    @include __DIR__ . '/components/consultor_widgets.php';
                    break;
            }
            ?>

        </div>
    </section>
</div>