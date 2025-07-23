<?php

session_start();
include 'auth.php';
include 'conexion.php';

// Detectar si la solicitud es AJAX
$isAjax = !empty($_POST['ajax']) ||
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

// Mostrar errores (modo desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function letraSufijo(int $num): string {
    $s = '';
    while($num >= 0){
        $s = chr(65 + ($num % 26)) . $s;
        $num = intdiv($num, 26) - 1;
    }
    return $s;
}

function generarFechasRecurrentes(array $config) {
    $inicio = new DateTime($config['fecha_inicio']);
    $fin = (clone $inicio)->modify("+{$config['plazo_meses']} months");
    $fechas = [];
    $tipo = $config['frecuencia'];

    if ($tipo === 'diaria') {
        while ($inicio <= $fin) {
            $fechas[] = $inicio->format('Y-m-d');
            $inicio->modify('+1 day');
        }
    } elseif ($tipo === 'quincenal_calendario') {
        while ($inicio <= $fin) {
            $f15 = new DateTime($inicio->format('Y-m-15'));
            $flast = new DateTime($inicio->format('Y-m-t'));
            if ($f15 >= new DateTime($config['fecha_inicio']) && $f15 <= $fin) $fechas[] = $f15->format('Y-m-d');
            if ($flast >= new DateTime($config['fecha_inicio']) && $flast <= $fin) $fechas[] = $flast->format('Y-m-d');
            $inicio->modify('first day of next month');
        }
    } elseif ($tipo === 'semanal') {
        $dias_map = ['domingo'=>0,'lunes'=>1,'martes'=>2,'miércoles'=>3,'jueves'=>4,'viernes'=>5,'sábado'=>6];
        $dias = [ (int) $inicio->format('w') ];  // día inicial
        while ($inicio <= $fin) {
            if (in_array((int)$inicio->format('w'), $dias)) {
                $fechas[] = $inicio->format('Y-m-d');
            }
            $inicio->modify('+1 day');
        }
    } elseif ($tipo === 'mensual') {
        $dia_mes = $inicio->format('d');
        while ($inicio <= $fin) {
            $fecha = new DateTime($inicio->format("Y-m-") . $dia_mes);
            if ($fecha >= new DateTime($config['fecha_inicio']) && $fecha <= $fin) {
                $fechas[] = $fecha->format('Y-m-d');
            }
            $inicio->modify('first day of next month');
        }
    }

    sort($fechas);
    return $fechas;
}

// Recibir POST
$proveedor_id = $_POST['proveedor_id'] ?? null;
$monto        = $_POST['monto'] ?? null;
$fecha_pago   = $_POST['fecha_pago'] ?? null;
$unidad_id    = $_POST['unidad_negocio_id'] ?? null;
$tipo_gasto   = $_POST['tipo_gasto'] ?? 'Unico';
$tipo_compra  = $_POST['tipo_compra'] ?? null;
$periodicidad = $_POST['periodicidad'] ?? null;
$plazo        = $_POST['plazo'] ?? null;
$medio_pago   = $_POST['medio_pago'] ?? 'Transferencia';
$cuenta       = $_POST['cuenta_bancaria'] ?? null;
$concepto     = $_POST['concepto'] ?? null;
$origen       = $_POST['origen'] ?? 'Directo';
$orden_folio  = $_POST['orden_folio'] ?? null;

// Subir múltiples comprobantes
$comprobantes_guardados = [];

if (isset($_FILES['comprobante']) && isset($_FILES['comprobante']['name']) && is_array($_FILES['comprobante']['name'])) {
    foreach ($_FILES['comprobante']['name'] as $idx => $nombre_original) {
        if (!is_uploaded_file($_FILES['comprobante']['tmp_name'][$idx])) continue;

        $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($ext, $permitidos)) continue;

        if (!is_dir('uploads/comprobantes')) mkdir('uploads/comprobantes', 0777, true);
        $nombre = uniqid('comp_') . '.' . $ext;
        $destino = 'uploads/comprobantes/' . $nombre;

        if (!move_uploaded_file($_FILES['comprobante']['tmp_name'][$idx], $destino)) continue;

        $comprobantes_guardados[] = $destino;
    }
}

// Guardar el primer comprobante como principal si hay varios (o null si ninguno)
$archivo_comprobante = $comprobantes_guardados[0] ?? null;


// Validación básica
if (!$proveedor_id || !$monto || !$fecha_pago || !$unidad_id) {
    echo 'Faltan datos obligatorios';
    exit;
}

// Folio
$nuevo_id = $conn->query("SELECT IFNULL(MAX(id),0)+1 AS nuevo_id FROM gastos")->fetch_assoc()['nuevo_id'];
$prefijo  = ($origen === 'Orden') ? 'OC-' : 'G-';
$folio    = $prefijo . str_pad($nuevo_id, 3, '0', STR_PAD_LEFT);

// Estatus
$hoy     = date('Y-m-d');
$estatus = ($origen === 'Orden') ? (($fecha_pago < $hoy) ? 'Vencido' : 'Por pagar') : 'Pagado';

$meses_plazo = [
    'Trimestral' => 3,
    'Semestral'  => 6,
    'Anual'      => 12
];

// Guardar
$conn->begin_transaction();
try {
    $sql = "INSERT INTO gastos 
        (folio, proveedor_id, monto, fecha_pago, unidad_negocio_id, tipo_gasto, tipo_compra, medio_pago, cuenta_bancaria, estatus, concepto, orden_folio, origen, archivo_comprobante)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Error al preparar: " . $conn->error);

if ($tipo_gasto === 'Recurrente' && empty($plazo)) {
    echo "Debe seleccionar un plazo para órdenes recurrentes";
    exit;
}

    if ($tipo_gasto === 'Recurrente') {
        $plazo_meses = $meses_plazo[$plazo] ?? 0;
        $map_freq = [
            'Diario'     => 'diaria',
            'Semanal'    => 'semanal',
            'Quincenal'  => 'quincenal_calendario',
            'Mensual'    => 'mensual'
        ];
        $frecuencia = $map_freq[$periodicidad] ?? 'mensual';

        $fechas = generarFechasRecurrentes([
            'fecha_inicio' => $fecha_pago,
            'plazo_meses'  => $plazo_meses,
            'frecuencia'   => $frecuencia,
            'patron'       => [] // ya no usamos días personalizados
        ]);

        if (empty($fechas)) throw new Exception("No se generaron fechas recurrentes");

        foreach ($fechas as $i => $fecha_i) {
            $folio_i = $folio . '-' . letraSufijo($i);
            $stmt->bind_param('sidsisssssssss', $folio_i, $proveedor_id, $monto, $fecha_i, $unidad_id, $tipo_gasto, $tipo_compra, $medio_pago, $cuenta, $estatus, $concepto, $orden_folio, $origen, $archivo_comprobante);
            if (!$stmt->execute()) throw new Exception($stmt->error);
        }
    } else {
        $stmt->bind_param('sidsisssssssss', $folio, $proveedor_id, $monto, $fecha_pago, $unidad_id, $tipo_gasto, $tipo_compra, $medio_pago, $cuenta, $estatus, $concepto, $orden_folio, $origen, $archivo_comprobante);
        if (!$stmt->execute()) throw new Exception($stmt->error);
    }

    $conn->commit();
    if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
} else {
    header('Location: gastos.php');
}
exit;

} catch (Exception $e) {
    $conn->rollback();
    file_put_contents('log_error_gastos.txt', date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, FILE_APPEND);
    echo 'Error al guardar. Revisa el log.';
}
?>
