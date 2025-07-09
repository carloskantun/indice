<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
include 'conexion.php';

$folio = $_GET['folio'] ?? '';
if (!$folio) { die('Folio no proporcionado'); }

$sql = "SELECT g.folio, p.nombre AS proveedor, un.nombre AS unidad, g.monto, g.fecha_pago, g.estatus, g.medio_pago, g.cuenta_bancaria, g.concepto
        FROM gastos g
        LEFT JOIN proveedores p ON g.proveedor_id=p.id
        LEFT JOIN unidades_negocio un ON g.unidad_negocio_id=un.id
        WHERE g.folio=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s',$folio);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows===0){ die('Registro no encontrado'); }
$row = $res->fetch_assoc();

$comp = $conn->query("SELECT archivo_comprobante FROM abonos_gastos WHERE gasto_id = (SELECT id FROM gastos WHERE folio='".$conn->real_escape_string($folio)."') AND archivo_comprobante IS NOT NULL ORDER BY id ASC");
$archivos = [];
while($r=$comp->fetch_assoc()){ $archivos[] = $r['archivo_comprobante']; }

$html = '<h3 style="text-align:center;font-weight:bold;">Detalle de Gasto</h3>';
$html .= '<table border="1" cellspacing="0" cellpadding="4" width="100%" style="font-size:12px">';
$html .= '<tr><td><strong>Folio</strong></td><td>'.htmlspecialchars($row['folio']).'</td></tr>';
$html .= '<tr><td><strong>Proveedor</strong></td><td>'.htmlspecialchars($row['proveedor']).'</td></tr>';
$html .= '<tr><td><strong>Unidad</strong></td><td>'.htmlspecialchars($row['unidad']).'</td></tr>';
$html .= '<tr><td><strong>Monto</strong></td><td>$'.number_format($row['monto'],2).'</td></tr>';
$html .= '<tr><td><strong>Fecha</strong></td><td>'.htmlspecialchars($row['fecha_pago']).'</td></tr>';
$html .= '<tr><td><strong>Estatus</strong></td><td>'.htmlspecialchars($row['estatus']).'</td></tr>';
$html .= '<tr><td><strong>Medio de pago</strong></td><td>'.htmlspecialchars($row['medio_pago']).'</td></tr>';
$html .= '<tr><td><strong>Cuenta</strong></td><td>'.htmlspecialchars($row['cuenta_bancaria']).'</td></tr>';
$html .= '<tr><td><strong>Concepto</strong></td><td>'.htmlspecialchars($row['concepto']).'</td></tr>';
$html .= '</table>';

if(count($archivos)){
    $html .= '<h4>Comprobantes</h4>';
    foreach($archivos as $a){
        $is_pdf = preg_match('/\.pdf$/i',$a);
        if($is_pdf){
            $html .= '<div><a href="'.htmlspecialchars($a).'">'.basename($a).'</a></div>';
        }else{
            $html .= '<div><img src="'.htmlspecialchars($a).'" style="max-width:500px;max-height:400px;"></div>';
        }
    }
}

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream('gasto_'.$folio.'.pdf', ['Attachment'=>false]);
exit;
?>