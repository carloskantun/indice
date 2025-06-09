<?php
include 'conexion.php';

$tables = ['ordenes_mantenimiento', 'ordenes_servicio_cliente'];

foreach ($tables as $table) {
    $sql = "ALTER TABLE $table MODIFY estatus ENUM('Pendiente', 'En proceso', 'Terminado', 'Cancelado') DEFAULT 'Pendiente'";
    if ($conn->query($sql) === TRUE) {
        echo "Tabla $table modificada\n";
    } else {
        echo "Error actualizando $table: " . $conn->error . "\n";
    }
}
?>
