<?php
// 📌 Configuración de la Base de Datos
$servername = "localhost";
$username = "corazon_caribe";
$password = "Kantun.01*";
$database = "corazon_orderdecompras";

// 📌 Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// 📌 Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 📌 Forzar UTF-8 en la conexión
$conn->set_charset("utf8mb4");

// 📌 Asegurar que la comunicación con la base de datos use UTF-8
mysqli_query($conn, "SET NAMES 'utf8mb4'");
mysqli_query($conn, "SET CHARACTER SET utf8mb4");
mysqli_query($conn, "SET SESSION collation_connection = 'utf8mb4_unicode_ci'");

?>
