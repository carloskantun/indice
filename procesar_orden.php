<?php
session_start();
include 'conexion.php'; // Conexión centralizada

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado.");
}

// Obtener datos del formulario
$proveedor = $_POST['proveedor_id'] ?? null;
$monto = $_POST['monto'] ?? 0;
$vencimiento = $_POST['vencimiento_pago'] ?? '';
$concepto = $_POST['concepto_pago'] ?? '';
$tipo_pago = $_POST['tipo_pago'] ?? '';
$factura = $_POST['genera_factura'] ?? 'No';
$usuario = $_POST['usuario_solicitante_id'] ?? null;
$unidad_negocio = $_POST['unidad_negocio_id'] ?? null;

// Determinar estatus inicial segun fecha de vencimiento
$estatus_inicial = (strtotime($vencimiento) < strtotime(date("Y-m-d"))) ? "Vencido" : "Por pagar";

$sql = "INSERT INTO ordenes_compra (folio, proveedor_id, monto, vencimiento_pago, concepto_pago, tipo_pago, genera_factura, usuario_solicitante_id, unidad_negocio_id, comprobante_path, estatus_pago)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt->bind_param("sidsississs", $folio, $proveedor, $monto, $vencimiento, $concepto, $tipo_pago, $factura, $usuario, $unidad_negocio, $comprobante_path, $estatus_inicial);
$conteo = $result->fetch_assoc()['total'] + 1;
$folio = "OC-$fecha-$conteo"; // Ejemplo: OC-20250205-3

// Procesar archivo si aplica
$comprobante_path = null;
if (!empty($_FILES['comprobante']['name'])) {
    $directorio = "uploads/";
    $nombre_archivo = time() . "_" . basename($_FILES['comprobante']['name']);
    $comprobante_path = $directorio . $nombre_archivo;
    move_uploaded_file($_FILES['comprobante']['tmp_name'], $comprobante_path);
}

// Validaciones
if (!$proveedor || !$usuario || !$unidad_negocio) {
    die("Error: Proveedor, usuario y unidad de negocio son obligatorios.");
}

// Insertar orden en la base de datos
$sql = "INSERT INTO ordenes_compra (folio, proveedor_id, monto, vencimiento_pago, concepto_pago, tipo_pago, genera_factura, usuario_solicitante_id, unidad_negocio_id, comprobante_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sidsississ", $folio, $proveedor, $monto, $vencimiento, $concepto, $tipo_pago, $factura, $usuario, $unidad_negocio, $comprobante_path);

if ($stmt->execute()) {
    echo "Orden registrada correctamente con folio: $folio. <a href='ordenes_compra.php'>Regresar</a>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
