<?php
include 'conexion.php';

$directorio = 'uploads/';
$log = fopen("log_renombrado_fotos.txt", "a");

fwrite($log, "🛠 RENOMBRADO INICIADO — " . date("Y-m-d H:i:s") . "\n\n");

$query = "SELECT id, folio, foto, foto_completado FROM ordenes_mantenimiento";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $folio = preg_replace('/[^a-zA-Z0-9_-]/', '_', $row['folio']); // limpiar nombre para filename seguro

    $updates = [];
    $mensajes = [];

    // 🖼 FOTO REPORTE
    if (!empty($row['foto'])) {
        $pathOriginal = $row['foto'];
        $rutaOriginal = $_SERVER['DOCUMENT_ROOT'] . '/' . $pathOriginal;

        if (file_exists($rutaOriginal)) {
            $ext = pathinfo($rutaOriginal, PATHINFO_EXTENSION);
            $nuevoNombre = "foto_{$folio}_reporte." . $ext;
            $nuevaRuta = $directorio . $nuevoNombre;

            if (rename($rutaOriginal, $_SERVER['DOCUMENT_ROOT'] . '/' . $nuevaRuta)) {
                $updates[] = "foto = '$nuevaRuta'";
                $mensajes[] = "✔ Renombrado [REPORTE]: $pathOriginal → $nuevoNombre";
            } else {
                $mensajes[] = "❌ ERROR al renombrar [REPORTE]: $pathOriginal";
            }
        } else {
            $mensajes[] = "⚠ Archivo no encontrado [REPORTE]: $pathOriginal";
        }
    }

    // 🖼 FOTO FINAL
    if (!empty($row['foto_completado'])) {
        $pathOriginal = $row['foto_completado'];
        $rutaOriginal = $_SERVER['DOCUMENT_ROOT'] . '/' . $pathOriginal;

        if (file_exists($rutaOriginal)) {
            $ext = pathinfo($rutaOriginal, PATHINFO_EXTENSION);
            $nuevoNombre = "foto_{$folio}_final." . $ext;
            $nuevaRuta = $directorio . $nuevoNombre;

            if (rename($rutaOriginal, $_SERVER['DOCUMENT_ROOT'] . '/' . $nuevaRuta)) {
                $updates[] = "foto_completado = '$nuevaRuta'";
                $mensajes[] = "✔ Renombrado [FINAL]: $pathOriginal → $nuevoNombre";
            } else {
                $mensajes[] = "❌ ERROR al renombrar [FINAL]: $pathOriginal";
            }
        } else {
            $mensajes[] = "⚠ Archivo no encontrado [FINAL]: $pathOriginal";
        }
    }

    // 📝 Actualizar la base de datos
    if (!empty($updates)) {
        $updateQuery = "UPDATE ordenes_mantenimiento SET " . implode(', ', $updates) . " WHERE id = $id";
        $conn->query($updateQuery);
    }

    // 🖋 Escribir log
    fwrite($log, "📌 FOLIO: " . $row['folio'] . "\n");
    foreach ($mensajes as $m) {
        fwrite($log, "  - $m\n");
    }
    fwrite($log, "\n");
}

fwrite($log, "✅ FINALIZADO — " . date("Y-m-d H:i:s") . "\n\n");
fclose($log);

echo "Renombrado completado. Revisa el archivo log_renombrado_fotos.txt para los detalles.";
?>
