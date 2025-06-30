<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
include 'conexion.php';

$folio = $_GET['folio'] ?? '';
if (!$folio) { die('Folio no proporcionado'); }

$sql = "SELECT g.folio, p.nombre AS proveedor, g.monto, g.fecha_pago, un.nombre AS unidad, g.tipo_gasto, g.medio_pago, g.cuenta_bancaria, g.estatus, g.comentario FROM gastos g LEFT JOIN proveedores p ON g.proveedor_id=p.id LEFT JOIN unidades_negocio un ON g.unidad_negocio_id=un.id WHERE g.folio=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s',$folio);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows===0){ die('Registro no encontrado'); }
$row = $res->fetch_assoc();

$html = '<h3 style="text-align:center;font-weight:bold;">Detalle de Gasto</h3>';
$html .= '<table border="1" cellspacing="0" cellpadding="4" width="100%" style="font-size:12px">';
foreach($row as $k=>$v){
    $html .= '<tr><td><strong>'.htmlspecialchars($k).'</strong></td><td>'.htmlspecialchars($v).'</td></tr>';
}
$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream('gasto_'.$folio.'.pdf', ['Attachment'=>false]);
exit;
?>
