<?php
// Evitar que los errores de PHP se envíen al CSV generado
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=export_compras.csv');

include 'conexion.php';
// Lanzar excepciones en caso de error de MySQL
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$output = fopen('php://output','w');

$mapa_columnas = [
    'folio' => 'oc.folio AS folio',
    'proveedor' => 'p.nombre AS proveedor',
    'monto' => 'oc.monto AS monto',
    'vencimiento' => 'oc.vencimiento_pago AS vencimiento',
    'concepto' => 'oc.concepto_pago AS concepto',
    'tipo' => 'oc.tipo_pago AS tipo',
    'factura' => 'oc.genera_factura AS factura',
    'usuario' => 'u.nombre AS usuario',
    'unidad_negocio' => 'un.nombre AS unidad_negocio',
    'estatus' => 'oc.estatus_pago AS estatus'
];

$mapa_titulos = [
    'folio' => 'Folio',
    'proveedor' => 'Proveedor',
    'monto' => 'Monto',
    'vencimiento' => 'Vencimiento',
    'concepto' => 'Concepto',
    'tipo' => 'Tipo',
    'factura' => 'Factura',
    'usuario' => 'Usuario',
    'unidad_negocio' => 'Unidad de Negocio',
    'estatus' => 'Estatus'
];

$cols = array_unique(array_filter(explode(',', $_GET['columnas'] ?? '')));
$cols = array_values(array_intersect($cols, array_keys($mapa_columnas)));
if (empty($cols)) {
    fputcsv($output,['Error: No se especificaron columnas válidas.']);
    fclose($output);
    exit;
}

$titulos = array_map(fn($c)=>$mapa_titulos[$c] ?? ucfirst($c), $cols);
fputcsv($output,$titulos);

$campos = array_map(fn($c)=>$mapa_columnas[$c], $cols);
$query = "SELECT " . implode(',', $campos) . " FROM ordenes_compra oc
    LEFT JOIN proveedores p ON oc.proveedor_id=p.id
    LEFT JOIN usuarios u ON oc.usuario_solicitante_id=u.id
    LEFT JOIN unidades_negocio un ON oc.unidad_negocio_id=un.id WHERE 1=1";

if (!empty($_GET['estatus'])) {
    $est = $conn->real_escape_string($_GET['estatus']);
    $query .= " AND oc.estatus_pago = '$est'";
}

if (!empty($_GET['proveedor']) && is_array($_GET['proveedor'])) {
    $ids = array_map('intval', $_GET['proveedor']);
    $query .= " AND oc.proveedor_id IN (".implode(',', $ids).")";
}
if (!empty($_GET['usuario']) && is_array($_GET['usuario'])) {
    $ids = array_map('intval', $_GET['usuario']);
    $query .= " AND oc.usuario_solicitante_id IN (".implode(',', $ids).")";
}
if (!empty($_GET['unidad_negocio']) && is_array($_GET['unidad_negocio'])) {
    $ids = array_map('intval', $_GET['unidad_negocio']);
    $query .= " AND oc.unidad_negocio_id IN (".implode(',', $ids).")";
}
if (!empty($_GET['fecha_inicio'])) {
    $fi = $conn->real_escape_string($_GET['fecha_inicio']);
    $query .= " AND oc.vencimiento_pago >= '$fi'";
}
if (!empty($_GET['fecha_fin'])) {
    $ff = $conn->real_escape_string($_GET['fecha_fin']);
    $query .= " AND oc.vencimiento_pago <= '$ff'";
}

$orden = $_GET['orden'] ?? 'folio';
$dir = strtoupper($_GET['dir'] ?? 'ASC');
$campo_orden = $mapa_columnas[$orden] ?? 'oc.folio';
$dir = $dir==='DESC' ? 'DESC':'ASC';
$query .= " ORDER BY $campo_orden $dir";

$res = null;
try {
    $res = $conn->query($query);

    while ($row = $res->fetch_assoc()) {
        $fila = [];
        foreach ($cols as $c) {
            $fila[] = isset($row[$c]) ? $row[$c] : '';
        }
        fputcsv($output, $fila);
    }
} catch (Throwable $e) {
    // Registrar el error en el log de PHP y enviar mensaje genérico
    error_log('Error exportar_compras: ' . $e->getMessage());
    fputcsv($output, ['Error al generar reporte']);
}

fclose($output);
exit;
?>
