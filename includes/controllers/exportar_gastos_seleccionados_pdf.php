<?php
require_once(__DIR__ . '/../../conexion.php');
require_once(__DIR__ . '/../../dompdf/autoload.inc.php');

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
    die("No se recibieron datos válidos.");
}

$ids = array_map('intval', $_POST['ids']);
$id_list = implode(',', $ids);

$sql = "SELECT 
    g.folio, 
    CASE 
        WHEN g.nota_credito_id IS NOT NULL THEN u.nombre 
        ELSE p.nombre 
    END AS proveedor, 
    g.monto, 
    g.fecha_pago, 
    un.nombre AS unidad, 
    g.tipo_gasto,
    g.tipo_compra,
    g.medio_pago,
    g.cuenta_bancaria, 
    g.concepto, 
    g.estatus, 
    (SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id) AS abonado_total,
    (g.monto - IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS saldo
FROM gastos g
LEFT JOIN proveedores p ON g.proveedor_id = p.id
LEFT JOIN unidades_negocio un ON g.unidad_negocio_id = un.id
LEFT JOIN notas_credito nc ON g.nota_credito_id = nc.id
LEFT JOIN usuarios u ON nc.usuario_responsable_id = u.id
WHERE g.id IN ($id_list)";

$res = $conn->query($sql);

// Construir HTML para el PDF
$html = '
<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 11px;
    }
    th, td {
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }
</style>
<h3>Gastos Seleccionados</h3>
<table>
<thead>
<tr>
    <th>Folio</th>
    <th>Proveedor</th>
    <th>Monto</th>
    <th>Fecha</th>
    <th>Unidad</th>
    <th>Tipo</th>
    <th>Uso</th>
    <th>Forma</th>
    <th>Cuenta</th>
    <th>Concepto</th>
    <th>Estatus</th>
    <th>Abonado</th>
    <th>Saldo</th>
</tr>
</thead>
<tbody>';

while ($row = $res->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($row['folio']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['proveedor']) . '</td>';
    $html .= '<td>$' . number_format($row['monto'], 2) . '</td>';
    $html .= '<td>' . $row['fecha_pago'] . '</td>';
    $html .= '<td>' . htmlspecialchars($row['unidad']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['tipo_gasto']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['tipo_compra']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['medio_pago']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['cuenta_bancaria']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['concepto']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['estatus']) . '</td>';
    $html .= '<td>$' . number_format($row['abonado_total'], 2) . '</td>';
    $html .= '<td>$' . number_format($row['saldo'], 2) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';

// Inicializar DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Enviar al navegador
$dompdf->stream('gastos_seleccionados.pdf', ['Attachment' => false]);
exit;
