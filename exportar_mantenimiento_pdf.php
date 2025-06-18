<?php
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

include 'conexion.php';

$dompdf = new Dompdf();


// ---- Construcción de la consulta respetando filtros activos ----
$sql = "SELECT folio, fecha_reporte, descripcion_reporte, estatus, nivel, fecha_completado, detalle_completado, costo_final,
               (SELECT nombre FROM alojamientos WHERE id = alojamiento_id) AS alojamiento,
               (SELECT nombre FROM usuarios WHERE id = usuario_solicitante_id) AS usuario,
               (SELECT nombre FROM unidades_negocio WHERE id = unidad_negocio_id) AS unidad_negocio,
               (SELECT nombre FROM usuarios WHERE id = quien_realizo_id) AS quien_realizo
        FROM ordenes_mantenimiento";

$condiciones = [];
if (!empty($_GET['estatus'])) {
    $estatus = mysqli_real_escape_string($conn, trim($_GET['estatus']));
    if (strtolower($estatus) === 'pendiente') {
        $condiciones[] = "(estatus = 'Pendiente' OR estatus IS NULL OR estatus = '')";
    } else {
        $condiciones[] = "estatus = '$estatus'";
    }
}


if (!empty($_GET['alojamiento']) && is_array($_GET['alojamiento'])) {
    $ids = array_map('intval', $_GET['alojamiento']);
    $condiciones[] = "alojamiento_id IN (" . implode(',', $ids) . ")";
}

if (!empty($_GET['usuario']) && is_array($_GET['usuario'])) {
    $ids = array_map('intval', $_GET['usuario']);
    $condiciones[] = "usuario_solicitante_id IN (" . implode(',', $ids) . ")";
}

if (!empty($_GET['unidad_negocio']) && is_array($_GET['unidad_negocio'])) {
    $ids = array_map('intval', $_GET['unidad_negocio']);
    $condiciones[] = "unidad_negocio_id IN (" . implode(',', $ids) . ")";
}

if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $fi = $conn->real_escape_string($_GET['fecha_inicio']);
    $ff = $conn->real_escape_string($_GET['fecha_fin']);
    $condiciones[] = "fecha_reporte BETWEEN '$fi' AND '$ff'";
} elseif (!empty($_GET['fecha_inicio'])) {
    $fi = $conn->real_escape_string($_GET['fecha_inicio']);
    $condiciones[] = "fecha_reporte >= '$fi'";
} elseif (!empty($_GET['fecha_fin'])) {
    $ff = $conn->real_escape_string($_GET['fecha_fin']);
    $condiciones[] = "fecha_reporte <= '$ff'";
}

$where = count($condiciones) ? ' WHERE ' . implode(' AND ', $condiciones) : '';
$sql .= $where . ' ORDER BY id ASC';

$resultado = $conn->query($sql);

// Generar HTML
$html = '<html><head><meta charset="UTF-8"></head><body>';
$html .= '<h2 style="text-align:center;">Reporte de Mantenimiento</h2>';
$html .= '<table border="1" cellspacing="0" cellpadding="4" style="width:100%; font-size:10px;">';
$html .= '<thead>
<tr>
<th>Folio</th>
<th>Fecha Reporte</th>
<th>Descripción</th>
<th>Estatus</th>
<th>Nivel</th>
<th>Fecha Ejecución</th>
<th>Detalle</th>
<th>Costo</th>
<th>Alojamiento</th>
<th>Solicitante</th>
<th>Unidad</th>
<th>Quién Realizó</th>
</tr>
</thead><tbody>';

while ($row = $resultado->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($row['folio']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['fecha_reporte']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['descripcion_reporte']) . '</td>';
    $estatus = $row['estatus'] ?? 'Pendiente';
    if ($estatus === '') $estatus = 'Pendiente';
    $html .= '<td>' . htmlspecialchars($estatus) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['nivel']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['fecha_completado']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['detalle_completado']) . '</td>';
    $html .= '<td>$' . number_format($row['costo_final'], 2) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['alojamiento']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['usuario']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['unidad_negocio']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['quien_realizo']) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table></body></html>';

// Renderizar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("reporte_mantenimiento.pdf", ["Attachment" => true]);
exit;
?>
