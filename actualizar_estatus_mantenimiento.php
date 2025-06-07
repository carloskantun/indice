<?php
session_start();
include 'auth.php';
include 'conexion.php';

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orden_id = $_POST['orden_id'] ?? '';
    $estatus = $_POST['estatus'] ?? '';

    if (!empty($orden_id) && !empty($estatus)) {
        $stmt = $conn->prepare("UPDATE ordenes_mantenimiento SET estatus = ? WHERE folio = ?");
        $stmt->bind_param("ss", $estatus, $orden_id);
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
