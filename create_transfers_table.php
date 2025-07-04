<?php
$servername = "localhost";
$username   = "corazon_caribe";
$password   = "Kantun.01*";
$database   = "corazon_orderdecompras";

$conn = new mysqli($servername, $username, $password, $database);

$sql = "CREATE TABLE IF NOT EXISTS ordenes_transfers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(20) UNIQUE,
  tipo ENUM('Llegada','Salida','Roundtrip') NOT NULL,
  fecha DATE NOT NULL,
  pickup TIME NOT NULL,
  hotel VARCHAR(100) NOT NULL,
  pasajeros INT NOT NULL,
  numero_reserva VARCHAR(50),
  vehiculo VARCHAR(100),
  conductor VARCHAR(100),
  agencia VARCHAR(100),
  estatus ENUM('Pendiente', 'En proceso', 'Terminado', 'Cancelado') DEFAULT 'Pendiente',
  usuario_creador_id INT,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

if ($conn->query($sql) === TRUE) {
    echo "Tabla creada";
} else {
    echo "Error: " . $conn->error;
}
?>
