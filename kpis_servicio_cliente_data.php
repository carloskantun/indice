<?php
include 'auth.php';
include 'conexion.php';
header('Content-Type: application/json');

// Obtener fechas
$fecha_inicio = !empty($_GET['fecha_inicio']) ? $conn->real_escape_string($_GET['fecha_inicio']) : '';
$fecha_fin    = !empty($_GET['fecha_fin']) ? $conn->real_escape_string($_GET['fecha_fin']) : '';

// Función para obtener valores
function obtener($sql, $conn) {
    $res = $conn->query($sql);
    return $res ? ($res->fetch_assoc()['total'] ?? 0) : 0;
}

// Filtros base
$where_total = "WHERE 1=1";
$where_reporte = "WHERE 1=1";
$where_completado = "WHERE estatus = 'Terminado' AND fecha_completado IS NOT NULL";

// Alojamiento
if (!empty($_GET['alojamiento'])) {
    $ids = implode(',', array_map('intval', $_GET['alojamiento']));
    $where_total .= " AND alojamiento_id IN ($ids)";
    $where_reporte .= " AND alojamiento_id IN ($ids)";
    $where_completado .= " AND alojamiento_id IN ($ids)";
}

// Unidad de negocio
if (!empty($_GET['unidad_negocio'])) {
    $ids = implode(',', array_map('intval', $_GET['unidad_negocio']));
    $where_total .= " AND unidad_negocio_id IN ($ids)";
    $where_reporte .= " AND unidad_negocio_id IN ($ids)";
    $where_completado .= " AND unidad_negocio_id IN ($ids)";
}

// ✅ Filtros de fechas aplicados correctamente
if ($fecha_inicio && $fecha_fin) {
    $where_total .= " AND (
        (fecha_reporte BETWEEN '$fecha_inicio' AND '$fecha_fin') OR
        (fecha_completado BETWEEN '$fecha_inicio' AND '$fecha_fin')
    )";
    $where_reporte .= " AND fecha_reporte BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    $where_completado .= " AND fecha_completado BETWEEN '$fecha_inicio' AND '$fecha_fin'";
} elseif ($fecha_inicio) {
    $where_total .= " AND (
        fecha_reporte >= '$fecha_inicio' OR fecha_completado >= '$fecha_inicio'
    )";
    $where_reporte .= " AND fecha_reporte >= '$fecha_inicio'";
    $where_completado .= " AND fecha_completado >= '$fecha_inicio'";
} elseif ($fecha_fin) {
    $where_total .= " AND (
        fecha_reporte <= '$fecha_fin' OR fecha_completado <= '$fecha_fin'
    )";
    $where_reporte .= " AND fecha_reporte <= '$fecha_fin'";
    $where_completado .= " AND fecha_completado <= '$fecha_fin'";
}

// KPIs operativos
$total        = obtener("SELECT COUNT(*) FROM ordenes_servicio_cliente $where_total", $conn);
$pendientes   = obtener("SELECT COUNT(*) FROM ordenes_servicio_cliente $where_reporte AND (estatus = 'Pendiente' OR estatus IS NULL OR TRIM(estatus) = '')", $conn);
$proceso      = obtener("SELECT COUNT(*) FROM ordenes_servicio_cliente $where_reporte AND estatus = 'En proceso'", $conn);
$cancelados   = obtener("SELECT COUNT(*) FROM ordenes_servicio_cliente $where_reporte AND estatus = 'Cancelado'", $conn);
$vencidos     = obtener("SELECT COUNT(*) FROM ordenes_servicio_cliente $where_reporte AND estatus = 'Vencido'", $conn);
$terminados   = obtener("SELECT COUNT(*) FROM ordenes_servicio_cliente $where_completado", $conn);

// JSON de respuesta
echo json_encode([
    'total' => (int)$total,
    'pendientes' => (int)$pendientes,
    'en_proceso' => (int)$proceso,
    'cancelados' => (int)$cancelados,
    'vencidos' => (int)$vencidos,
    'terminados' => (int)$terminados
]);
?>
