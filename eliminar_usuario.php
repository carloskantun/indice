<?php
session_start();
include 'auth.php';
include 'conexion.php';

if (!in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    die("Acceso no autorizado.");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID no proporcionado.");
}

// Prevenciиоn bивsica: No permitir que un usuario se borre a sик mismo
if ($_SESSION['user_id'] == $id) {
    die("No puedes eliminar tu propio usuario.");
}

$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: usuarios.php?mensaje=Usuario eliminado");
    exit;
} else {
    echo "Error al eliminar usuario.";
}
?>