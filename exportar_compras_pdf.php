<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
include 'conexion.php';
$dompdf = new Dompdf();

$sql = "SELECT oc.folio, oc.monto, oc.vencimiento_pago, oc.concepto_pago, oc.tipo_pago, oc.genera_factura, oc.estatus_pago,
               p.nombre AS proveedor, u.nombre AS usuario, un.nombre AS unidad_negocio
        FROM ordenes_compra oc
        LEFT JOIN proveedores p ON oc.proveedor_id = p.id
        LEFT JOIN usuarios u ON oc.usuario_solicitante_id = u.id
        LEFT JOIN unidades_negocio un ON oc.unidad_negocio_id = un.id";

$cond = [];
if (!empty($_GET['estatus'])) {
    $est = mysqli_real_escape_string($conn, $_GET['estatus']);
    $cond[] = "oc.estatus_pago = '$est'";
}
if (!empty($_GET['proveedor']) && is_array($_GET['proveedor'])) {
    $ids = array_map('intval', $_GET['proveedor']);
    $cond[] = "oc.proveedor_id IN (".implode(',', $ids).")";
}
if (!empty($_GET['usuario']) && is_array($_GET['usuario'])) {
    $ids = array_map('intval', $_GET['usuario']);
    $cond[] = "oc.usuario_solicitante_id IN (".implode(',', $ids).")";
}
if (!empty($_GET['unidad_negocio']) && is_array($_GET['unidad_negocio'])) {
    $ids = array_map('intval', $_GET['unidad_negocio']);
    $cond[] = "oc.unidad_negocio_id IN (".implode(',', $ids).")";
}
if (!empty($_GET['fecha_inicio'])) {
    $fi = $conn->real_escape_string($_GET['fecha_inicio']);
    $cond[] = "oc.vencimiento_pago >= '$fi'";
}
if (!empty($_GET['fecha_fin'])) {
    $ff = $conn->real_escape_string($_GET['fecha_fin']);
    $cond[] = "oc.vencimiento_pago <= '$ff'";
}
$where = count($cond) ? ' WHERE '.implode(' AND ',$cond) : '';
$sql .= $where.' ORDER BY oc.folio ASC';
$result = $conn->query($sql);

$html = '<html><head><meta charset="UTF-8"></head><body>';
$html .= '<h2 style="text-align:center;">Índice de Gastos</h2>';
$html .= '<table border="1" cellspacing="0" cellpadding="4" style="width:100%;font-size:10px;">';
$html .= '<thead><tr><th>Folio</th><th>Proveedor</th><th>Monto</th><th>Vencimiento</th><th>Concepto</th><th>Tipo</th><th>Factura</th><th>Usuario</th><th>Unidad</th><th>Estatus</th></tr></thead><tbody>';
while($row=$result->fetch_assoc()){
    $html .= '<tr>';
    $html .= '<td>'.htmlspecialchars($row['folio']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['proveedor']).'</td>';
    $html .= '<td>$'.number_format($row['monto'],2).'</td>';
    $html .= '<td>'.htmlspecialchars($row['vencimiento_pago']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['concepto_pago']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['tipo_pago']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['genera_factura']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['usuario']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['unidad_negocio']).'</td>';
    $html .= '<td>'.htmlspecialchars($row['estatus_pago']).'</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table></body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('gastos.pdf', ['Attachment' => true]);
exit;
?>
