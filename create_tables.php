<?php
// database/migrations/create_proveedores_table.php
$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'user';
$password   = getenv('DB_PASSWORD') ?: 'password';
$database   = getenv('DB_NAME') ?: 'database';

$conn = new mysqli($servername, $username, $password, $database);

// Proveedores Table
$conn->query("
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    clabe_interbancaria VARCHAR(18),
    numero_cuenta VARCHAR(20),
    banco VARCHAR(50),
    direccion TEXT,
    rfc VARCHAR(13),
    descripcion_servicio TEXT
);
");

// Usuarios Table
$conn->query("
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(255),
    -- Permitir guardar multiples puestos separados por coma
    puesto TEXT
);
");

// Unidades de Negocio Table
$conn->query("
CREATE TABLE IF NOT EXISTS unidades_negocio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);
");

// Ordenes de Compra Table
$conn->query("
CREATE TABLE IF NOT EXISTS ordenes_compra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    vencimiento_pago DATE NOT NULL,
    concepto_pago TEXT,
    tipo_pago ENUM('Recurrente Mensual', 'Recurrente Semanal', 'Recurrente Quincenal', 'Pago Único', 'Nota de Crédito') NOT NULL,
    genera_factura BOOLEAN NOT NULL,
    usuario_solicitante_id INT NOT NULL,
    unidad_negocio_id INT NOT NULL,
    folio VARCHAR(50) NOT NULL UNIQUE,
    estatus_pago ENUM('Por pagar', 'Pagado', 'Vencido', 'Pago parcial', 'Cancelado', 'Nota de crédito abierta') NOT NULL,
    quien_pago_id INT,
    notas TEXT,
    comprobante_path VARCHAR(255),
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
    FOREIGN KEY (usuario_solicitante_id) REFERENCES usuarios(id),
    FOREIGN KEY (unidad_negocio_id) REFERENCES unidades_negocio(id)
);
");
echo "Tablas creadas exitosamente";
?>
