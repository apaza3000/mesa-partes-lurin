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
    <link rel="stylesheet" href="/view/assets/css/landing.css">
</head>

<body>

    <!-- ================= NAVBAR ================= -->
    <nav class="navbar-mp">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="/" class="brand">
                <img class="brand-logo" src="/view/assets/img/logo.png" alt="Logo institucional del IESTP Lurín">
                <div>
                    Mesa de Partes Virtual
                    <small>Gestión Documentaria</small>
                </div>
            </a>
            <div class="navbar-actions">

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
                        <i class="fas fa-circle text-warning badge-dot"></i>
                        Plataforma oficial en línea
                    </span>
                    <h1>Mesa de Partes Virtual<br>rápida, segura y transparente</h1>
                    <p class="lead">
                        Presenta, consulta y haz seguimiento a tus expedientes desde cualquier lugar.
                        Sin colas, sin papeles, disponible 24/7.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="/register" class="btn btn-hero-primary mr-2 mb-2">
                            <i class="fas fa-user-plus"></i> Crear cuenta gratis
                        </a>
                        <a href="#atajos" class="btn btn-hero-outline mb-2">
                            <i class="fas fa-th-large"></i> Ver servicios
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center d-none d-lg-block">
                    <figure class="hero-institution">
                        <img class="hero-logo" src="/view/assets/img/logo.png" alt="Emblema del Instituto de Educación Superior Tecnológico Público Lurín">
                        <figcaption>
                            <strong>IESTP Lurín</strong>
                            <span>Instituto de Educación Superior Tecnológico Público</span>
                        </figcaption>
                    </figure>
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
                        <span class="link-more">Próximamente</span>
                    </a>
                </div>


                <!-- Seguimiento -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="#" class="card-atajo">
                        <div class="icon-box bg-grad-3">
                            <i class="fas fa-route"></i>
                        </div>
                        <h5>Seguimiento de Trámite</h5>
                        <p>Conoce el recorrido de tu expediente por cada área de la institución.</p>
                        <span class="link-more">Próximamente</span>
                    </a>
                </div>

                <!-- Notificaciones -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-atajo is-unavailable" aria-disabled="true">
                        <div class="icon-box bg-grad-4">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h5>Notificaciones</h5>
                        <p>Revisa las respuestas, observaciones y comunicaciones oficiales.</p>
                        <span class="link-more">Próximamente</span>
                    </div>
                </div>

                <!-- Mesas de Partes -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-atajo is-unavailable" aria-disabled="true">
                        <div class="icon-box bg-grad-5">
                            <i class="fas fa-building"></i>
                        </div>
                        <h5>Mesas de Partes</h5>
                        <p>Directorio de mesas de partes físicas y virtuales por sede.</p>
                        <span class="link-more">Próximamente</span>
                    </div>
                </div>

                <!-- Ayuda -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-atajo is-unavailable" aria-disabled="true">
                        <div class="icon-box bg-grad-6">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h5>Ayuda y Soporte</h5>
                        <p>Guías, preguntas frecuentes y canales de atención al ciudadano.</p>
                        <span class="link-more">Próximamente</span>
                    </div>
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
            <a href="/register" class="btn btn-hero-primary btn-lg text-light   ">
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
                        <img class="footer-logo" src="/view/assets/img/logo.png" alt="" aria-hidden="true">
                        <span>Mesa de Partes Virtual</span>
                    </div>
                    <p>Plataforma oficial para la presentación, consulta y seguimiento de expedientes de forma digital.</p>
                    <div class="social mt-3">
                        <span class="social-link" aria-label="Facebook, enlace no configurado"><i class="fab fa-facebook-f" aria-hidden="true"></i></span>
                        <span class="social-link" aria-label="Twitter, enlace no configurado"><i class="fab fa-twitter" aria-hidden="true"></i></span>
                        <span class="social-link" aria-label="YouTube, enlace no configurado"><i class="fab fa-youtube" aria-hidden="true"></i></span>
                        <span class="social-link" aria-label="LinkedIn, enlace no configurado"><i class="fab fa-linkedin-in" aria-hidden="true"></i></span>
                    </div>
                </div>

                <div class="col-md-2 mb-4">
                    <h6>Servicios</h6>
                    <a href="/register">Entrega de Expedientes</a>
                    <span class="footer-link-unavailable">Consulta de Expedientes</span>
                    <span class="footer-link-unavailable">Seguimiento</span>
                    <span class="footer-link-unavailable">Notificaciones</span>
                </div>

                <div class="col-md-2 mb-4">
                    <h6>Institución</h6>
                    <span class="footer-link-unavailable">Quiénes somos</span>
                    <span class="footer-link-unavailable">Transparencia</span>
                    <span class="footer-link-unavailable">Normativa</span>
                    <span class="footer-link-unavailable">Contacto</span>
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
    <script src="/view/assets/js/landing.js" defer></script>

</body>

</html>