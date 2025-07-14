<?php
// Database configuration
$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'user';
$password   = getenv('DB_PASSWORD') ?: 'password';
$database   = getenv('DB_NAME') ?: 'database';

// Create connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
mysqli_query($conn, "SET NAMES 'utf8mb4'");
mysqli_query($conn, "SET CHARACTER SET utf8mb4");
mysqli_query($conn, "SET SESSION collation_connection = 'utf8mb4_unicode_ci'");


if ($conn->connect_error) {
if (!defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', 'uploads');
}
if (!defined('COMPROBANTES_DIR')) {
    define('COMPROBANTES_DIR', UPLOADS_DIR . '/comprobantes');
}
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

?>
