<?php
session_start();
include 'auth.php';
include 'conexion.php';

if (!in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    die("Acceso no autorizado.");
}

$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$email = $_POST['email'] ?? '';
$puesto = $_POST['puesto'] ?? '';
$rol = $_POST['rol'] ?? 'user';

if (!$id || !$nombre || !$telefono || !$email || !$puesto) {
    die("Faltan campos obligatorios.");
}

$stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, email = ?, puesto = ?, rol = ? WHERE id = ?");
$stmt->bind_param("sssssi", $nombre, $telefono, $email, $puesto, $rol, $id);

if ($stmt->execute()) {
    echo "Usuario actualizado correctamente. <a href='listar_usuarios.php'>Volver</a>";
} else {
    echo "Error al actualizar: " . $stmt->error;
}
$stmt->close();
$conn->close();