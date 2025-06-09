<?php
include 'auth.php';
include 'conexion.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=kpis_servicio_cliente.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Indicador', 'Valor']);

// Mismos filtros
$where = "WHERE 1=1";
if (!empty($_GET['alojamiento'])) {
    $ids = implode(',', array_map('intval', $_GET['alojamiento']));
    $where .= " AND alojamiento_id IN ($ids)";
}
if (!empty($_GET['unidad_negocio'])) {
    $ids = implode(',', array_map('intval', $_GET['unidad_negocio']));
    $where .= " AND unidad_negocio_id IN ($ids)";
}
if (!empty($_GET['fecha_inicio'])) {
    $fecha = $conn->real_escape_string($_GET['fecha_inicio']);
    $where .= " AND fecha_reporte >= '$fecha'";
}
if (!empty($_GET['fecha_fin'])) {
    $fecha = $conn->real_escape_string($_GET['fecha_fin']);
    $where .= " AND fecha_reporte <= '$fecha'";
}

function obtener($query, $conn) {
    return $conn->query($query)->fetch_assoc()['total'] ?? 0;
}

$total         = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where", $conn);
$pendientes    = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Pendiente'", $conn);
$proceso       = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'En proceso'", $conn);
$terminados    = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Terminado'", $conn);
$cancelados    = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Cancelado'", $conn);
$vencidos      = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Vencido'", $conn);
$costo_total   = obtener("SELECT SUM(costo_final) AS total FROM ordenes_servicio_cliente $where", $conn);
$costo_prom    = obtener("SELECT AVG(costo_final) AS total FROM ordenes_servicio_cliente $where AND costo_final IS NOT NULL", $conn);
$prom_dias     = obtener("SELECT AVG(DATEDIFF(fecha_completado, fecha_reporte)) AS total FROM ordenes_servicio_cliente $where AND fecha_completado IS NOT NULL", $conn);

$mes = date('Y-m');
$mes_total      = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mes'", $conn);
$mes_terminados = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND estatus = 'Terminado' AND DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mes'", $conn);
$porcentaje_cumplido = ($mes_total > 0) ? round(($mes_terminados / $mes_total) * 100, 1) : 0;

fputcsv($output, ['Solicitudes Totales', $total]);
fputcsv($output, ['En Espera', $pendientes]);
fputcsv($output, ['En Proceso', $proceso]);
fputcsv($output, ['Completadas', $terminados]);
fputcsv($output, ['Canceladas', $cancelados]);
fputcsv($output, ['Vencidas', $vencidos]);
fputcsv($output, ['Costo Total', '$' . number_format($costo_total, 2)]);
fputcsv($output, ['Costo Promedio', '$' . number_format($costo_prom, 2)]);
fputcsv($output, ['Promedio de Días', $prom_dias]);
fputcsv($output, ['% Cumplimiento del Mes', $porcentaje_cumplido . '%']);

fclose($output);
exit;
