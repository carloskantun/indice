<?php
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
include 'conexion.php';

$folio = $_GET['folio'] ?? '';
if ($folio === '') die('Folio no especificado');

$stmt = $conn->prepare("SELECT * FROM ordenes_transfers WHERE folio=?");
$stmt->bind_param('s',$folio);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows===0) die('No existe');
$orden = $res->fetch_assoc();

$dompdf = new Dompdf();
$dompdf->setPaper('letter');
$html = '<h2>Transfer '.$orden['folio'].'</h2><table>'; 
foreach($orden as $k=>$v){
    if($k==='id') continue;
    $html .= '<tr><th>'.htmlspecialchars($k).'</th><td>'.htmlspecialchars($v).'</td></tr>';
}
$html .= '</table>';
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("transfer_$folio.pdf",['Attachment'=>false]);
exit;
?>
