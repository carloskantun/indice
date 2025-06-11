<?php
session_start();
include 'auth.php';
include 'conexion.php';

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orden_id = $_POST['orden_id'] ?? '';
    $nivel = $_POST['nivel'] ?? '';

    if (!empty($orden_id) && !empty($nivel)) {
        $stmt = $conn->prepare("UPDATE ordenes_mantenimiento SET nivel = ? WHERE folio = ?");
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
