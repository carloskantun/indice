<?php
session_start();
include 'auth.php';
include 'conexion.php';

// Solo admin y superadmin pueden cambiar el estatus
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'superadmin' && $_SESSION['user_role'] !== 'admin')) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orden_id = $_POST['orden_id'] ?? '';
    $estatus_pago = $_POST['estatus_pago'] ?? '';

    if (!empty($orden_id) && !empty($estatus_pago)) {
        // Asegurar que la consulta solo afecta la orden correcta
        $stmt = $conn->prepare("UPDATE ordenes_compra SET estatus_pago = ? WHERE folio = ?");
        $stmt->bind_param("ss", $estatus_pago, $orden_id); // Folio es VARCHAR, por eso "ss"
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
