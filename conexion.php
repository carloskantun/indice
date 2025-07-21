<?php
//  Configuracion de la Base de Datos
$servername = "localhost";
$username   = "corazon_caribe";
$password   = "Kantun.01*";
$database   = "corazon_orderdecompras";

// Crear conexion
$conn = new mysqli($servername, $username, $password, $database);

// Validar conexion
if ($conn->connect_error) {
    die('Error de conexion a la base de datos: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
mysqli_query($conn, "SET NAMES 'utf8mb4'");
mysqli_query($conn, "SET CHARACTER SET utf8mb4");
mysqli_query($conn, "SET SESSION collation_connection = 'utf8mb4_unicode_ci'");

// Definir constantes de rutas
if (!defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', 'uploads');
}
if (!defined('COMPROBANTES_DIR')) {
    define('COMPROBANTES_DIR', UPLOADS_DIR . '/comprobantes');
}
?>
