<?php
include 'auth.php';
include 'conexion.php';

$folio = $_POST['orden_id'] ?? '';
$vehiculo = $_POST['vehiculo'] ?? '';

if (!$folio || $vehiculo === null) {
    http_response_code(400);
    echo "Faltan datos.";
    exit;
}

$stmt = $conn->prepare("UPDATE ordenes_transfers SET vehiculo=? WHERE folio=?");
$stmt->bind_param("ss", $vehiculo, $folio);
$stmt->execute();
$stmt->close();
$conn->close();

echo "Guardado";