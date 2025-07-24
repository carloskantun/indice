<?php
include 'auth.php';
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folio = $_POST['orden_id'] ?? '';
    $delegado_id = $_POST['usuario_delegado_id'] ?? '';

    if (!$folio) {
        echo "error";
        exit;
    }

    if ($delegado_id !== '') {
        $sql = "UPDATE ordenes_servicio_cliente SET usuario_delegado_id = ? WHERE folio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            log_error('Error al preparar consulta en actualizar_delegado_servicio_cliente: ' . $conn->error);
            echo 'error';
            exit;
        }
        $stmt->bind_param('is', $delegado_id, $folio);
    } else {
        $sql = "UPDATE ordenes_servicio_cliente SET usuario_delegado_id = NULL WHERE folio = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            log_error('Error al preparar consulta en actualizar_delegado_servicio_cliente: ' . $conn->error);
            echo 'error';
            exit;
        }
        $stmt->bind_param('s', $folio);
    }

    if ($stmt->execute()) {
        echo 'ok';
    } else {
        log_error('Error al ejecutar consulta en actualizar_delegado_servicio_cliente: ' . $stmt->error);
        echo 'error';
    }
    $stmt->close();
}
?>