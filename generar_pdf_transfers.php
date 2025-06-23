<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

include 'auth.php';
include 'conexion.php';

$folio = $_GET['folio'] ?? '';
if (!$folio) {
    die("Error: folio no proporcionado.");
}

$sql = "SELECT * FROM ordenes_transfers WHERE folio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $folio);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Error: orden no encontrada.");
}

$data = $res->fetch_assoc();

// HTML para el PDF
$estilo = '
<style>
  body { font-family: sans-serif; font-size: 14px; }
  h2 { text-align: center; margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  td { padding: 6px; vertical-align: top; }
  .label { font-weight: bold; background-color: #f0f0f0; width: 30%; }
</style>
';

$html = '<h2>📄 Orden de Transfer</h2>';
$html .= '<table border="1">';
$html .= '<tr><td class="label">Folio</td><td>' . $data['folio'] . '</td></tr>';
$html .= '<tr><td class="label">Tipo de Servicio</td><td>' . $data['tipo_servicio'] . '</td></tr>';
$html .= '<tr><td class="label">Fecha</td><td>' . $data['fecha_servicio'] . '</td></tr>';
$html .= '<tr><td class="label">Hora Pickup</td><td>' . $data['pickup'] . '</td></tr>';
$html .= '<tr><td class="label">Hotel</td><td>' . $data['hotel_pickup'] . '</td></tr>';
$html .= '<tr><td class="label">Pasajeros</td><td>' . $data['nombre_pasajeros'] . '</td></tr>';
$html .= '<tr><td class="label">No. Reserva</td><td>' . $data['num_pasajeros'] . '</td></tr>';
$html .= '<tr><td class="label">Vehículo</td><td>' . $data['vehiculo'] . '</td></tr>';
$html .= '<tr><td class="label">Conductor</td><td>' . $data['conductor'] . '</td></tr>';
$html .= '<tr><td class="label">Agencia</td><td>' . $data['agencia'] . '</td></tr>';
$html .= '<tr><td class="label">Estatus</td><td>' . $data['estatus'] . '</td></tr>';
$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($estilo . $html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("transfer_" . $folio . ".pdf", ["Attachment" => false]);
exit;
