<?php
include 'conexion.php';
session_start();

$proveedor_id = $_POST['proveedor_id'] ?? null;
$fecha_compra = $_POST['fecha_compra'] ?? null;
$monto_total = $_POST['monto_total'] ?? null;
$nota_credito = $_POST['nota_credito'] ?? null;
$usuario_id = $_SESSION['user_id'] ?? 0;

if (!$proveedor_id || !$fecha_compra || !$monto_total) {
    echo "error: Datos incompletos";
    exit;
}

$stmt = $conn->prepare("INSERT INTO compras (proveedor_id, fecha_compra, monto_total, usuario_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isdi", $proveedor_id, $fecha_compra, $monto_total, $usuario_id);

if ($stmt->execute()) {
    echo "ok";
} else {
    echo "error: No se pudo guardar";
}
exit;

