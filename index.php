<?php
session_start();

require_once "vendor/autoload.php";


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$_SESSION["login"] = true;
if ($_SESSION["login"]) {
    require_once("view/plantilla.php");
} else {
    require_once("view/pages/login.php");
}
