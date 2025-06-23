<?php
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
include 'conexion.php';

$folio = $_GET['folio'] ?? '';
if (!$folio) { die('Folio no proporcionado.'); }

$sql = "SELECT oc.folio, oc.monto, oc.vencimiento_pago, oc.concepto_pago, oc.tipo_pago, oc.genera_factura, oc.estatus_pago, oc.nivel,
               p.nombre AS proveedor, u.nombre AS usuario, un.nombre AS unidad_negocio
        FROM ordenes_compra oc
        LEFT JOIN proveedores p ON oc.proveedor_id=p.id
        LEFT JOIN usuarios u ON oc.usuario_solicitante_id=u.id
        LEFT JOIN unidades_negocio un ON oc.unidad_negocio_id=un.id
        WHERE oc.folio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s',$folio);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows===0){ die('Orden no encontrada.'); }
$data = $res->fetch_assoc();

$dompdf = new Dompdf();
$html = '<h2 style="text-align:center;">Orden de Compra</h2>';
$html .= '<table border="1" cellspacing="0" cellpadding="5" style="width:100%;font-size:12px;">';
foreach($data as $k=>$v){
    $html .= '<tr><td><strong>'.htmlspecialchars($k).'</strong></td><td>'.htmlspecialchars($v).'</td></tr>';
}
$html .= '</table>';
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream('orden_'.$folio.'.pdf',["Attachment"=>false]);
exit;
?>
