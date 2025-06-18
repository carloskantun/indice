<?php
include 'conexion.php';

$folio = $_POST['orden_id'] ?? '';
$ponderacion = intval($_POST['ponderacion'] ?? 1);

if ($folio !== '') {
    $stmt = $conn->prepare("UPDATE ordenes_mantenimiento SET ponderacion = ? WHERE folio = ?");
    $stmt->bind_param("is", $ponderacion, $folio);

    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "error";
    }

    $stmt->close();
} else {
    echo "error";
}

$conn->close();
?>