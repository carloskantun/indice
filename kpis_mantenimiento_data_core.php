<?php
if (!isset($conn)) include 'conexion.php';

// Filtros GET simulados si se usa internamente
$_GET = $_GET ?? [];

// 🧠 Filtros dinámicos
$where = "WHERE 1=1";
if (!empty($_GET['alojamiento'])) {
    $ids = implode(',', array_map('intval', $_GET['alojamiento']));
    $where .= " AND alojamiento_id IN ($ids)";
}
if (!empty($_GET['unidad_negocio'])) {
    $ids = implode(',', array_map('intval', $_GET['unidad_negocio']));
    $where .= " AND unidad_negocio_id IN ($ids)";
}

$fecha_inicio = !empty($_GET['fecha_inicio']) ? $conn->real_escape_string($_GET['fecha_inicio']) : '';
$fecha_fin    = !empty($_GET['fecha_fin'])    ? $conn->real_escape_string($_GET['fecha_fin']) : '';

if ($fecha_inicio) $where .= " AND fecha_reporte >= '$fecha_inicio'";
if ($fecha_fin)    $where .= " AND fecha_reporte <= '$fecha_fin'";

// Función base
function obtener($sql, $conn) {
    return $conn->query($sql)->fetch_assoc()['total'] ?? 0;
}

// KPIs básicos
$total      = obtener("SELECT COUNT(*) AS total FROM ordenes_mantenimiento $where", $conn);
$pendientes = obtener("SELECT COUNT(*) AS total FROM ordenes_mantenimiento $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Pendiente'", $conn);
$proceso    = obtener("SELECT COUNT(*) AS total FROM ordenes_mantenimiento $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'En proceso'", $conn);
$terminados = obtener("SELECT COUNT(*) AS total FROM ordenes_mantenimiento $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Terminado'", $conn);
$cancelados = obtener("SELECT COUNT(*) AS total FROM ordenes_mantenimiento $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Cancelado'", $conn);
$vencidos   = obtener("SELECT COUNT(*) AS total FROM ordenes_mantenimiento $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Vencido'", $conn);

$costo_total = obtener("SELECT SUM(costo_final) AS total FROM ordenes_mantenimiento $where", $conn);
$costo_prom  = obtener("SELECT AVG(costo_final) AS total FROM ordenes_mantenimiento $where AND costo_final IS NOT NULL", $conn);
$prom_dias   = obtener("SELECT AVG(DATEDIFF(fecha_completado, fecha_reporte)) AS total FROM ordenes_mantenimiento $where AND fecha_completado IS NOT NULL", $conn);

// Cumplimiento mensual
$mes = date('Y-m');
$mes_total     = obtener("SELECT COUNT(*) AS total FROM ordenes_mantenimiento $where AND DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mes'", $conn);
$mes_terminado = obtener("SELECT COUNT(*) AS total FROM ordenes_mantenimiento $where AND estatus = 'Terminado' AND DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mes'", $conn);
$cumplimiento_mes = $mes_total > 0 ? round(($mes_terminado / $mes_total) * 100, 1) : 0;

// Órdenes por mes
$q1 = $conn->query("SELECT DATE_FORMAT(fecha_reporte, '%Y-%m') AS mes, COUNT(*) AS total FROM ordenes_mantenimiento $where GROUP BY mes ORDER BY mes");
$labels_mes = $values_mes = [];
while ($r = $q1->fetch_assoc()) {
    $labels_mes[] = $r['mes'];
    $values_mes[] = (int)$r['total'];
}

// Costo mensual
$q2 = $conn->query("SELECT DATE_FORMAT(fecha_reporte, '%Y-%m') AS mes, SUM(costo_final) AS total FROM ordenes_mantenimiento $where GROUP BY mes ORDER BY mes");
$labels_costos = $values_costos = [];
while ($r = $q2->fetch_assoc()) {
    $labels_costos[] = $r['mes'];
    $values_costos[] = round($r['total'], 2);
}

