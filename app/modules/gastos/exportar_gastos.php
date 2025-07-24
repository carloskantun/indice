<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=gastos.csv');
include 'conexion.php';

$proveedor    = $_GET['proveedor'] ?? '';
$unidad       = $_GET['unidad'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin'] ?? '';
$estatus      = $_GET['estatus'] ?? '';
$origen       = $_GET['origen'] ?? '';
$orden        = $_GET['orden'] ?? 'fecha';
$dir          = strtoupper($_GET['dir'] ?? 'DESC');

$cond = [];
if ($proveedor !== '') { $cond[] = 'g.proveedor_id=' . intval($proveedor); }
if ($unidad !== '') { $cond[] = 'g.unidad_negocio_id=' . intval($unidad); }
if ($fecha_inicio !== '') { $cond[] = "g.fecha_pago >= '".$conn->real_escape_string($fecha_inicio)."'"; }
if ($fecha_fin !== '') { $cond[] = "g.fecha_pago <= '".$conn->real_escape_string($fecha_fin)."'"; }
if ($estatus !== '') { $cond[] = "g.estatus='".$conn->real_escape_string($estatus)."'"; }
if ($origen !== '') { $cond[] = "g.origen='".$conn->real_escape_string($origen)."'"; }
$where = $cond ? 'WHERE '.implode(' AND ', $cond) : '';

$mapa_orden_sql = [
    'folio'    => 'g.folio',
    'proveedor'=> 'p.nombre',
    'monto'    => 'g.monto',
    'fecha'    => 'g.fecha_pago',
    'unidad'   => 'un.nombre',
    'tipo'     => 'g.tipo_gasto',
    'tipo_compra' => 'g.tipo_compra',
    'medio'    => 'g.medio_pago',
    'cuenta'   => 'g.cuenta_bancaria',
    'concepto' => 'g.concepto',
    'estatus'  => 'g.estatus'
];
$columna_orden = $mapa_orden_sql[$orden] ?? 'g.fecha_pago';
$dir = $dir === 'ASC' ? 'ASC' : 'DESC';

$sql = "SELECT
    g.folio,
    p.nombre AS proveedor,
    g.monto,
    g.fecha_pago,
    un.nombre AS unidad,
    g.tipo_gasto,
    g.tipo_compra,
    g.medio_pago,
    g.cuenta_bancaria,
    g.concepto,
    g.estatus,
    g.origen,
    (SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id) AS abonado_total,
    (g.monto - IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id),0)) AS saldo,
    (SELECT GROUP_CONCAT(a.archivo_comprobante SEPARATOR ';') FROM abonos_gastos a WHERE a.gasto_id = g.id AND a.archivo_comprobante IS NOT NULL) AS archivo_comprobante
FROM gastos g
LEFT JOIN proveedores p ON g.proveedor_id = p.id
LEFT JOIN unidades_negocio un ON g.unidad_negocio_id = un.id
$where
ORDER BY $columna_orden $dir";

$res = $conn->query($sql);
$out = fopen('php://output','w');
fputcsv($out,['Folio','Proveedor','Monto','Abonado','Saldo','Fecha','Unidad','Tipo','Uso','Medio','Cuenta','Concepto','Estatus','Origen','Comprobantes']);
while($row=$res->fetch_assoc()){
    fputcsv($out,[
        $row['folio'],
        $row['proveedor'],
        $row['monto'],
        $row['abonado_total'],
        $row['saldo'],
        $row['fecha_pago'],
        $row['unidad'],
        $row['tipo_gasto'],
        $row['tipo_compra'],
        $row['medio_pago'],
        $row['cuenta_bancaria'],
        $row['concepto'],
        $row['estatus'],
        $row['origen'],
        $row['archivo_comprobante']
    ]);
}
fclose($out);
exit;
?>