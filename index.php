<?php

use Dotenv\Dotenv;

session_start();

$autoload = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}
require_once "autoload.php";

$envFile = __DIR__ . DIRECTORY_SEPARATOR . '.env';
Dotenv::createImmutable(__DIR__)->load();


$_SESSION["login"] = true;
if ($_SESSION["login"]) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "plantilla.php";
} else {
    require_once __DIR__ . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "pages" . DIRECTORY_SEPARATOR . "login.php";
}
