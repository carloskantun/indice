<?php
session_start();
include 'auth.php';
include 'conexion.php';

// Solo admin y superadmin pueden cambiar "Quién Pagó"
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'superadmin' && $_SESSION['user_role'] !== 'admin')) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orden_id = $_POST['orden_id'] ?? '';
    $quien_pago_id = $_POST['quien_pago_id'] ?? '';

    if (!empty($orden_id)) {
        $stmt = $conn->prepare("UPDATE ordenes_compra SET quien_pago_id = ? WHERE folio = ?");
        $stmt->bind_param("ss", $quien_pago_id, $orden_id);
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
