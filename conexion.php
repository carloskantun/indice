<?php
// Cargar variables de entorno desde .env si existe
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

//  Configuracion de la Base de Datos
$servername = $_ENV['DB_HOST'] ?? 'localhost';
$username   = $_ENV['DB_USER'] ?? 'user';
$password   = $_ENV['DB_PASSWORD'] ?? 'password';
$database   = $_ENV['DB_NAME'] ?? 'database';

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