// Unidades
$q3 = $conn->query("SELECT u.nombre AS unidad, COUNT(*) AS total FROM ordenes_mantenimiento o JOIN unidades_negocio u ON o.unidad_negocio_id = u.id $where GROUP BY o.unidad_negocio_id");
$labels_unidad = $values_unidad = [];
while ($r = $q3->fetch_assoc()) {
    $labels_unidad[] = $r['unidad'];
    $values_unidad[] = (int)$r['total'];
}

// Estatus
$estatus_labels = ['Pendiente', 'En proceso', 'Terminado', 'Cancelado', 'Vencido'];
$estatus_valores = [];
foreach ($estatus_labels as $e) {
    $estatus_valores[] = obtener("SELECT COUNT(*) AS total FROM ordenes_mantenimiento $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = '$e'", $conn);
}

// Completadas por día
$where_completadas = ["COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Terminado'", "fecha_completado IS NOT NULL"];
if (!empty($_GET['alojamiento'])) {
    $ids = implode(',', array_map('intval', $_GET['alojamiento']));
    $where_completadas[] = "alojamiento_id IN ($ids)";
}
if (!empty($_GET['unidad_negocio'])) {
    $ids = implode(',', array_map('intval', $_GET['unidad_negocio']));
    $where_completadas[] = "unidad_negocio_id IN ($ids)";
}
if ($fecha_inicio) $where_completadas[] = "fecha_completado >= '$fecha_inicio'";
if ($fecha_fin)    $where_completadas[] = "fecha_completado <= '$fecha_fin'";
$where_comp_sql = 'WHERE ' . implode(' AND ', $where_completadas);

$completadas_dia = [];
if ($fecha_inicio && $fecha_fin) {
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $fin->modify('+1 day');

    foreach (new DatePeriod($inicio, new DateInterval('P1D'), $fin) as $fecha) {
        $completadas_dia[$fecha->format('Y-m-d')] = 0;
    }

    $res = $conn->query("SELECT DATE(fecha_completado) AS dia, COUNT(*) AS total FROM ordenes_mantenimiento $where_comp_sql GROUP BY dia ORDER BY dia ASC");
    while ($r = $res->fetch_assoc()) {
        $completadas_dia[$r['dia']] = (int)$r['total'];
    }

    $labels_completadas = array_keys($completadas_dia);
    $valores_completadas = array_values($completadas_dia);
} else {
    $labels_completadas = $valores_completadas = [];
    $res = $conn->query("SELECT DATE(fecha_completado) AS dia, COUNT(*) AS total FROM ordenes_mantenimiento $where_comp_sql GROUP BY dia ORDER BY dia ASC");
    while ($r = $res->fetch_assoc()) {
        $labels_completadas[] = $r['dia'];
        $valores_completadas[] = (int)$r['total'];
    }
}

// Completadas por usuario
$q_users = $conn->query("SELECT (SELECT nombre FROM usuarios WHERE id = quien_realizo_id) AS usuario, COUNT(*) AS total FROM ordenes_mantenimiento $where_comp_sql GROUP BY quien_realizo_id");
$labels_usuarios = $valores_usuarios = [];
while ($r = $q_users->fetch_assoc()) {
    $labels_usuarios[] = $r['usuario'];
    $valores_usuarios[] = (int)$r['total'];
}

// Productividad
$total_completadas = array_sum($valores_completadas);
$usuarios_ativos = obtener("SELECT COUNT(*) AS total FROM usuarios WHERE puesto LIKE '%Mantenimiento%'", $conn);
$dias_periodo = 0;
if ($fecha_inicio && $fecha_fin) {
    $start = new DateTime($fecha_inicio);
    $end = new DateTime($fecha_fin);
    $dias_periodo = $start->diff($end)->days + 1;
}
$meta_diaria = 5;
$esperadas = $usuarios_ativos * $dias_periodo * $meta_diaria;

$productividad = ($esperadas > 0) ? round(($total_completadas / $esperadas) * 100, 1) : 0;

// Productividad ponderada
$total_ponderacion_terminadas = obtener("SELECT SUM(ponderacion) AS total FROM ordenes_mantenimiento $where_comp_sql", $conn);
$ponderado = ($esperadas > 0) ? round(($total_ponderacion_terminadas / $esperadas) * 100, 1) : 0;

