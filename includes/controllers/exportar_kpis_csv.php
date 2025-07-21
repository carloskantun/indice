<?php
include '../../conexion.php';

// Parámetros
$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin    = $_GET['fecha_fin'] ?? null;
$unidad_id    = $_GET['unidad_negocio_id'] ?? null;

if (!$fecha_inicio || !$fecha_fin) {
    die('Fechas inválidas');
}

// Condiciones SQL
$cond = ["fecha_pago BETWEEN '$fecha_inicio' AND '$fecha_fin'"];
if (!empty($unidad_id)) {
    $cond[] = "unidad_negocio_id = " . intval($unidad_id);
}
$where = 'WHERE ' . implode(' AND ', $cond);

// Consultar datos por proveedor
$proveedores = [];
$res = $conn->query("
    SELECT 
        p.nombre AS proveedor,
        SUM(g.monto) AS total,
        SUM(IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS abonado,
        SUM(g.monto - IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS saldo
    FROM gastos g
    LEFT JOIN proveedores p ON g.proveedor_id = p.id
    $where
    GROUP BY p.nombre
    ORDER BY total DESC
");

$total_general = ['total' => 0, 'abonado' => 0, 'saldo' => 0];

while ($row = $res->fetch_assoc()) {
    $nombre = trim($row['proveedor']) ?: 'Sin proveedor';
    $total = (float)$row['total'];
    $abonado = (float)$row['abonado'];
    $saldo = (float)$row['saldo'];

    $proveedores[$nombre] = [
        'total'   => $total,
        'abonado' => $abonado,
        'saldo'   => $saldo,
    ];

    $total_general['total']   += $total;
    $total_general['abonado'] += $abonado;
    $total_general['saldo']   += $saldo;
}

// Crear CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="kpis_gastos_proveedores.csv"');

$output = fopen('php://output', 'w');

// Encabezado
$headers = array_merge(['Métrica'], array_keys($proveedores), ['Total General']);
fputcsv($output, $headers);

// Fila: Gasto Total
$row_total = ['Gasto Total'];
foreach ($proveedores as $p) {
    $row_total[] = '$' . number_format($p['total'], 2);
}
$row_total[] = '$' . number_format($total_general['total'], 2);
fputcsv($output, $row_total);

// Fila: Abonado
$row_abonado = ['Abonado'];
foreach ($proveedores as $p) {
    $row_abonado[] = '$' . number_format($p['abonado'], 2);
}
$row_abonado[] = '$' . number_format($total_general['abonado'], 2);
fputcsv($output, $row_abonado);

// Fila: Saldo
$row_saldo = ['Saldo'];
foreach ($proveedores as $p) {
    $row_saldo[] = '$' . number_format($p['saldo'], 2);
}
$row_saldo[] = '$' . number_format($total_general['saldo'], 2);
fputcsv($output, $row_saldo);

fclose($output);
exit;
