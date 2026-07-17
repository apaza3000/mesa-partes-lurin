<?php
$error_email = "";
$error_password = "";
$email_value = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_value = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $form_valido = true;

    if (empty($email_value)) {
        $error_email = "Por favor, ingrese su correo institucional.";
        $form_valido = false;
    } elseif (!filter_var($email_value, FILTER_VALIDATE_EMAIL)) {
        $error_email = "El formato de correo no es válido.";
        $form_valido = false;
    } elseif (!preg_match('/^[0-9]{8}@iestplurin\.edu\.pe$/', $email_value)) {
        $error_email = "Debe ingresar su DNI seguido de @iestplurin.edu.pe";
        $form_valido = false;
    }

    if (empty($password)) {
        $error_password = "La contraseña debe tener al menos 6 caracteres.";
        $form_valido = false;
    } elseif (strlen($password) < 6) {
        $error_password = "La contraseña debe tener al menos 6 caracteres.";
        $form_valido = false;
    }

    if ($form_valido) {
        header("Location: dashboard.php"); 
        exit();
    }
}
?>
