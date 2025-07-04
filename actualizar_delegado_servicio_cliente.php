<?php
include 'auth.php';
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folio = $conn->real_escape_string($_POST['orden_id'] ?? '');
    $delegado_id = $conn->real_escape_string($_POST['usuario_delegado_id'] ?? '');

    if (!$folio) {
        echo "error";
        exit;
    }

    $campo = $delegado_id !== '' ? "'$delegado_id'" : "NULL";
    $query = "UPDATE ordenes_servicio_cliente SET usuario_delegado_id = $campo WHERE folio = '$folio'";
    
    if ($conn->query($query)) {
        echo "ok";
    } else {
        echo "error";
    }
}
?>