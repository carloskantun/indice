<?php
include 'conexion.php';

// Validar y obtener datos del formulario
$nombre = $_POST['nombre'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$unidad_negocio_id = $_POST['unidad_negocio_id'] ?? null;
$notas = $_POST['notas'] ?? '';

// Validaci¨®n b¨¢sica
if (empty($nombre)) {
    die("Error: El nombre del alojamiento es obligatorio.");
}

// Insertar en la base de datos
$sql = "INSERT INTO alojamientos (nombre, direccion, unidad_negocio_id, notas) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssis", $nombre, $direccion, $unidad_negocio_id, $notas);

if ($stmt->execute()) {
    echo "Alojamiento registrado correctamente. <a href='alojamientos.php'>Regresar</a>";
} else {
    echo "Error al registrar el alojamiento: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
