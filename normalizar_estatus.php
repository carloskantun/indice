<?php
include 'conexion.php';

$tablas = ['ordenes_mantenimiento', 'ordenes_servicio_cliente'];

foreach ($tablas as $tabla) {
    $conn->query("UPDATE $tabla SET estatus = 'Pendiente' WHERE estatus IS NULL OR TRIM(estatus) = ''");
    $conn->query("UPDATE $tabla SET estatus = 'En proceso' WHERE LOWER(TRIM(estatus)) IN ('en proceso','en_proceso','enproceso')");
    $conn->query("UPDATE $tabla SET estatus = 'Terminado' WHERE LOWER(TRIM(estatus)) IN ('terminado','term','completado','finalizado')");
    $conn->query("UPDATE $tabla SET estatus = 'Cancelado' WHERE LOWER(TRIM(estatus)) IN ('cancelado','cancelar','anulado','anulada')");
}

echo "Estatus normalizado.";
?>
