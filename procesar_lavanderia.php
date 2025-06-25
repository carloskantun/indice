<?php
include 'auth.php';
include 'conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario = $_SESSION['user_id'] ?? null;

$fecha    = $_POST['fecha'] ?? '';
$cliente  = $_POST['cliente'] ?? '';
$servicio = $_POST['servicio'] ?? '';
$prenda   = $_POST['prenda'] ?? '';
$cantidad = (int)($_POST['cantidad'] ?? 1);
$monto    = (float)($_POST['monto'] ?? 0);
$estatus  = $_POST['estatus'] ?? 'Pendiente';
$unidad   = $_POST['unidad_negocio_id'] ?? null;

if (!$fecha || !$cliente || !$servicio || !$prenda || !$unidad) {
    die("Error: faltan datos obligatorios.");
}

$sql = "INSERT INTO ordenes_lavanderia (fecha, cliente, servicio, prenda, cantidad, monto, estatus, usuario_creador_id, unidad_negocio_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssssiisii', $fecha, $cliente, $servicio, $prenda, $cantidad, $monto, $estatus, $usuario, $unidad);

if ($stmt->execute()) {
    $id = $stmt->insert_id;
    $folio = 'LAV-' . date('ym') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    $up = $conn->prepare("UPDATE ordenes_lavanderia SET folio=? WHERE id=?");
    $up->bind_param('si', $folio, $id);
    $up->execute();
    $mensaje = "✅ Pedido registrado con folio $folio";
} else {
    $mensaje = "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

$destino = $_POST['origen'] ?? 'minipanel_lavanderia.php';
header("Location: $destino?msg=" . urlencode($mensaje));
exit;
?>
