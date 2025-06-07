<?php
session_start();
include 'auth.php';
include 'conexion.php';

// Solo admin y superadmin pueden cambiar el nivel
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'superadmin' && $_SESSION['user_role'] !== 'admin')) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orden_id = $_POST['orden_id'] ?? '';
    $nivel = $_POST['nivel'] ?? '';

    if (!empty($orden_id) && !empty($nivel)) {
        $stmt = $conn->prepare("UPDATE ordenes_compra SET nivel = ? WHERE folio = ?");
        $stmt->bind_param("ss", $nivel, $orden_id);
        if ($stmt->execute()) {
            echo "ok";
        } else {
            echo "error";
        }
    } else {
        echo "error";
    }
}
?>
