<?php
include 'conexion.php';

$folio = $_POST['folio'] ?? '';
$monto = $_POST['monto'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$comentario = $_POST['comentario'] ?? '';

if ($folio === '' || $monto === '' || $fecha === '') {
    echo 'error: Faltan datos';
    exit;
}

$comprobante_url = null;
if (!empty($_FILES['comprobante']['name'])) {
    $permitidos = ['jpg','jpeg','png','gif','pdf'];
    $ext = strtolower(pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION));
    $tmpName = $_FILES['comprobante']['tmp_name'];
    if (!in_array($ext, $permitidos)) {
        echo 'error: Archivo no permitido';
        exit;
    }

    if (in_array($ext, ['jpg','jpeg','png','gif'])) {
        $allowedTypes = ['image/jpeg','image/png','image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $mimeType = mime_content_type($tmpName);
        $size = $_FILES['comprobante']['size'];

        if (!in_array($mimeType, $allowedTypes)) {
            echo 'error: Formato de imagen no permitido';
            exit;
        }

        if ($size > $maxSize) {
            echo 'error: Imagen excede el tamaño máximo de 5MB';
            exit;
        }
    } else if ($_FILES['comprobante']['size'] > 5*1024*1024) {
        echo 'error: Archivo excede tamaño máximo';
        exit;
    }

    $nombre_archivo = uniqid('comprobante_') . '.' . $ext;
    $ruta = 'uploads/comprobantes/' . $nombre_archivo;
    if (!move_uploaded_file($tmpName, $ruta)) {
        echo 'error: No se pudo subir archivo';
        exit;
    }
    $comprobante_url = $ruta;
}

$stmt = $conn->prepare("INSERT INTO abonos_ordenes_compra (folio, monto, fecha, comentario, comprobante_url) VALUES (?, ?, ?, ?, ?)");
if(!$stmt){
    echo 'error: ' . $conn->error;
    exit;
}
$stmt->bind_param('sdsss', $folio, $monto, $fecha, $comentario, $comprobante_url);

if ($stmt->execute()) {
    echo 'ok';
} else {
    echo 'error: ' . $stmt->error;
}
exit;
?>
