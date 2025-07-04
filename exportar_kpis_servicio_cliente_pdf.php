<?php
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
include 'auth.php';
include 'conexion.php';

// Filtros
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

function obtener($sql, $conn) {
    return $conn->query($sql)->fetch_assoc()['total'] ?? 0;
}

$total        = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where", $conn);
$pendientes   = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Pendiente'", $conn);
$proceso      = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'En proceso'", $conn);
$terminados   = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Terminado'", $conn);
$cancelados   = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Cancelado'", $conn);
$vencidos     = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Vencido'", $conn);
$costo_total  = obtener("SELECT SUM(costo_final) AS total FROM ordenes_servicio_cliente $where", $conn);
$costo_prom   = obtener("SELECT AVG(costo_final) AS total FROM ordenes_servicio_cliente $where AND costo_final IS NOT NULL", $conn);
$prom_dias    = obtener("SELECT AVG(DATEDIFF(fecha_completado, fecha_reporte)) AS total FROM ordenes_servicio_cliente $where AND fecha_completado IS NOT NULL", $conn);

$mes = date('Y-m');
$mes_total = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mes'", $conn);
$mes_terminados = obtener("SELECT COUNT(*) AS total FROM ordenes_servicio_cliente $where AND estatus = 'Terminado' AND DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mes'", $conn);
$cumplimiento_mes = ($mes_total > 0) ? round(($mes_terminados / $mes_total) * 100, 1) : 0;

$html = "
<h2 style='text-align:center;'>📊 KPIs de Tareas</h2>
<table border='1' cellpadding='8' cellspacing='0' style='width:100%; font-family:sans-serif; font-size:14px;'>
<tr><th>Indicador</th><th>Valor</th></tr>
<tr><td>Solicitudes Totales</td><td>$total</td></tr>
<tr><td>En Espera</td><td>$pendientes</td></tr>
<tr><td>En Proceso</td><td>$proceso</td></tr>
<tr><td>Completadas</td><td>$terminados</td></tr>
<tr><td>Canceladas</td><td>$cancelados</td></tr>
<tr><td>Vencidas</td><td>$vencidos</td></tr>
<tr><td>Costo Total</td><td>$" . number_format($costo_total, 2) . "</td></tr>
<tr><td>Costo Promedio</td><td>$" . number_format($costo_prom, 2) . "</td></tr>
<tr><td>Promedio de Días</td><td>" . round($prom_dias, 1) . "</td></tr>
<tr><td>% Cumplimiento del Mes</td><td>$cumplimiento_mes%</td></tr>
</table>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("kpis_servicio_cliente_" . date("Ymd") . ".pdf", ["Attachment" => 0]);
exit;
