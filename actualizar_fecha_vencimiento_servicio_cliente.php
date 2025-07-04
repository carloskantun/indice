<?php
include 'auth.php';
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folio = $conn->real_escape_string($_POST['orden_id'] ?? '');
    $fecha_vencimiento = $conn->real_escape_string($_POST['fecha_vencimiento'] ?? '');

    if (!$folio || !$fecha_vencimiento) {
        echo "error";
        exit;
    }

    $query = "UPDATE ordenes_servicio_cliente SET fecha_vencimiento = '$fecha_vencimiento' WHERE folio = '$folio'";
    if ($conn->query($query)) {
        echo "ok";
    } else {
        echo "error";
    }
}
?>