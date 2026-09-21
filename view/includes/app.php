<!doctype html>
<html lang="en">
<!--begin::Head-->
<?php
require_once("head.php");
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <!--begin::Header-->
        <?php
        require_once("header.php");
        ?>
        <!--end::Header-->
        <!--begin::Sidebar-->
        <?php
        require_once("sidebar.php");
        ?>
        <!--end::Sidebar-->
        <!--begin::App Main-->
        <main class="app-main">
            <!--begin::App Content Header-->
            <?php
            echo $contenido;
            ?>
            <!--end::App Content-->
        </main>
        <!--end::App Main-->
        <!--begin::Footer-->
        <?php
        require_once("footer.php");
        ?>
        <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->

    <?php
    require_once("scripts.php");
    ?>
    <!--begin::Color Mode Toggle-->
    <!-- The light/dark/auto switcher ships in adminlte.js as the ColorMode
     module (since 4.1) — no page script needed. Only the no-flash snippet
     in <head> stays inline, because it must run before first paint. -->
    <!--end::Color Mode Toggle-->
    <!--end::Script-->
</body>
<!--end::Body-->

</html>