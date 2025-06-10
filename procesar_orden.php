<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$concepto = $_POST['concepto_pago'] ?? '';
$tipo_pago = $_POST['tipo_pago'] ?? '';
$factura = $_POST['genera_factura'] ?? 'No';
$usuario = $_POST['usuario_solicitante_id'] ?? null;
$unidad_negocio = $_POST['unidad_negocio_id'] ?? null;

// Generar folio único basado en la fecha y un número incremental
$fecha = date("Ymd"); // AñoMesDía
$result = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE DATE(fecha_creacion) = CURDATE()");
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
