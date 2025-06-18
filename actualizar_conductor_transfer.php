<?php
session_start();
include 'auth.php';
include 'conexion.php';

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin','superadmin','webmaster','ceo','supervisor operador'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orden_id = $_POST['orden_id'] ?? '';
    $conductor = $_POST['conductor'] ?? '';
    if (!empty($orden_id)) {
        $stmt = $conn->prepare("UPDATE ordenes_transfers SET conductor=? WHERE folio=?");
        $stmt->bind_param('ss', $conductor, $orden_id);
        echo $stmt->execute() ? 'ok' : 'error';
    } else {
        echo 'error';
    }
}
?>

