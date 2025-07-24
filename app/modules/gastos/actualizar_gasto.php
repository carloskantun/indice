<?php
session_start();
include 'auth.php';
include 'conexion.php';

if ($_SESSION['user_role'] !== 'superadmin') {
    exit('No autorizado');
}

$id = $_POST['id'] ?? null;

$campos = [
    'proveedor_id', 'monto', 'fecha_pago', 'unidad_negocio_id', 
    'tipo_gasto', 'medio_pago', 'cuenta_bancaria', 
    'concepto', 'tipo_compra', 'estatus'
];

$valores = [];
$placeholders = [];
$tipos = '';

foreach ($campos as $campo) {
    if (!isset($_POST[$campo])) continue;
    $valores[] = $_POST[$campo];
    $placeholders[] = "$campo = ?";
    $tipos .= is_numeric($_POST[$campo]) ? 'i' : 's';
}

$valores[] = $id;
$tipos .= 'i';

$sql = "UPDATE gastos SET " . implode(', ', $placeholders) . " WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($tipos, ...$valores);
if ($stmt->execute()) {
    echo 'ok';
} else {
    echo 'Error: ' . $conn->error;
}
