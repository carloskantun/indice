<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'auth.php';
include 'conexion.php';

if (!in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    die("Acceso no autorizado.");
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID no proporcionado.");
?>
$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: usuarios.php?mensaje=Usuario eliminado");
    exit;
} else {
    echo "Error al eliminar usuario.";
}
?>