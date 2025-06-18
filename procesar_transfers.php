<?php
include 'conexion.php';

$tipo_servicio = $_POST['tipo_servicio'] ?? '';
$fecha_servicio = $_POST['fecha_servicio'] ?? '';
$pickup = $_POST['pickup'] ?? '';
$hotel_pickup = $_POST['hotel_pickup'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$nombre_pasajeros = $_POST['nombre_pasajeros'] ?? '';
$num_pasajeros = $_POST['num_pasajeros'] ?? '';
$habitacion = $_POST['habitacion'] ?? '';
$vehiculo = $_POST['vehiculo'] ?? '';
$placas = $_POST['placas'] ?? '';
$numero_economico = $_POST['numero_economico'] ?? '';
$conductor = $_POST['conductor'] ?? '';
$agencia = $_POST['agencia'] ?? '';
$observaciones = $_POST['observaciones'] ?? '';
$estatus = $_POST['estatus'] ?? 'Pendiente';
$usuario_id = $_POST['usuario_solicitante_id'] ?? '';
$unidad_id = $_POST['unidad_negocio_id'] ?? '';

if (empty($tipo_servicio) || empty($fecha_servicio) || empty($pickup) || empty($hotel_pickup)) {
    die("Error: faltan datos obligatorios.");
}

$sql = "INSERT INTO ordenes_transfers
    (tipo_servicio, fecha_servicio, pickup, hotel_pickup, direccion, nombre_pasajeros,
     num_pasajeros, habitacion, observaciones, vehiculo, placas, numero_economico,
     conductor, agencia, estatus, usuario_solicitante_id, unidad_negocio_id)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ssssssissssssssii', $tipo_servicio, $fecha_servicio, $pickup, $hotel_pickup, $direccion, $nombre_pasajeros, $num_pasajeros, $habitacion, $observaciones, $vehiculo, $placas, $numero_economico, $conductor, $agencia, $estatus, $usuario_id, $unidad_id);

if ($stmt->execute()) {
    $id = $stmt->insert_id;
    $folio = date('ym') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    $up = $conn->prepare("UPDATE ordenes_transfers SET folio=? WHERE id=?");
    $up->bind_param('si', $folio, $id);
    $up->execute();
    echo "✅ Transfer registrado con folio <strong>$folio</strong>.";
} else {
    echo "❌ Error al registrar: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
