<?php
session_start();
include 'auth.php';
include 'conexion.php';

if ($_SESSION['user_role'] !== 'superadmin') {
    http_response_code(403);
    echo 'No autorizado';
    exit;
}

// Validar entrada
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$campo = $_POST['campo'] ?? '';
$valor = $_POST['valor'] ?? '';

$campos_permitidos = ['medio_pago', 'cuenta_bancaria', 'concepto', 'tipo_compra', 'estatus'];

if (!$id || !in_array($campo, $campos_permitidos)) {
    http_response_code(400);
    echo 'Solicitud inválida';
    exit;
}

$valor_escapado = $conn->real_escape_string($valor);

// Ejecutar actualización
$sql = "UPDATE gastos SET $campo = '$valor_escapado' WHERE id = $id";
if ($conn->query($sql)) {
    echo 'ok';
} else {
    http_response_code(500);
    echo 'Error al actualizar';
}
