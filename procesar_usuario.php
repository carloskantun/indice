<?php
session_start();
include 'conexion.php';

// Verificar permisos: solo superadmin puede registrar usuarios
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    die("Acceso no autorizado.");
}

// Obtener datos del formulario
$nombre   = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$email    = $_POST['email'] ?? '';
$puesto   = $_POST['puesto'] ?? '';
$password = $_POST['password'] ?? '';
$rol      = $_POST['rol'] ?? 'user';

// Validar campos
if (empty($nombre) || empty($telefono) || empty($email) || empty($puesto) || empty($password)) {
    die("Error: Todos los campos son obligatorios.");
}

// Guardar contraseña como texto plano (simple)
$sql = "INSERT INTO usuarios (nombre, telefono, email, puesto, password, rol) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $nombre, $telefono, $email, $puesto, $password, $rol);

if ($stmt->execute()) {
    echo "Usuario registrado correctamente. <a href='usuarios.php'>Regresar</a>";
} else {
    echo "Error al registrar usuario: " . $stmt->error;
}

$stmt->close();
$conn->close();