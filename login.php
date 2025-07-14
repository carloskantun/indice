<?php
session_start();

$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'user';
$password   = getenv('DB_PASSWORD') ?: 'password';
$database   = getenv('DB_NAME') ?: 'database';

    die('Conexión fallida: ' . $conn->connect_error);
$email    = $_POST['email'] ?? '';
    if ($password === $user['password']) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['rol'];
        $_SESSION['puesto']    = $user['puesto'];
        header('Location: menu_principal.php');
        header('Location: index.php?error=Credenciales incorrectas');
    header('Location: index.php?error=Usuario no encontrado');
?>

if ($password === $user['password']) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol']; // Almacena el rol del usuario
        $_SESSION['puesto'] = $user['puesto']; // 7²2„1‚5 este es el que falta
        header("Location: menu_principal.php");
        echo "<pre>";
print_r($_SESSION);
exit;
        exit;
    } else {
        header("Location: index.php?error=Credenciales incorrectas");
        exit;
    }
} else {
    header("Location: index.php?error=Usuario no encontrado");
    exit;
}

echo "<pre>";
print_r($_SESSION);
exit;