// Top alojamientos
$top_general = [];
$res = $conn->query("SELECT a.nombre, COUNT(*) AS total FROM ordenes_mantenimiento o JOIN alojamientos a ON o.alojamiento_id = a.id $where GROUP BY o.alojamiento_id ORDER BY total DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    $top_general[] = ['nombre' => $row['nombre'], 'total' => (int)$row['total']];
}

$top_pendientes = [];
$res = $conn->query("SELECT a.nombre, COUNT(*) AS total FROM ordenes_mantenimiento o JOIN alojamientos a ON o.alojamiento_id = a.id $where AND COALESCE(NULLIF(TRIM(o.estatus), ''), 'Pendiente') = 'Pendiente' GROUP BY o.alojamiento_id ORDER BY total DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    $top_pendientes[] = ['nombre' => $row['nombre'], 'total' => (int)$row['total']];
}

$top_terminados = [];
$res = $conn->query("SELECT a.nombre, COUNT(*) AS total FROM ordenes_mantenimiento o JOIN alojamientos a ON o.alojamiento_id = a.id $where AND COALESCE(NULLIF(TRIM(o.estatus), ''), 'Pendiente') = 'Terminado' GROUP BY o.alojamiento_id ORDER BY total DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    $top_terminados[] = ['nombre' => $row['nombre'], 'total' => (int)$row['total']];
}

$sin_reportes = [];
$res = $conn->query("SELECT nombre FROM alojamientos WHERE id NOT IN (SELECT alojamiento_id FROM ordenes_mantenimiento)");
while ($row = $res->fetch_assoc()) {
    $sin_reportes[] = $row['nombre'];
}

// Ponderaciones por estatus
$total_ponderado_general = obtener("SELECT SUM(ponderacion) AS total FROM ordenes_mantenimiento $where", $conn);
$total_ponderado_proceso = obtener("SELECT SUM(ponderacion) AS total FROM ordenes_mantenimiento $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'En proceso'", $conn);
$total_ponderado_terminado = obtener("SELECT SUM(ponderacion) AS total FROM ordenes_mantenimiento $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Terminado'", $conn);
$total_ponderado_pendiente = obtener("SELECT SUM(ponderacion) AS total FROM ordenes_mantenimiento $where AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = 'Pendiente'", $conn);



// 👉 Retornar todos los KPIs
$kpis = [
    'total' => (int)$total,
    'pendientes' => (int)$pendientes,
    'en_proceso' => (int)$proceso,
    'terminados' => (int)$terminados,
    'cancelados' => (int)$cancelados,
    'vencidos' => (int)$vencidos,
    'costo_total' => round($costo_total, 2),
    'costo_promedio' => round($costo_prom, 2),
    'promedio_dias' => round($prom_dias, 1),
    'cumplimiento_mes' => $cumplimiento_mes,
    'productividad' => $productividad,
    'mensual' => ['labels' => $labels_mes, 'valores' => $values_mes],
    'costo_mensual' => ['labels' => $labels_costos, 'valores' => $values_costos],
    'unidades' => ['labels' => $labels_unidad, 'valores' => $values_unidad],
    'estatus' => ['labels' => $estatus_labels, 'valores' => $estatus_valores],
    'completadas_dia' => ['labels' => $labels_completadas, 'valores' => $valores_completadas],
    'completadas_usuario' => ['labels' => $labels_usuarios, 'valores' => $valores_usuarios],
    'top_general' => $top_general,
    'top_pendientes' => $top_pendientes,
    'top_terminados' => $top_terminados,
    'sin_reportes' => $sin_reportes,
    'total_ponderado_general' => (int)$total_ponderado_general,
    'total_ponderado_proceso' => (int)$total_ponderado_proceso,
    'total_ponderado_terminado' => (int)$total_ponderado_terminado,
    'total_ponderado_pendiente' => (int)$total_ponderado_pendiente,
    'ponderado' => $ponderado,

];

return $kpis;