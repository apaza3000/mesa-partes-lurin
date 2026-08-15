<?php
session_start();
$_SESSION["login"] = true;
if ($_SESSION["login"]) {
    require_once("view/platilla.php");
} else {
    require_once("view/pages/login.php");
}

?>