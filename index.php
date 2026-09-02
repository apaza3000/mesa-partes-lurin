<?php
session_start();

define('ROOT_DIR', __DIR__);

require_once __DIR__ . '/backend/src/core/conexion.php';

$email_value = '';
$error_email = '';
$error_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email_value = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email_value === '') {
        $error_email = 'Por favor, ingrese su correo institucional.';
    } elseif (!preg_match('/^[0-9]{8}@iestplurin\.edu\.pe$/i', $email_value)) {
        $error_email = 'Debe ingresar su DNI seguido de @iestplurin.edu.pe';
    } elseif (strlen($password) < 6) {
        $error_password = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        try {
            $pdo = \src\Core\Conexion::getConexion();
            $stmt = $pdo->prepare('SELECT u.id_usuario, u.password, u.estado, p.email
                FROM usuarios u
                INNER JOIN persona p ON p.id_persoan = u.id_persona
                WHERE p.email = :email AND u.estado = "Activo"
                LIMIT 1');
            $stmt->execute([':email' => $email_value]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                $error_email = 'No se encontró un usuario activo con ese correo institucional.';
            } elseif (!password_verify($password, $usuario['password'])) {
                $error_password = 'La contraseña es incorrecta.';
            } else {
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['login'] = true;
                header('Location: index.php');
                exit;
            }
        } catch (Throwable $e) {
            $error_email = 'No se pudo verificar la sesión. Revise la conexión a la base de datos.';
            error_log($e->getMessage());
        }
    }
}

if (!empty($_SESSION['login']) && !empty($_SESSION['usuario_id'])) {
    require_once __DIR__ . '/view/platilla.php';
} else {
    require_once __DIR__ . '/view/pages/login.php';
}
