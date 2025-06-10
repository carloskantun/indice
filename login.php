<?php
session_start();

$servername = "localhost";
$username = "corazon_caribe";
$password = "Kantun.01*";
$database = "corazon_orderdecompras";

// Crear conexiÃ³n
$conn = new mysqli($servername, $username, $password, $database);

        $_SESSION['rol'] = $user['rol']; // Alias para compatibilidad
// Revisar conexiÃ³n
if ($conn->connect_error) {
    die("ConexiÃ³n fallida: " . $conn->connect_error);
}

// Obtener datos del formulario
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validar credenciales
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    // Verificar contraseÃ±a

if ($password === $user['password']) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol']; // Almacena el rol del usuario
        $_SESSION['puesto'] = $user['puesto']; // 7²2„1‚5 este es el que falta
        header("Location: menu_principal.php");
        echo "<pre>";
print_r($_SESSION);
exit;
        exit;
    } else {
        header("Location: index.php?error=Credenciales incorrectas");
        exit;
    }
} else {
    header("Location: index.php?error=Usuario no encontrado");
    exit;
}

echo "<pre>";
print_r($_SESSION);
exit;

