<?php
include '../../conexion.php';
session_start();

$nota_id = $_POST['nota_id'] ?? null;
$monto = $_POST['monto'] ?? 0;
$comentario = $_POST['comentario'] ?? '';
$usuario_id = $_SESSION['user_id'] ?? 0;

if (!$nota_id || !$monto || !$usuario_id) {
    echo 'Faltan datos obligatorios';
    exit;
}

$nota_id = intval($nota_id);
$monto = floatval($monto);
$comentario = $conn->real_escape_string($comentario);

// Manejo de archivo
$nombre_archivo = null;
if (isset($_FILES['archivo_comprobante']) && $_FILES['archivo_comprobante']['error'] === UPLOAD_ERR_OK) {
    $nombre_tmp = $_FILES['archivo_comprobante']['tmp_name'];
    $nombre_original = basename($_FILES['archivo_comprobante']['name']);
    $ext = pathinfo($nombre_original, PATHINFO_EXTENSION);
    $nuevo_nombre = 'abono_nota_' . time() . '_' . rand(1000,9999) . '.' . $ext;
    $ruta_destino = '../uploads/comprobantes/' . $nuevo_nombre;

    if (move_uploaded_file($nombre_tmp, $ruta_destino)) {
        $nombre_archivo = 'uploads/comprobantes/' . $nuevo_nombre; // para guardar en BD
    } else {
        echo 'Error al subir archivo';
        exit;
    }
}

// Insertar abono
$sql = "INSERT INTO abonos_notas_credito 
        (nota_credito_id, monto, comentario, usuario_id, archivo_comprobante) 
        VALUES 
        ($nota_id, $monto, '$comentario', $usuario_id, " . ($nombre_archivo ? "'$nombre_archivo'" : "NULL") . ")";

if ($conn->query($sql)) {
    echo 'ok';
} else {
    echo 'Error al guardar abono';
}
