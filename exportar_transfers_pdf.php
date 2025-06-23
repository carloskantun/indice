<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

include 'auth.php';
include 'conexion.php';

// Validar columnas
$columnas = explode(',', $_GET['columnas'] ?? '');
$columnas_validas = [
    'folio' => 'Folio',
    'tipo' => 'Tipo de Servicio',
    'fecha' => 'Fecha',
    'pickup' => 'Hora Pickup',
    'hotel' => 'Hotel',
    'pasajeros' => 'Pasajeros',
    'numero_reserva' => 'Reserva',
    'vehiculo' => 'Vehículo',
    'conductor' => 'Conductor',
    'agencia' => 'Agencia',
    'estatus' => 'Estatus'
];
$columnas_mostrar = [];
foreach ($columnas as $c) {
    if (isset($columnas_validas[$c])) {
        $columnas_mostrar[$c] = $columnas_validas[$c];
    }
}
if (empty($columnas_mostrar)) {
    die("Error: No hay columnas válidas para exportar.");
}

// Filtros
$where = "WHERE 1=1";
if (!empty($_GET['tipo'])) {
    $t = $conn->real_escape_string($_GET['tipo']);
    $where .= " AND tipo_servicio = '$t'";
}
if (!empty($_GET['agencia'])) {
    $a = $conn->real_escape_string($_GET['agencia']);
    $where .= " AND agencia LIKE '%$a%'";
}
if (!empty($_GET['operador'])) {
    $op = (int)$_GET['operador'];
    $where .= " AND usuario_solicitante_id = $op";
}
if (!empty($_GET['fecha_inicio'])) {
    $fi = $conn->real_escape_string($_GET['fecha_inicio']);
    $where .= " AND fecha_servicio >= '$fi'";
}
if (!empty($_GET['fecha_fin'])) {
    $ff = $conn->real_escape_string($_GET['fecha_fin']);
    $where .= " AND fecha_servicio <= '$ff'";
}

// Consulta
$res = $conn->query("SELECT * FROM ordenes_transfers $where ORDER BY fecha_servicio ASC");

// Generar HTML
$estilo = '
<style>
    body { font-family: sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #aaa; padding: 5px; }
    th { background-color: #f0f0f0; }
    h2 { text-align: center; }
</style>
';

$html = "<h2>Reporte de Transfers</h2>";
$html .= "<table><thead><tr>";
foreach ($columnas_mostrar as $label) {
    $html .= "<th>$label</th>";
}
$html .= "</tr></thead><tbody>";

while ($row = $res->fetch_assoc()) {
    $html .= "<tr>";
    foreach (array_keys($columnas_mostrar) as $c) {
        switch ($c) {
            case 'tipo':  $html .= "<td>" . $row['tipo_servicio'] . "</td>"; break;
            case 'fecha': $html .= "<td>" . $row['fecha_servicio'] . "</td>"; break;
            case 'hotel': $html .= "<td>" . $row['hotel_pickup'] . "</td>"; break;
            case 'pasajeros': $html .= "<td>" . $row['nombre_pasajeros'] . "</td>"; break;
            case 'numero_reserva': $html .= "<td>" . $row['num_pasajeros'] . "</td>"; break;
            default: $html .= "<td>" . htmlspecialchars($row[$c]) . "</td>";
        }
    }
    $html .= "</tr>";
}
$html .= "</tbody></table>";

// Generar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($estilo . $html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("transfers_reporte.pdf", ["Attachment" => false]);
exit;
