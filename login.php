<?php
session_start();
require_once 'conexion.php'; // ✅ Conectamos aquí

// Obtener datos del formulario
$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Buscar usuario
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if ($password === $user['password']) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['nombre'];
        $_SESSION['user_role']  = $user['rol'];
        $_SESSION['puesto']     = $user['puesto'];

        header("Location: menu_principal.php");
        exit;
    } else {
        header("Location: index.php?error=Credenciales incorrectas");
        exit;
    }
} else {
    header("Location: index.php?error=Usuario no encontrado");
    exit;
}
?>
