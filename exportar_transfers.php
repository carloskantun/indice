<?php
include 'conexion.php';

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=transfers.csv");

$output = fopen("php://output", "w");

$columnas_validas = [
    'folio' => 'folio',
    'tipo' => 'tipo_servicio AS tipo',
    'fecha' => 'fecha_servicio AS fecha',
    'pickup' => 'pickup',
    'hotel' => 'hotel_pickup AS hotel',
    'pasajeros' => 'nombre_pasajeros AS pasajeros',
    'numero_reserva' => 'num_pasajeros AS numero_reserva',
    'vehiculo' => 'vehiculo',
    'conductor' => 'conductor',
    'agencia' => 'agencia',
    'estatus' => 'estatus'
];

$columnas_get = explode(',', $_GET['columnas'] ?? '');
$seleccionadas = [];

foreach ($columnas_get as $col) {
    $col = trim($col);
    if (isset($columnas_validas[$col])) {
        $seleccionadas[$col] = $columnas_validas[$col];
    }
}

if (empty($seleccionadas)) {
    die("No hay columnas válidas");
}

// Filtrado
$where = "WHERE 1=1";
if (!empty($_GET['tipo'])) {
    $tipo = $conn->real_escape_string($_GET['tipo']);
    $where .= " AND tipo_servicio = '$tipo'";
}
if (!empty($_GET['agencia'])) {
    $agencia = $conn->real_escape_string($_GET['agencia']);
    $where .= " AND agencia LIKE '%$agencia%'";
}
if (!empty($_GET['operador'])) {
    $operador = (int)$_GET['operador'];
    $where .= " AND usuario_solicitante_id = $operador";
}
if (!empty($_GET['fecha_inicio'])) {
    $fi = $conn->real_escape_string($_GET['fecha_inicio']);
    $where .= " AND fecha_servicio >= '$fi'";
}
if (!empty($_GET['fecha_fin'])) {
    $ff = $conn->real_escape_string($_GET['fecha_fin']);
    $where .= " AND fecha_servicio <= '$ff'";
}

// Ordenamiento
$mapa_orden_sql = array_keys($columnas_validas);
$orden = in_array($_GET['orden'] ?? '', $mapa_orden_sql) ? $_GET['orden'] : 'id';
$dir = (strtoupper($_GET['dir'] ?? '') === 'DESC') ? 'DESC' : 'ASC';

$sql = "SELECT " . implode(", ", $seleccionadas) . " FROM ordenes_transfers $where ORDER BY $orden $dir";
$result = $conn->query($sql);

// Cabeceras
fputcsv($output, array_keys($seleccionadas));

// Datos
while ($row = $result->fetch_assoc()) {
    $linea = [];
    foreach (array_keys($seleccionadas) as $key) {
        $linea[] = $row[$key];
    }
    fputcsv($output, $linea);
}

fclose($output);
exit;
?>
