<?php
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

include 'conexion.php';

// ✅ Función robusta para convertir imágenes a base64 y manejar nombres problemáticos
function embedImage($url) {
    $relative = parse_url($url, PHP_URL_PATH);

    // Forzar que comience con "/"
    if (substr($relative, 0, 1) !== '/') {
        $relative = '/' . $relative;
    }

    // Reemplazar paréntesis y espacios por guiones bajos
    $cleanRelative = str_replace(['(', ')'], '', $relative);
    $cleanRelative = preg_replace('/\s+/', '_', $cleanRelative); // reemplaza espacios con "_"

    // Ruta completa
    $path = $_SERVER['DOCUMENT_ROOT'] . $cleanRelative;

    // Si no existe, registrar en log
    if (!file_exists($path)) {
        file_put_contents("debug_imagenes.log", date('Y-m-d H:i:s') . " ❌ No se encontró imagen: $path\n", FILE_APPEND);
        return ''; // No mostrar nada
    }

    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    return 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// ✅ Función para convertir ruta relativa (ej. uploads/xxx.jpg) a URL pública completa
// ✅ Función corregida para respetar subcarpeta como "/indice"
function rutaPublicaDesdeRelativa($rutaRelativa) {
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $base . '/' . ltrim($rutaRelativa, '/');
}

$folio = $_GET['folio'] ?? '';
if (empty($folio)) {
    die("Folio no proporcionado.");
}

// Consulta SQL con todos los campos necesarios
$query = "SELECT 
    om.folio, om.fecha_reporte, om.descripcion_reporte, om.foto, om.estatus, om.nivel,
    om.fecha_completado, om.detalle_completado, om.foto_completado, om.costo_final,
    a.nombre AS alojamiento,
    u.nombre AS usuario,
    un.nombre AS unidad_negocio
FROM ordenes_servicio_cliente om
LEFT JOIN alojamientos a ON om.alojamiento_id = a.id
LEFT JOIN usuarios u ON om.usuario_solicitante_id = u.id
LEFT JOIN unidades_negocio un ON om.unidad_negocio_id = un.id
WHERE om.folio = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $folio);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("No se encontró la orden.");
}
$orden = $result->fetch_assoc();
$estatus = $estatus ?? 'Pendiente';
if ($estatus === '') $estatus = 'Pendiente';

// Preparar DOMPDF
$dompdf = new Dompdf();
$dompdf->setPaper('letter');

// HTML del PDF
$html = '
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; }
    .seccion { margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
    th { background-color: #f2f2f2; }
    .titulo { background: #004085; color: white; padding: 10px; text-align: center; }
    .img-thumb { max-width: 200px; max-height: 150px; display: block; margin-top: 10px; }
</style>

<h2 class="titulo">Resumen de Tarea</h2>

<div class="seccion">
  <h4>Información del Reporte</h4>
  <table>
    <tr><th>Folio</th><td>' . $orden['folio'] . '</td></tr>
    <tr><th>Fecha del Reporte</th><td>' . $orden['fecha_reporte'] . '</td></tr>
    <tr><th>Alojamiento</th><td>' . $orden['alojamiento'] . '</td></tr>
    <tr><th>Unidad de Negocio</th><td>' . $orden['unidad_negocio'] . '</td></tr>
    <tr><th>Usuario Solicitante</th><td>' . $orden['usuario'] . '</td></tr>
    <tr><th>Descripción</th><td>' . $orden['descripcion_reporte'] . '</td></tr>
    <tr><th>Estatus</th><td>' . $estatus . '</td></tr>
    <tr><th>Nivel</th><td>' . $orden['nivel'] . '</td></tr>';

// ✅ Imagen del reporte como link
if (!empty($orden['foto'])) {
    $urlFoto = rutaPublicaDesdeRelativa($orden['foto']);
    $html .= '<tr><th>Foto de Reporte</th><td><a href="' . $urlFoto . '" target="_blank">Ver Foto</a></td></tr>';
} else {
    $html .= '<tr><th>Foto de Reporte</th><td>—</td></tr>';
}

$html .= '
  </table>
</div>

<div class="seccion">
  <h4>Información del Trabajo Completado</h4>
  <table>
    <tr><th>Fecha de Ejecución</th><td>' . ($orden['fecha_completado'] ?? '—') . '</td></tr>
    <tr><th>Detalle del Trabajo</th><td>' . ($orden['detalle_completado'] ?? '—') . '</td></tr>
    <tr><th>Costo Final</th><td>' . (isset($orden['costo_final']) && $orden['costo_final'] !== null ? '$' . number_format($orden['costo_final'], 2) : '—') . '</td></tr>';

// ✅ Imagen final como link
if (!empty($orden['foto_completado'])) {
    $urlFinal = rutaPublicaDesdeRelativa($orden['foto_completado']);
    $html .= '<tr><th>Foto Final</th><td><a href="' . $urlFinal . '" target="_blank">Ver Foto</a></td></tr>';
} else {
    $html .= '<tr><th>Foto Final</th><td>—</td></tr>';
}

$html .= '
  </table>
</div>';

// Cargar y generar PDF
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("reporte_servicio_cliente_{$orden['folio']}.pdf", ["Attachment" => false]);
exit;
?>
