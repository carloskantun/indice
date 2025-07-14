<?php
include 'auth.php';
include 'conexion.php';

if ($_SESSION['user_role'] !== 'superadmin') {
    echo "Acceso no autorizado";
    exit;
}

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    echo "ID inválido.";
    exit;
}

// Eliminar archivos de comprobantes
$res = $conn->query("SELECT archivo_comprobante FROM abonos_gastos WHERE gasto_id = $id AND archivo_comprobante IS NOT NULL");
while ($row = $res->fetch_assoc()) {
    $archivo = $row['archivo_comprobante'];
    if ($archivo && file_exists(COMPROBANTES_DIR . "/$archivo")) {
        unlink(COMPROBANTES_DIR . "/$archivo");
    }
}

// Eliminar abonos
$conn->query("DELETE FROM abonos_gastos WHERE gasto_id = $id");

// Eliminar gasto
$conn->query("DELETE FROM gastos WHERE id = $id");

header("Location: gastos.php");
exit;