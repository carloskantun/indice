<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
include 'conexion.php';
$dompdf = new Dompdf();

$where = "WHERE 1=1";
if (!empty($_GET['cliente'])) $where .= " AND cliente LIKE '%".$conn->real_escape_string($_GET['cliente'])."%'";
if (!empty($_GET['servicio'])) $where .= " AND servicio LIKE '%".$conn->real_escape_string($_GET['servicio'])."%'";
if (!empty($_GET['estatus'])) $where .= " AND estatus='".$conn->real_escape_string($_GET['estatus'])."'";
if (!empty($_GET['fecha_inicio'])) $where .= " AND fecha>='".$conn->real_escape_string($_GET['fecha_inicio'])."'";
if (!empty($_GET['fecha_fin'])) $where .= " AND fecha<='".$conn->real_escape_string($_GET['fecha_fin'])."'";

$sql = "SELECT folio,fecha,cliente,servicio,prenda,cantidad,monto,estatus FROM ordenes_lavanderia $where";
$res = $conn->query($sql);
$html = '<h3>Reporte Lavandería</h3><table border="1" width="100%"><thead><tr><th>Folio</th><th>Fecha</th><th>Cliente</th><th>Servicio</th><th>Prenda</th><th>Cantidad</th><th>Monto</th><th>Estatus</th></tr></thead><tbody>';
while($row=$res->fetch_assoc()){
 $html.='<tr>';
 foreach($row as $v){$html.='<td>'.htmlspecialchars($v).'</td>';}
 $html.='</tr>';
}
$html.='</tbody></table>';
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','landscape');
$dompdf->render();
$dompdf->stream('lavanderia.pdf',['Attachment'=>true]);
?>
