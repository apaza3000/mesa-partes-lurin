<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$email_value    = $_SESSION['email_value'] ?? '';
$error_email    = $_SESSION['error_email'] ?? '';
$error_password = $_SESSION['error_password'] ?? '';

// Limpiamos los mensajes de error de la sesión para que no se queden parpadeando eternamente
unset($_SESSION['email_value'], $_SESSION['error_email'], $_SESSION['error_password']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión Documentaria IESTP Lurín</title>
    <link rel="stylesheet" href="/view/assets/css/login.css">
</head>

<body>
    <!-- El resto de tu código HTML sigue exactamente igual -->