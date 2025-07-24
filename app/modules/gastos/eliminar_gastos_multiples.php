<?php
session_start();
include 'auth.php';
include 'conexion.php';

if ($_SESSION['user_role'] !== 'superadmin') {
    exit('No autorizado');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids']) && is_array($_POST['ids'])) {
    $ids = array_map('intval', $_POST['ids']);
    $ids_str = implode(',', $ids);

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Eliminar abonos relacionados
        $conn->query("DELETE FROM abonos_gastos WHERE gasto_id IN ($ids_str)");

        // Eliminar los gastos
        $conn->query("DELETE FROM gastos WHERE id IN ($ids_str)");

        // Confirmar cambios
        $conn->commit();
        echo 'ok';
    } catch (Exception $e) {
        $conn->rollback();
        echo 'Error al eliminar: ' . $e->getMessage();
    }
} else {
    echo 'Error en la solicitud';
}