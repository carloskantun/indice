<?php
function registrar_actividad($conn, $usuario_id, $accion) {
    if (!$conn) {
        die("Conexión a la base de datos no válida.");
    }

    $stmt = $conn->prepare("INSERT INTO registro_actividad (usuario_id, accion) VALUES (?, ?)");
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("is", $usuario_id, $accion);
    $stmt->execute();
    $stmt->close();
}
?>
