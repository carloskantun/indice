<?php
include 'conexion.php'; // Conexión centralizada

// Validar datos del formulario
$alojamiento_id = $_POST['alojamiento_id'] ?? '';
$descripcion = $_POST['descripcion_reporte'] ?? '';
$fecha_reporte = $_POST['fecha_reporte'] ?? '';
$estatus = isset($_POST['estatus']) && trim($_POST['estatus']) !== '' ? $_POST['estatus'] : 'Pendiente';
$usuario_id = $_POST['usuario_solicitante_id'] ?? '';
$unidad_id = $_POST['unidad_negocio_id'] ?? '';
$notas = $_POST['notas'] ?? '';
$foto_url = null;

// Validar campos obligatorios
if (empty($alojamiento_id) || empty($descripcion) || empty($fecha_reporte)) {
    die("Error: Todos los campos obligatorios deben completarse.");
}

// Procesar imagen si se subió
if (!empty($_FILES['foto']['name'])) {
    $targetDir = "uploads/"; // Asegúrate de que esta carpeta exista y tenga permisos
    $fileName = basename($_FILES["foto"]["name"]);
    $targetFilePath = $targetDir . uniqid() . "_" . $fileName;

    if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath)) {
        $foto_url = $targetFilePath;
    } else {
        die("Error al subir la foto.");
    }
}

// Paso 1: Insertar SIN folio
$sql = "INSERT INTO ordenes_servicio_cliente 
    (alojamiento_id, descripcion_reporte, foto, fecha_reporte, estatus, usuario_solicitante_id, unidad_negocio_id, notas) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isssssis", $alojamiento_id, $descripcion, $foto_url, $fecha_reporte, $estatus, $usuario_id, $unidad_id, $notas);

if ($stmt->execute()) {
    $id_insertado = $stmt->insert_id;

    // Paso 2: Generar folio con el ID
    $folio = date('ym') . '-' . str_pad($id_insertado, 4, '0', STR_PAD_LEFT);  // Ej: 2504-0012

    // Paso 3: Actualizar el folio
    $update = $conn->prepare("UPDATE ordenes_servicio_cliente SET folio = ? WHERE id = ?");
    $update->bind_param("si", $folio, $id_insertado);
    $update->execute();

    echo "✅ Reporte registrado con folio <strong>$folio</strong>. <a href='minipanel_servicio_cliente.php'>Regresar</a>";
} else {
    echo "❌ Error al registrar el reporte: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
