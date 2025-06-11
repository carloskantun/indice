<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folio = $_POST['folio'] ?? '';
    $fecha_completado = $_POST['fecha_completado'] ?? '';
    $detalle_completado = $_POST['detalle_completado'] ?? '';
    $costo_final = $_POST['costo_final'] ?? null;
    $foto_completado = null;

    // Preparar log
    $log = fopen("debug_completado.log", "a");
    fwrite($log, "\n[" . date("Y-m-d H:i:s") . "] Intento de completar orden $folio\n");
    fwrite($log, "POST: " . print_r($_POST, true));
    fwrite($log, "FILES: " . print_r($_FILES, true));

    if (empty($folio) || empty($fecha_completado) || empty($detalle_completado)) {
        fwrite($log, "❌ Error: faltan campos obligatorios.\n");
        fclose($log);
        echo "Error: faltan campos obligatorios.";
        exit;
    }

    // Procesar imagen si viene incluida
    if (!empty($_FILES['foto_completado']['name']) && $_FILES['foto_completado']['error'] === UPLOAD_ERR_OK) {
        $directorio = 'uploads/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
            fwrite($log, "ℹ️ Directorio creado: $directorio\n");
        }

        $tipo = mime_content_type($_FILES['foto_completado']['tmp_name']);
        $permitidos = ['image/jpeg', 'image/png'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($tipo, $permitidos)) {
            fwrite($log, "❌ Tipo no permitido: $tipo\n");
            fclose($log);
            echo "Error: tipo de archivo no permitido.";
            exit;
        }

        if ($_FILES['foto_completado']['size'] > $maxSize) {
            fwrite($log, "❌ Archivo demasiado grande: {$_FILES['foto_completado']['size']} bytes\n");
            fclose($log);
            echo "Error: archivo demasiado grande.";
            exit;
        }

        $ext = pathinfo($_FILES['foto_completado']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = "foto_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $folio) . "." . $ext;
        $rutaCompleta = $directorio . $nombreArchivo;

        if (move_uploaded_file($_FILES['foto_completado']['tmp_name'], $rutaCompleta)) {
            $foto_completado = $rutaCompleta;
            fwrite($log, "✅ Imagen guardada en $rutaCompleta\n");
        } else {
            fwrite($log, "❌ Error al mover la imagen a $rutaCompleta\n");
            fclose($log);
            echo "Error al subir la foto.";
            exit;
        }
    }

    // Preparar SQL
    if ($foto_completado) {
        $sql = "UPDATE ordenes_mantenimiento 
                SET fecha_completado = ?, 
                    detalle_completado = ?, 
                    foto_completado = ?, 
                    costo_final = ?, 
                    estatus = 'Terminado'
                WHERE folio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $fecha_completado, $detalle_completado, $foto_completado, $costo_final, $folio);
    } else {
        $sql = "UPDATE ordenes_mantenimiento 
                SET fecha_completado = ?, 
                    detalle_completado = ?, 
                    costo_final = ?, 
                    estatus = 'Terminado'
                WHERE folio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $fecha_completado, $detalle_completado, $costo_final, $folio);
    }

    // Ejecutar y cerrar
    if ($stmt && $stmt->execute()) {
        fwrite($log, "✅ Actualización exitosa en la base de datos.\n");
        echo "ok";
    } else {
        fwrite($log, "❌ Error SQL: " . $stmt->error . "\n");
        echo "Error al actualizar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    fclose($log);
} else {
    echo "Método no permitido.";
}
?>
