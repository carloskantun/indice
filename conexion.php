<?php
// �9�8 Configuraci��n de la Base de Datos
$servername = "localhost";
$username = "corazon_caribe";
$password = "Kantun.01*";
$database = "corazon_orderdecompras";

// �9�8 Crear conexi��n
$conn = new mysqli($servername, $username, $password, $database);

// �9�8 Verificar conexi��n
if ($conn->connect_error) {
    die("Error de conexi��n: " . $conn->connect_error);
}

// �9�8 Forzar UTF-8 en la conexi��n
$conn->set_charset("utf8mb4");

// �9�8 Asegurar que la comunicaci��n con la base de datos use UTF-8
mysqli_query($conn, "SET NAMES 'utf8mb4'");
mysqli_query($conn, "SET CHARACTER SET utf8mb4");
mysqli_query($conn, "SET SESSION collation_connection = 'utf8mb4_unicode_ci'");

?>
