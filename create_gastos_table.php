<?php
$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'user';
$password   = getenv('DB_PASSWORD') ?: 'password';
$database   = getenv('DB_NAME') ?: 'database';

$conn = new mysqli($servername, $username, $password, $database);

$sql = "CREATE TABLE IF NOT EXISTS gastos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(20) UNIQUE,
  proveedor_id INT,
  monto DECIMAL(10,2) NOT NULL,
  fecha_pago DATE NOT NULL,
  unidad_negocio_id INT,
  tipo_gasto ENUM('Recurrente','Unico') DEFAULT 'Unico',
  tipo_compra ENUM('Venta','Administrativa','Operativo','Impuestos','Intereses/Créditos') DEFAULT NULL,
  medio_pago ENUM('Tarjeta','Transferencia','Efectivo') DEFAULT 'Transferencia',
  cuenta_bancaria VARCHAR(50),
  estatus ENUM('Pagado','Pago parcial','Vencido','Por pagar') DEFAULT 'Pagado',
  concepto TEXT,
  orden_folio VARCHAR(50),
  origen ENUM('Directo','Orden') DEFAULT 'Directo',
  origen_id VARCHAR(50),
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
  FOREIGN KEY (unidad_negocio_id) REFERENCES unidades_negocio(id)
);";

if ($conn->query($sql) === TRUE) {
    echo "Tabla creada";
} else {
    echo "Error: " . $conn->error;
}
?>