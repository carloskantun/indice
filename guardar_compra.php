<?php
session_start();
include 'conexion.php';

$orden_id = $_POST['orden_folio'] ?? null;
$fecha = $_POST['fecha_compra'] ?? '';
$monto = $_POST['monto'] ?? 0;
$proveedor_id = $_POST['proveedor_id'] ?? null;
$usuario_id = $_SESSION['user_id'] ?? null;
$nota_credito_id = $_POST['nota_credito_id'] ?? null;
$comprobante = $_POST['comprobante'] ?? null;
$observaciones = $_POST['observaciones'] ?? null;

if (empty($fecha) || empty($monto) || empty($proveedor_id)) {
    echo "Faltan campos.";
    exit;
}

// Generar folio automático tipo C-2025-0001
$anio = date('Y');
$prefix = "C-$anio-";
$result = $conn->query("SELECT COUNT(*) AS total FROM compras WHERE folio LIKE '$prefix%'");
$count = $result->fetch_assoc()['total'] + 1;
$folio = $prefix . str_pad($count, 4, "0", STR_PAD_LEFT);

// Validar nota de crédito (opcional)
if (!empty($nota_credito_id)) {
    $nc = $conn->query("SELECT monto FROM notas_credito WHERE id = $nota_credito_id")->fetch_assoc();
    if (!$nc || $nc['monto'] < $monto) {
        echo "El monto excede la nota de crédito disponible.";
        exit;
    }

    // Descontar monto de la nota de crédito
    $conn->query("UPDATE notas_credito SET monto = monto - $monto WHERE id = $nota_credito_id");
}

// Insertar en compras
$stmt = $conn->prepare("INSERT INTO compras 
    (folio, orden_id, fecha_compra, proveedor_id, usuario_id, monto_total, comprobante, observaciones, nota_credito_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sssiidssi",
    $folio,
    $orden_id,
    $fecha,
    $proveedor_id,
    $usuario_id,
    $monto,
    $comprobante,
    $observaciones,
    $nota_credito_id
);

if ($stmt->execute()) {
    // Si está ligada a una orden, actualizar estatus a Pagado
    if (!empty($orden_id)) {
        $conn->query("UPDATE ordenes_compra SET estatus_pago = 'Pagado' WHERE folio = '$orden_id'");
    }

    echo "ok";
} else {
    echo "Error al guardar: " . $stmt->error;
}

