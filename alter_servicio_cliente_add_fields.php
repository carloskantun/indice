<?php
include 'conexion.php';

$sql1 = "ALTER TABLE ordenes_servicio_cliente ADD COLUMN IF NOT EXISTS fecha_vencimiento DATE AFTER fecha_reporte";
$sql2 = "ALTER TABLE ordenes_servicio_cliente ADD COLUMN IF NOT EXISTS delegar_usuario_id INT AFTER usuario_solicitante_id";
$sql3 = "ALTER TABLE ordenes_servicio_cliente
          ADD CONSTRAINT IF NOT EXISTS fk_delegar_usuario
          FOREIGN KEY (delegar_usuario_id) REFERENCES usuarios(id)";

if ($conn->query($sql1) === TRUE) {
    echo "Columna fecha_vencimiento agregada\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
if ($conn->query($sql2) === TRUE) {
    echo "Columna delegar_usuario_id agregada\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
@$conn->query($sql3);
?>
