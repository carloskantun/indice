<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orden_id = $_POST['orden_id'] ?? null;
    $quien_realizo_id = $_POST['quien_realizo_id'] ?? null;

    if ($orden_id !== null) {
        $stmt = $conn->prepare("UPDATE ordenes_servicio_cliente SET quien_realizo_id = ? WHERE folio = ?");
        $stmt->bind_param("is", $quien_realizo_id, $orden_id);
        if ($stmt->execute()) {
            echo "ok";
        } else {
            echo "error";
        }
        $stmt->close();
    } else {
        echo "invalido";
    }
}
?>
