<?php

use Src\Core\Session;

$auth = Session::isAuthenticated();


// echo "<pre>";
// print_r(Session::user());
// echo "</pre>";
// return;


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mesa de Partes Virtual</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Mesa de Partes Virtual - Entrega, consulta y seguimiento de expedientes en línea.">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e3c72;
            --primary-light: #2a5298;
            --accent: #ffb703;
            --success: #28a745;
            --info: #17a2b8;
            --danger: #dc3545;
            --dark: #1c1c1c;
            --gray: #6c757d;
        }

        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f7fb;
            color: #333;
        }

        /* ============ NAVBAR ============ */
        .navbar-mp {
            background: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-mp .brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            color: var(--primary);
            font-size: 1.25rem;
            text-decoration: none;
        }

        .navbar-mp .brand i {
            font-size: 1.7rem;
            margin-right: 10px;
            color: var(--primary);
        }

        .navbar-mp .brand small {
            display: block;
            font-size: .7rem;
            font-weight: 400;
            color: var(--gray);
            letter-spacing: .5px;
        }

        .btn-login {
            border: 2px solid var(--primary);
            color: var(--primary);
            font-weight: 600;
            border-radius: 30px;
            padding: 6px 22px;
            transition: all .25s;
        }

        .btn-login:hover {
            background: var(--primary);
            color: #fff;
        }

        .btn-register {
            background: var(--primary);
            border: 2px solid var(--primary);
            color: #fff;
            font-weight: 600;
            border-radius: 30px;
            padding: 6px 22px;
            transition: all .25s;
        }

        .btn-register:hover {
            background: var(--primary-light);
            border-color: var(--primary-light);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(30, 60, 114, .3);
        }

        /* ============ HERO ============ */
        .hero {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 60%, #3f6fb5 100%);
            color: #fff;
            padding: 90px 0 110px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: -60px;
            right: -60px;
            width: 320px;
            height: 320px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .hero::after {
            content: "";
            position: absolute;
            bottom: -120px;
            left: -80px;
            width: 380px;
            height: 380px;
            background: rgba(255, 255, 255, .05);
            border-radius: 50%;
        }

        .hero h1 {
            font-size: 2.6rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 18px;
        }

        .hero p.lead {
            font-size: 1.1rem;
            opacity: .92;
            max-width: 620px;
            margin-bottom: 30px;
        }

        .hero .badge-mp {
            background: rgba(255, 255, 255, .15);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 30px;
            padding: 6px 18px;
            font-size: .85rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 20px;
        }

        .hero .btn-hero-primary {
            background: var(--accent);
            border: none;
            color: #1c1c1c;
            font-weight: 600;
            border-radius: 30px;
            padding: 12px 30px;
            transition: all .25s;
        }

        .hero .btn-hero-primary:hover {
            background: #ffc93c;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(255, 183, 3, .4);
        }

        .hero .btn-hero-outline {
            border: 2px solid rgba(255, 255, 255, .7);
            color: #fff;
            font-weight: 600;
            border-radius: 30px;
            padding: 12px 30px;
            transition: all .25s;
        }

        .hero .btn-hero-outline:hover {
            background: #fff;
            color: var(--primary);
        }

        .hero-img {
            max-width: 100%;
            filter: drop-shadow(0 20px 40px rgba(0, 0, 0, .3));
        }

        /* ============ ATAJOS ============ */
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-weight: 700;
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .section-title p {
            color: var(--gray);
            max-width: 620px;
            margin: 0 auto;
        }

        .atajos {
            padding: 80px 0;
            margin-top: -60px;
        }

        .card-atajo {
            background: #fff;
            border-radius: 16px;
            padding: 30px 25px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, .06);
            transition: all .3s ease;
            height: 100%;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }

        .card-atajo::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .3s;
        }

        .card-atajo:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 40px rgba(30, 60, 114, .18);
            border-color: rgba(30, 60, 114, .08);
        }

        .card-atajo:hover::before {
            transform: scaleX(1);
        }

        .card-atajo .icon-box {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            color: #fff;
            margin-bottom: 18px;
        }

        .card-atajo h5 {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .card-atajo p {
            color: var(--gray);
            font-size: .92rem;
            margin-bottom: 15px;
        }

        .card-atajo .link-more {
            color: var(--primary);
            font-weight: 600;
            font-size: .88rem;
        }

        .card-atajo:hover .link-more i {
            transform: translateX(4px);
        }

        .card-atajo .link-more i {
            transition: transform .25s;
            margin-left: 5px;
        }

        .bg-grad-1 {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
        }

        .bg-grad-2 {
            background: linear-gradient(135deg, #17a2b8, #0f7f92);
        }

        .bg-grad-3 {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }

        .bg-grad-4 {
            background: linear-gradient(135deg, #ffb703, #e69500);
        }

        .bg-grad-5 {
            background: linear-gradient(135deg, #6f42c1, #553098);
        }

        .bg-grad-6 {
            background: linear-gradient(135deg, #dc3545, #a71d2a);
        }

        /* ============ CÓMO FUNCIONA ============ */
        .como-funciona {
            padding: 70px 0;
            background: #fff;
        }

        .paso {
            text-align: center;
            padding: 20px;
        }

        .paso .num {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            box-shadow: 0 8px 20px rgba(30, 60, 114, .25);
        }

        .paso h6 {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .paso p {
            color: var(--gray);
            font-size: .9rem;
        }

        /* ============ CTA ============ */
        .cta {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            padding: 60px 0;
            text-align: center;
        }

        .cta h3 {
            font-weight: 700;
            margin-bottom: 12px;
        }

        .cta p {
            opacity: .9;
            margin-bottom: 25px;
        }

        /* ============ FOOTER ============ */
        footer {
            background: #0f1f3d;
            color: #b8c3d6;
            padding: 50px 0 20px;
            font-size: .9rem;
        }

        footer h6 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 18px;
            letter-spacing: .5px;
        }

        footer a {
            color: #b8c3d6;
            text-decoration: none;
            display: block;
            margin-bottom: 8px;
            transition: color .2s;
        }

        footer a:hover {
            color: #fff;
        }

        footer .footer-brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 12px;
        }

        footer .social a {
            display: inline-flex;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            transition: all .25s;
        }

        footer .social a:hover {
            background: var(--primary-light);
            color: #fff;
            transform: translateY(-3px);
        }

        footer .copy {
            border-top: 1px solid rgba(255, 255, 255, .1);
            margin-top: 30px;
            padding-top: 18px;
            text-align: center;
            font-size: .82rem;
            color: #8896b3;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 1.9rem;
            }

            .hero {
                padding: 60px 0 80px;
            }

            .atajos {
                margin-top: -40px;
            }
        }
    </style>
</head>

<body>

    <!-- ================= NAVBAR ================= -->
    <nav class="navbar-mp">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="index.php" class="brand">
                <i class="fas fa-landmark"></i>
                <div>
                    Mesa de Partes Virtual
                    <small>Gestión Documentaria</small>
                </div>
            </a>
            <div class="d-flex align-items-center">

                <?php if (!$auth): ?>
                    <a href="/login" class="btn btn-login mr-2">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                    <a href="/register" class="btn btn-register">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </a>
                <?php else: ?>
                    <a href="/home" class="btn btn-login mr-2">
                        <i class="fas fa-dashboard"></i> Dashboard
                    </a>
                <?php endif; ?>



            </div>
        </div>
    </nav>

    <!-- ================= HERO ================= -->
    <section class="hero">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="badge-mp">
                        <i class="fas fa-circle text-warning" style="font-size:.5rem;"></i>
                        Plataforma oficial en línea
                    </span>
                    <h1>Mesa de Partes Virtual<br>rápida, segura y transparente</h1>
                    <p class="lead">
                        Presenta, consulta y haz seguimiento a tus expedientes desde cualquier lugar.
                        Sin colas, sin papeles, disponible 24/7.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="index.php?c=registro&a=index" class="btn btn-hero-primary mr-2 mb-2">
                            <i class="fas fa-user-plus"></i> Crear cuenta gratis
                        </a>
                        <a href="#atajos" class="btn btn-hero-outline mb-2">
                            <i class="fas fa-th-large"></i> Ver servicios
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center d-none d-lg-block">
                    <i class="fas fa-folder-open hero-img" style="font-size: 14rem; opacity:.9;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= ATAJOS ================= -->
    <section class="atajos" id="atajos">
        <div class="container">
            <div class="section-title">
                <h2>¿Qué deseas hacer hoy?</h2>
                <p>Accede rápidamente a los servicios más utilizados de la Mesa de Partes Virtual.</p>
            </div>

            <div class="row">

                <!-- Entrega de Expedientes -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="/entregas" class="card-atajo">
                        <div class="icon-box bg-grad-1">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <h5>Entrega de Expedientes</h5>
                        <p>Presenta tu solicitud o documento de forma virtual en pocos pasos.</p>
                        <span class="link-more">Comenzar <i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>

                <!-- Consulta de Expedientes -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="/consulta" class="card-atajo">
                        <div class="icon-box bg-grad-2">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5>Consulta de Expedientes</h5>
                        <p>Verifica el estado y ubicación de tu expediente en tiempo real.</p>
                        <span class="link-more">Consultar <i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>

                <!-- Seguimiento -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="index.php?c=seguimiento&a=index" class="card-atajo">
                        <div class="icon-box bg-grad-3">
                            <i class="fas fa-route"></i>
                        </div>
                        <h5>Seguimiento de Trámite</h5>
                        <p>Conoce el recorrido de tu expediente por cada área de la institución.</p>
                        <span class="link-more">Ver seguimiento <i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>

                <!-- Notificaciones -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="index.php?c=notificaciones&a=index" class="card-atajo">
                        <div class="icon-box bg-grad-4">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h5>Notificaciones</h5>
                        <p>Revisa las respuestas, observaciones y comunicaciones oficiales.</p>
                        <span class="link-more">Ver notificaciones <i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>

                <!-- Mesas de Partes -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="index.php?c=mesas&a=index" class="card-atajo">
                        <div class="icon-box bg-grad-5">
                            <i class="fas fa-building"></i>
                        </div>
                        <h5>Mesas de Partes</h5>
                        <p>Directorio de mesas de partes físicas y virtuales por sede.</p>
                        <span class="link-more">Ver directorio <i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>

                <!-- Ayuda -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="index.php?c=ayuda&a=index" class="card-atajo">
                        <div class="icon-box bg-grad-6">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5>Ayuda y Soporte</h5>
                        <p>Guías, preguntas frecuentes y canales de atención al ciudadano.</p>
                        <span class="link-more">Obtener ayuda <i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- ================= CÓMO FUNCIONA ================= -->
    <section class="como-funciona">
        <div class="container">
            <div class="section-title">
                <h2>¿Cómo funciona?</h2>
                <p>En solo 3 pasos puedes presentar y dar seguimiento a tu expediente.</p>
            </div>
            <div class="row">
                <div class="col-md-4 paso">
                    <div class="num">1</div>
                    <h6>Regístrate</h6>
                    <p>Crea tu cuenta de usuario invitado con tu DNI, correo y una contraseña segura.</p>
                </div>
                <div class="col-md-4 paso">
                    <div class="num">2</div>
                    <h6>Presenta tu expediente</h6>
                    <p>Sube tus documentos, completa el formulario y obtén tu número de expediente.</p>
                </div>
                <div class="col-md-4 paso">
                    <div class="num">3</div>
                    <h6>Haz seguimiento</h6>
                    <p>Consulta en línea el estado, observaciones y respuesta de tu trámite.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= CTA ================= -->
    <section class="cta">
        <div class="container">
            <h3>¿Listo para empezar?</h3>
            <p>Crea tu cuenta y presenta tu primer expediente en minutos.</p>
            <a href="index.php?c=registro&a=index" class="btn btn-hero-primary btn-lg">
                <i class="fas fa-user-plus"></i> Registrarme ahora
            </a>
        </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="footer-brand">
                        <i class="fas fa-landmark"></i> Mesa de Partes Virtual
                    </div>
                    <p>Plataforma oficial para la presentación, consulta y seguimiento de expedientes de forma digital.</p>
                    <div class="social mt-3">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="col-md-2 mb-4">
                    <h6>Servicios</h6>
                    <a href="index.php?c=entrega&a=index">Entrega de Expedientes</a>
                    <a href="index.php?c=consulta&a=index">Consulta de Expedientes</a>
                    <a href="index.php?c=seguimiento&a=index">Seguimiento</a>
                    <a href="index.php?c=notificaciones&a=index">Notificaciones</a>
                </div>

                <div class="col-md-2 mb-4">
                    <h6>Institución</h6>
                    <a href="#">Quiénes somos</a>
                    <a href="#">Transparencia</a>
                    <a href="#">Normativa</a>
                    <a href="#">Contacto</a>
                </div>

                <div class="col-md-4 mb-4">
                    <h6>Contacto</h6>
                    <p class="mb-1"><i class="fas fa-map-marker-alt mr-2"></i> Av. Principal 123, Lima - Perú</p>
                    <p class="mb-1"><i class="fas fa-phone mr-2"></i> (01) 123-4567</p>
                    <p class="mb-1"><i class="fas fa-envelope mr-2"></i> mesadepartes@institucion.gob.pe</p>
                    <p class="mb-0"><i class="fas fa-clock mr-2"></i> Lun a Vie: 8:30 am - 5:30 pm</p>
                </div>
            </div>

            <div class="copy">
                © <?= date('Y') ?> Mesa de Partes Virtual — Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        // Scroll suave para anclas
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>

</body>

</html>