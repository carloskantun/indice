<?php
$servername = "localhost";
$username = "corazon_caribe";
$password = "Kantun.01*";
$database = "corazon_orderdecompras";

$conn = new mysqli($servername, $username, $password, $database);

$sql = "CREATE TABLE IF NOT EXISTS ordenes_transfers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(20) UNIQUE,
  tipo_servicio ENUM('Llegada','Salida','Roundtrip') NOT NULL,
  fecha_servicio DATE NOT NULL,
  pickup TIME NOT NULL,
  hotel_pickup TEXT NOT NULL,
  direccion TEXT,
  nombre_pasajeros TEXT NOT NULL,
  num_pasajeros INT NOT NULL,
  habitacion VARCHAR(50),
  observaciones TEXT,
  vehiculo VARCHAR(100),
  placas VARCHAR(20),
  numero_economico VARCHAR(50),
  conductor VARCHAR(100),
  agencia VARCHAR(100),
  estatus ENUM('Pendiente', 'Realizado', 'Cancelado') DEFAULT 'Pendiente',
  usuario_solicitante_id INT,
  unidad_negocio_id INT,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

if ($conn->query($sql) === TRUE) {
    echo "Tabla creada";
} else {
    echo "Error: " . $conn->error;
}
?>

