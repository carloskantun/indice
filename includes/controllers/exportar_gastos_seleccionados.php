<?php
require_once(__DIR__ . '/../../conexion.php');


if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
    die("No se recibieron datos válidos.");
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=gastos_seleccionados.csv');

$output = fopen('php://output', 'w');

// Encabezados del CSV
fputcsv($output, [
    'Folio', 'Proveedor', 'Monto', 'Fecha de pago', 'Unidad',
    'Tipo', 'Tipo compra', 'Forma', 'Cuenta', 'Concepto',
    'Estatus', 'Abonado', 'Saldo'
]);

$ids = array_map('intval', $_POST['ids']);
$id_list = implode(',', $ids);

$sql = "SELECT 
    g.folio, 
    CASE 
        WHEN g.nota_credito_id IS NOT NULL THEN u.nombre 
        ELSE p.nombre 
    END AS proveedor, 
    g.monto, 
    g.fecha_pago, 
    un.nombre AS unidad, 
    g.tipo_gasto,
    g.tipo_compra,
    g.medio_pago,
    g.cuenta_bancaria, 
    g.concepto, 
    g.estatus, 
    (SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id) AS abonado_total,
    (g.monto - IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS saldo
FROM gastos g
LEFT JOIN proveedores p ON g.proveedor_id = p.id
LEFT JOIN unidades_negocio un ON g.unidad_negocio_id = un.id
LEFT JOIN notas_credito nc ON g.nota_credito_id = nc.id
LEFT JOIN usuarios u ON nc.usuario_responsable_id = u.id
WHERE g.id IN ($id_list)";

$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {
    fputcsv($output, [
        $row['folio'],
        $row['proveedor'],
        number_format($row['monto'], 2, '.', ''),
        $row['fecha_pago'],
        $row['unidad'],
        $row['tipo_gasto'],
        $row['tipo_compra'],
        $row['medio_pago'],
        $row['cuenta_bancaria'],
        $row['concepto'],
        $row['estatus'],
        number_format($row['abonado_total'], 2, '.', ''),
        number_format($row['saldo'], 2, '.', '')
    ]);
}

fclose($output);
exit;
