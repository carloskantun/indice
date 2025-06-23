<?php
include 'auth.php';
include 'conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_creador = $_SESSION['user_id'] ?? null;

$tipo_servicio  = $_POST['tipo_servicio'] ?? '';
$fecha          = $_POST['fecha'] ?? '';
$pickup         = $_POST['pickup'] ?? '';
$hotel          = $_POST['hotel'] ?? '';
$pasajeros      = $_POST['pasajeros'] ?? '';
$numero_reserva = $_POST['numero_reserva'] ?? '';
$vehiculo       = $_POST['vehiculo'] ?? '';
$conductor      = $_POST['conductor'] ?? '';
$agencia        = $_POST['agencia'] ?? '';
$estatus        = $_POST['estatus'] ?? 'Pendiente';

if (empty($tipo_servicio) || empty($fecha) || empty($pickup) || empty($hotel)) {
    die("Error: faltan datos obligatorios.");
}

$sql = "INSERT INTO ordenes_transfers 
    (tipo_servicio, fecha_servicio, pickup, hotel_pickup, nombre_pasajeros, num_pasajeros, 
     vehiculo, conductor, agencia, estatus, usuario_solicitante_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'sssssissssi',
    $tipo_servicio,
    $fecha,
    $pickup,
    $hotel,
    $pasajeros,
    $numero_reserva,
    $vehiculo,
    $conductor,
    $agencia,
    $estatus,
    $usuario_creador
);

$mensaje = '';
if ($stmt->execute()) {
    $id = $stmt->insert_id;
    $folio = 'TRSF-' . date('ym') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    
    $up = $conn->prepare("UPDATE ordenes_transfers SET folio=? WHERE id=?");
    $up->bind_param('si', $folio, $id);
    $up->execute();
    
    $mensaje = "✅ Orden registrada con folio $folio";
} else {
    $mensaje = "❌ Error al registrar: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redireccionamiento dinámico
if (isset($_POST['origen']) && $_POST['origen'] === 'registrar') {
    header("Location: registrar_transfer.php?msg=" . urlencode($mensaje));
} else {
    header("Location: minipanel_transfers.php?msg=" . urlencode($mensaje));
}
exit;
