<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=export_mantenimiento.csv');

include 'conexion.php';

$output = fopen('php://output', 'w');

// Mapas
$mapa_columnas = [
    'folio' => 'folio',
    'fecha' => 'fecha_reporte',
    'descripcion' => 'descripcion_reporte',
    'estatus' => 'estatus',
    'nivel' => 'nivel',
    'fecha_completado' => 'fecha_completado',
    'detalle_completado' => 'detalle_completado',
    'costo_final' => 'costo_final',
    'alojamiento' => '(SELECT nombre FROM alojamientos WHERE id = alojamiento_id)',
    'usuario' => '(SELECT nombre FROM usuarios WHERE id = usuario_solicitante_id)',
    'unidad_negocio' => '(SELECT nombre FROM unidades_negocio WHERE id = unidad_negocio_id)',
    'foto' => 'foto',
    'foto_completado' => 'foto_completado',
    'quien_pago' => '(SELECT nombre FROM usuarios WHERE id = quien_realizo_id)',
    'completar' => 'estatus'
];

$mapa_titulos = [
    'folio' => 'Folio',
    'fecha' => 'Fecha Reporte',
    'descripcion' => 'Descripción',
    'estatus' => 'Estatus',
    'nivel' => 'Nivel',
    'fecha_completado' => 'Fecha Ejecución',
    'detalle_completado' => 'Detalle Completado',
    'costo_final' => 'Costo Final',
    'alojamiento' => 'Alojamiento',
    'usuario' => 'Usuario Solicitante',
    'unidad_negocio' => 'Unidad de Negocio',
    'foto' => 'Foto',
    'foto_completado' => 'Foto Final',
    'quien_pago' => 'Quién Realizó',
    'completar' => 'Estado Avance'
];

// Sanitizar columnas recibidas
$columnas = array_unique(array_filter(explode(',', $_GET['columnas'] ?? '')));
$columnas = array_values(array_intersect($columnas, array_keys($mapa_columnas)));

// Validar que haya columnas válidas
if (empty($columnas)) {
    fputcsv($output, ['Error: No se especificaron columnas válidas.']);
    fclose($output);
    exit;
}

// Encabezados CSV
$titulos = array_map(fn($col) => $mapa_titulos[$col] ?? ucfirst($col), $columnas);
fputcsv($output, $titulos);

// Construcción de la consulta
$campos_sql = array_map(fn($col) => $mapa_columnas[$col] . " AS $col", $columnas);
$query = "SELECT " . implode(", ", $campos_sql) . " FROM ordenes_mantenimiento WHERE 1=1";

// Filtros
if (!empty($_GET['alojamiento']) && is_array($_GET['alojamiento'])) {
    $ids = array_map('intval', $_GET['alojamiento']);
    $query .= " AND alojamiento_id IN (" . implode(',', $ids) . ")";
}

if (!empty($_GET['estatus'])) {
    $estatus = trim($conn->real_escape_string($_GET['estatus']));
    $query .= " AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = '$estatus'";
}

if (!empty($_GET['usuario']) && is_array($_GET['usuario'])) {
    $ids = array_map('intval', $_GET['usuario']);
    $query .= " AND usuario_solicitante_id IN (" . implode(',', $ids) . ")";
}

if (!empty($_GET['unidad_negocio']) && is_array($_GET['unidad_negocio'])) {
    $ids = array_map('intval', $_GET['unidad_negocio']);
    $query .= " AND unidad_negocio_id IN (" . implode(',', $ids) . ")";
}

if (!empty($_GET['fecha_inicio'])) {
    $fecha_inicio = $conn->real_escape_string($_GET['fecha_inicio']);
    $query .= " AND fecha_reporte >= '$fecha_inicio'";
}
if (!empty($_GET['fecha_fin'])) {
    $fecha_fin = $conn->real_escape_string($_GET['fecha_fin']);
    $query .= " AND fecha_reporte <= '$fecha_fin'";
}

// Orden dinámico
$orden = $_GET['orden'] ?? 'folio';
$dir = strtoupper($_GET['dir'] ?? 'ASC');
$orden_sql = $mapa_columnas[$orden] ?? 'folio';
$dir = in_array($dir, ['ASC', 'DESC']) ? $dir : 'ASC';

$query .= " ORDER BY $orden_sql $dir";

// Ejecutar
$resultado = $conn->query($query);
if (!$resultado) {
    fputcsv($output, ['Error en la consulta.']);
    fclose($output);
    exit;
}

// Escribir datos
while ($row = $resultado->fetch_assoc()) {
    $fila = [];
    foreach ($columnas as $col) {
        $valor = trim($row[$col] ?? '');

        // URL para imágenes
        if (in_array($col, ['foto', 'foto_completado']) && $valor !== '') {
            $valor = rutaPublicaDesdeRelativa($valor);
        }

        // Texto largo
        if (in_array($col, ['descripcion', 'detalle_completado'])) {
            $valor = str_replace(["\r", "\n"], [' ', ' '], $valor);
        }

        $fila[] = ($valor === '') ? '—' : $valor;
    }
    fputcsv($output, $fila);
}

fclose($output);
exit;

// Función auxiliar
function rutaPublicaDesdeRelativa($ruta) {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $base . '/' . ltrim($ruta, '/');
}