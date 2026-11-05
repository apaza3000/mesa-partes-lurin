<?php
/*
 * Vista de error 500 (SEC-09).
 * No muestra rutas absolutas, trazas ni mensajes internos del sistema.
 * Renderizada de forma directa (sin layout) porque el fallo puede haber
 * ocurrido antes de que la aplicación esté en condiciones de montar una.
 */
?>
<!DOCTYPE html>
<html lang="es-PE">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error interno del servidor</title>
    <link rel="stylesheet" href="/view/assets/css/admin-lte/adminlte.css">
</head>

<body class="bg-body-tertiary">
    <div class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="container">
            <div class="text-center">
                <h1 class="display-1 fw-bold text-dark">500</h1>
                <p class="fs-3"><span class="text-danger">Ups.</span> Se produjo un error en el servidor.</p>
                <p class="lead text-muted">
                    La operación no pudo completarse. El incidente quedó registrado;
                    intente de nuevo en unos minutos o comuníquese con el administrador del sistema.
                </p>
                <a href="/" class="btn btn-primary">Volver al inicio</a>
                <a href="/login" class="btn btn-outline-secondary">Iniciar sesión</a>
            </div>
        </div>
    </div>
</body>

</html>
