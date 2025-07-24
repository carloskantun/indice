<?php
include 'auth.php';
include 'conexion.php';

session_start();

if ($_SESSION['user_role'] !== 'superadmin') {
    exit('No autorizado');
}

$id         = intval($_POST['id']);
$proveedor  = intval($_POST['proveedor_id']);
$monto      = floatval($_POST['monto']);
$fecha      = $conn->real_escape_string($_POST['fecha_pago']);
$unidad     = intval($_POST['unidad_negocio_id']);
$concepto   = $conn->real_escape_string($_POST['concepto']);
$tipo_compra = $conn->real_escape_string($_POST['tipo_compra']);
$medio_pago  = $conn->real_escape_string($_POST['medio_pago']);
$cuenta_bancaria = $conn->real_escape_string($_POST['cuenta_bancaria']);

$sql = "UPDATE gastos 
        SET proveedor_id=?, monto=?, fecha_pago=?, unidad_negocio_id=?, concepto=?, tipo_compra=?, medio_pago=?, cuenta_bancaria=? 
        WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("idssssssi", $proveedor, $monto, $fecha, $unidad, $concepto, $tipo_compra, $medio_pago, $cuenta_bancaria, $id);

if ($stmt->execute()) {
    echo "ok";
} else {
    echo "Error al actualizar: " . $stmt->error;
}
