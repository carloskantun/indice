<?php
include 'conexion.php';
session_start();

$proveedor_id = $_POST['proveedor_id'] ?? null;
$monto = $_POST['monto'] ?? null;
$fecha_pago = $_POST['fecha_pago'] ?? null;
$unidad_id = $_POST['unidad_negocio_id'] ?? null;
$tipo_gasto = $_POST['tipo_gasto'] ?? 'Unico';
$medio_pago = $_POST['medio_pago'] ?? 'Transferencia';
$cuenta = $_POST['cuenta_bancaria'] ?? null;
$concepto = $_POST['concepto'] ?? null;
$origen = $_POST['origen'] ?? 'Directo';
$orden_folio = $_POST['orden_folio'] ?? null;

if (!$proveedor_id || !$monto || !$fecha_pago || !$unidad_id) {
    echo 'Faltan datos';
    exit;
}

$anio = date('Y');
$prefix = "G-$anio-";
$count = $conn->query("SELECT COUNT(*) AS total FROM gastos WHERE folio LIKE '$prefix%'")->fetch_assoc()['total'] + 1;
$folio = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);

$estatus = 'Pagado';
if ($origen === 'Orden') {
    $estatus = 'Abonado';
}

$stmt = $conn->prepare("INSERT INTO gastos (folio, proveedor_id, monto, fecha_pago, unidad_negocio_id, tipo_gasto, medio_pago, cuenta_bancaria, estatus, concepto, orden_folio, origen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sidsisssssss', $folio, $proveedor_id, $monto, $fecha_pago, $unidad_id, $tipo_gasto, $medio_pago, $cuenta, $estatus, $concepto, $orden_folio, $origen);

if ($stmt->execute()) {
    echo 'ok';
} else {
    echo 'Error al guardar';
}
?>
