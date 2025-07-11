<?php
$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'user';
$password   = getenv('DB_PASSWORD') ?: 'password';
$database   = getenv('DB_NAME') ?: 'database';

$conn = new mysqli($servername, $username, $password, $database);

$sql = "CREATE TABLE IF NOT EXISTS ordenes_lavanderia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(20) UNIQUE,
  fecha DATE NOT NULL,
  cliente VARCHAR(100) NOT NULL,
  servicio VARCHAR(100) NOT NULL,
  prenda VARCHAR(100) NOT NULL,
  cantidad INT DEFAULT 1,
  monto DECIMAL(10,2) DEFAULT 0,
  estatus ENUM('Pendiente','En proceso','Terminado','Cancelado') DEFAULT 'Pendiente',
  usuario_creador_id INT,
  unidad_negocio_id INT,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

if ($conn->query($sql) === TRUE) {
    echo "Tabla creada";
} else {
    echo "Error: " . $conn->error;
}
?>
