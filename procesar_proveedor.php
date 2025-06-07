<?php
include 'conexion.php'; // Conexión centralizada

// Validar datos del formulario
$nombre = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$email = $_POST['email'] ?? null;
$clabe = $_POST['clabe_interbancaria'] ?? null;
$cuenta = $_POST['numero_cuenta'] ?? null;
$banco = $_POST['banco'] ?? null;
$direccion = $_POST['direccion'] ?? null;
$rfc = $_POST['rfc'] ?? null;
$descripcion = $_POST['descripcion_servicio'] ?? '';
$persona_responsable = $_POST['persona_responsable'] ?? ''; // Evitar NULL

// Validar campos obligatorios
if (empty($nombre) || empty($telefono)) {
    die("Error: El nombre y el teléfono son obligatorios.");
}

// Insertar proveedor
$sql = "INSERT INTO proveedores (nombre, telefono, email, clabe_interbancaria, numero_cuenta, banco, direccion, rfc, descripcion_servicio, persona_responsable) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssss", $nombre, $telefono, $email, $clabe, $cuenta, $banco, $direccion, $rfc, $descripcion, $persona_responsable);


if ($stmt->execute()) {
    echo "Proveedor registrado correctamente. <a href='proveedores.php'>Regresar</a>";
} else {
    echo "Error al registrar el proveedor: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
