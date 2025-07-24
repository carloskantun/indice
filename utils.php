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

/**
 * Registra un mensaje de error en un archivo y en el log de PHP
 */
function log_error(string $message, string $file = 'app_error.log'): void {
    $entry = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
    error_log($entry, 3, $file);
    error_log($message);
}
?>
