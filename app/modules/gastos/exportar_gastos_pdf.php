<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
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

$html = '<h3 style="text-align:center;font-weight:bold;">Reporte de Gastos</h3>';
$html .= '<table border="1" cellspacing="0" cellpadding="4" width="100%" style="font-size:10px">';
$html .= '<thead><tr><th>Folio</th><th>Proveedor</th><th>Monto</th><th>Abonado</th><th>Saldo</th><th>Fecha</th><th>Unidad</th><th>Tipo</th><th>Uso</th><th>Medio</th><th>Cuenta</th><th>Concepto</th><th>Estatus</th><th>Origen</th></tr></thead><tbody>';
while($row=$res->fetch_assoc()){
    $html .= '<tr>';
    $html .= '<td>'.htmlspecialchars($row['folio']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['proveedor']).'</td>';
    $html .= '<td>$'.number_format($row['monto'],2).'</td>';
    $html .= '<td>$'.number_format($row['abonado_total'],2).'</td>';
    $html .= '<td>$'.number_format($row['saldo'],2).'</td>';
    $html .= '<td>'.htmlspecialchars($row['fecha_pago']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['unidad']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['tipo_gasto']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['tipo_compra']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['medio_pago']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['cuenta_bancaria']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['concepto']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['estatus']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['origen']).'</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','landscape');
$dompdf->render();
$dompdf->stream('gastos.pdf', ['Attachment'=>false]);
exit;
?>