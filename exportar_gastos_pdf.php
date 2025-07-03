<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
include 'conexion.php';

$cond = [];
if (!empty($_GET['proveedor'])) {
    $id = intval($_GET['proveedor']);
    $cond[] = "g.proveedor_id=$id";
}
if (!empty($_GET['unidad'])) {
    $id = intval($_GET['unidad']);
    $cond[] = "g.unidad_negocio_id=$id";
}
if (!empty($_GET['fecha_inicio'])) {
    $f = $conn->real_escape_string($_GET['fecha_inicio']);
    $cond[] = "g.fecha_pago >= '$f'";
}
if (!empty($_GET['fecha_fin'])) {
    $f = $conn->real_escape_string($_GET['fecha_fin']);
    $cond[] = "g.fecha_pago <= '$f'";
}
if (!empty($_GET['estatus'])) {
    $e = $conn->real_escape_string($_GET['estatus']);
    $cond[] = "g.estatus='$e'";
}
if (!empty($_GET['origen'])) {
    $o = $conn->real_escape_string($_GET['origen']);
    $cond[] = "g.origen='$o'";
}
$where = $cond ? 'WHERE '.implode(' AND ',$cond) : '';

$sql = "SELECT g.folio, p.nombre AS proveedor, g.monto, g.fecha_pago, un.nombre AS unidad, g.tipo_gasto, g.medio_pago, g.cuenta_bancaria, g.concepto, g.estatus FROM gastos g LEFT JOIN proveedores p ON g.proveedor_id=p.id LEFT JOIN unidades_negocio un ON g.unidad_negocio_id=un.id $where ORDER BY g.fecha_pago DESC";
$res = $conn->query($sql);

$html = '<h3 style="text-align:center;font-weight:bold;">Reporte de Gastos</h3>';
$html .= '<table border="1" cellspacing="0" cellpadding="4" width="100%" style="font-size:10px">';
$html .= '<thead><tr><th>Folio</th><th>Proveedor</th><th>Monto</th><th>Fecha</th><th>Unidad</th><th>Tipo</th><th>Medio</th><th>Cuenta</th><th>Concepto</th><th>Estatus</th></tr></thead><tbody>';
while($row=$res->fetch_assoc()){
    $html .= '<tr>';
    $html .= '<td>'.htmlspecialchars($row['folio']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['proveedor']).'</td>';
    $html .= '<td>$'.number_format($row['monto'],2).'</td>';
    $html .= '<td>'.htmlspecialchars($row['fecha_pago']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['unidad']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['tipo_gasto']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['medio_pago']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['cuenta_bancaria']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['concepto']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['estatus']).'</td>';
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
