<?php
include 'conexion.php';

$compra_id = $_POST['compra_id'] ?? null;
$fecha = $_POST['fecha_nota'] ?? '';
$monto = $_POST['monto_nota'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$archivo_nombre = null;

if (empty($compra_id) || empty($fecha) || empty($monto) || empty($motivo)) {
    echo "Faltan campos.";
    exit;
}

// Generar folio tipo NC-2025-0001
$anio = date('Y');
$prefix = "NC-$anio-";
$count = $conn->query("SELECT COUNT(*) AS total FROM notas_credito WHERE folio LIKE '$prefix%'")->fetch_assoc()['total'] + 1;
$folio = $prefix . str_pad($count, 4, "0", STR_PAD_LEFT);

// Procesar archivo adjunto si existe
if (!empty($_FILES['archivo']['name']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $permitidos = ['pdf', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $permitidos)) {
        echo "Formato de archivo no permitido.";
        exit;
    }

    $nombre_limpio = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($_FILES['archivo']['name']));
    $archivo_nombre = "uploads/nota_{$folio}_{$nombre_limpio}";

    $destino = $_SERVER['DOCUMENT_ROOT'] . '/' . $archivo_nombre;
    if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
        echo "Error al subir archivo.";
        exit;
    }
}

// Insertar en la base de datos
$stmt = $conn->prepare("INSERT INTO notas_credito 
    (folio, compra_id, fecha_nota, monto, motivo, archivo_adjunto) 
    VALUES (?, ?, ?, ?, ?, ?)");

$stmt->bind_param("sisdss", $folio, $compra_id, $fecha, $monto, $motivo, $archivo_nombre);

if ($stmt->execute()) {
    echo "ok";
} else {
    echo "Error al guardar: " . $stmt->error;
}