<?php
include '../../conexion.php';

$id = $_POST['id'] ?? null;
$unidad = $_POST['unidad_negocio_id'] ?? '';
$usuario = $_POST['usuario_responsable_id'] ?? '';
$fecha = $_POST['fecha_nota'] ?? '';
$monto = $_POST['monto'] ?? '';
$concepto = $_POST['concepto'] ?? '';
$estatus = $_POST['estatus'] ?? '';

if (!$unidad || !$usuario || !$fecha || !$monto || !$concepto) {
    echo 'Faltan campos obligatorios';
    exit;
}

// Seguridad básica
$unidad = intval($unidad);
$usuario = intval($usuario);
$fecha = $conn->real_escape_string($fecha);
$concepto = $conn->real_escape_string($concepto);
$estatus = $conn->real_escape_string($estatus);
$monto = floatval($monto);

// Actualizar
if ($id) {
    $id = intval($id);
    $sql = "UPDATE notas_credito SET 
                unidad_negocio_id = $unidad,
                usuario_responsable_id = $usuario,
                fecha_nota = '$fecha',
                monto = $monto,
                concepto = '$concepto',
                estatus = '$estatus'
            WHERE id = $id";
    if ($conn->query($sql)) {
        echo 'ok';
    } else {
        echo 'Error al actualizar';
    }
    exit;
}

// Insertar
$sql = "INSERT INTO notas_credito (unidad_negocio_id, usuario_responsable_id, fecha_nota, monto, concepto, estatus) 
        VALUES ($unidad, $usuario, '$fecha', $monto, '$concepto', '$estatus')";

if ($conn->query($sql)) {
    echo 'ok';
} else {
    echo 'Error al insertar';
}
