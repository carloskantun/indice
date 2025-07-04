<?php
include 'auth.php';
include 'conexion.php';

$folio = $conn->real_escape_string($_POST['orden_id']);
$usuario_id = (int) $_POST['usuario_id'];

if (!$folio || !$usuario_id) {
    echo "error";
    exit;
}

$update = $conn->prepare("UPDATE ordenes_servicio_cliente SET usuario_solicitante_id = ? WHERE folio = ?");
$update->bind_param('is', $usuario_id, $folio);

if ($update->execute()) {
    echo "ok";
} else {
    echo "error";
}
?>
