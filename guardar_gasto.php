<?php
include 'conexion.php';
session_start();

function letraSufijo(int $num): string {
    $s = '';
    while($num >= 0){
        $s = chr(65 + ($num % 26)) . $s;
        $num = intdiv($num,26) - 1;
    }
    return $s;
}

function generarFechasRecurrentes(array $config){
    $inicio = new DateTime($config['fecha_inicio']);
    $fin = (clone $inicio)->modify("+{$config['plazo_meses']} months");
    $fechas = [];
    $tipo = $config['frecuencia'];
    $patron = $config['patron'];

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
        $dias = [];
        foreach ($patron['dias_semana'] ?? [] as $d) {
            $clave = strtolower($d);
            if (isset($dias_map[$clave])) {
                $dias[] = $dias_map[$clave];
            }
        }
        while ($inicio <= $fin) {
            if (in_array((int)$inicio->format('w'), $dias)) {
                $fechas[] = $inicio->format('Y-m-d');
            }
            $inicio->modify('+1 day');
        }
    } elseif ($tipo === 'mensual') {
        while ($inicio <= $fin) {
            foreach ($patron['dias_mes'] ?? [] as $dia) {
                try {
                    $fecha = new DateTime($inicio->format("Y-m-") . str_pad($dia, 2, '0', STR_PAD_LEFT));
                    if ($fecha >= new DateTime($config['fecha_inicio']) && $fecha <= $fin) {
                        $fechas[] = $fecha->format('Y-m-d');
                    }
                } catch(Exception $e){}
            }
            $inicio->modify('first day of next month');
        }
    } elseif ($tipo === 'personalizada') {
        foreach ($patron['fechas_exactas'] ?? [] as $f) {
            $fecha = new DateTime($f);
            if ($fecha >= new DateTime($config['fecha_inicio']) && $fecha <= $fin) {
                $fechas[] = $fecha->format('Y-m-d');
            }
        }
    }

    sort($fechas);
    return array_values(array_unique($fechas));
}

// Recibir datos del POST
$proveedor_id = $_POST['proveedor_id'] ?? null;
$monto = $_POST['monto'] ?? null;
$fecha_pago = $_POST['fecha_pago'] ?? null;
$unidad_id = $_POST['unidad_negocio_id'] ?? null;
$tipo_gasto = $_POST['tipo_gasto'] ?? 'Unico';
$tipo_compra = $_POST['tipo_compra'] ?? null;
$periodicidad = $_POST['periodicidad'] ?? null;
$plazo = $_POST['plazo'] ?? null;
$medio_pago = $_POST['medio_pago'] ?? 'Transferencia';
$cuenta = $_POST['cuenta_bancaria'] ?? null;
$concepto = $_POST['concepto'] ?? null;
$origen = $_POST['origen'] ?? 'Directo';
$orden_folio = $_POST['orden_folio'] ?? null;

$archivo_comprobante = null;
if (isset($_FILES['comprobante_gasto']) && is_uploaded_file($_FILES['comprobante_gasto']['tmp_name'])) {
    $ext = strtolower(pathinfo($_FILES['comprobante_gasto']['name'], PATHINFO_EXTENSION));
    $permitidos = ['jpg', 'jpeg', 'png', 'pdf'];
    if (in_array($ext, $permitidos)) {
        if (!is_dir('uploads/comprobantes')) mkdir('uploads/comprobantes', 0777, true);
        $nombre = uniqid('comp_') . '.' . $ext;
        $destino = 'uploads/comprobantes/' . $nombre;
        move_uploaded_file($_FILES['comprobante_gasto']['tmp_name'], $destino);
        $archivo_comprobante = $destino;
    }
}

if (!$proveedor_id || !$monto || !$fecha_pago || !$unidad_id) {
    echo 'Faltan datos';
    exit;
}

$nuevo_id = $conn->query("SELECT IFNULL(MAX(id),0)+1 AS nuevo_id FROM gastos")->fetch_assoc()['nuevo_id'];
$prefijo = ($origen === 'Orden') ? 'OC-' : 'G-';
$folio = $prefijo . str_pad($nuevo_id, 3, '0', STR_PAD_LEFT);
$hoy = date('Y-m-d');
$estatus = ($origen === 'Orden') ? (($fecha_pago < $hoy) ? 'Vencido' : 'Por pagar') : 'Pagado';

$meses_plazo = [
    'Trimestral' => 3,
    'Semestral'  => 6,
    'Anual'      => 12
];

$conn->begin_transaction();
try {
    $sql = "INSERT INTO gastos 
    (folio, proveedor_id, monto, fecha_pago, unidad_negocio_id, tipo_gasto, tipo_compra, medio_pago, cuenta_bancaria, estatus, concepto, orden_folio, origen, archivo_comprobante)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($tipo_gasto === 'Recurrente') {
        $plazo_meses = intval($_POST['plazo_meses'] ?? ($meses_plazo[$plazo] ?? 0));
        $map_freq = ['Diario'=>'diaria','Semanal'=>'semanal','Quincenal'=>'quincenal_calendario','Mensual'=>'mensual'];
        $frecuencia = $_POST['frecuencia'] ?? ($map_freq[$periodicidad] ?? 'diaria');
        $patron = [
            'dias_mes' => $_POST['dias_mes'] ?? [],
            'dias_semana' => $_POST['dias_semana'] ?? [],
            'fechas_exactas' => $_POST['fechas_exactas'] ?? []
        ];

        if (empty($patron['dias_semana']) && $frecuencia === 'semanal') {
            $dias_nombre = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
            $patron['dias_semana'] = [$dias_nombre[(int)date('w', strtotime($fecha_pago))]];
        }
        if (empty($patron['dias_mes']) && $frecuencia === 'mensual') {
            $patron['dias_mes'] = [intval(date('d', strtotime($fecha_pago)))];
        }

        $config = [
            'fecha_inicio' => $fecha_pago,
            'plazo_meses' => $plazo_meses,
            'frecuencia' => $frecuencia,
            'patron' => $patron
        ];

        $fechas = generarFechasRecurrentes($config);

        foreach ($fechas as $i => $fecha_i) {
            $sufijo = letraSufijo($i);
            $folio_i = $folio . '-' . $sufijo;

            // crear variables por separado para evitar error de referencia
            $folio_b = $folio_i;
            $prov_b = $proveedor_id;
            $monto_b = $monto;
            $fecha_b = $fecha_i;
            $unidad_b = $unidad_id;
            $tipo_gasto_b = $tipo_gasto;
            $tipo_compra_b = $tipo_compra;
            $medio_b = $medio_pago;
            $cuenta_b = $cuenta;
            $estatus_b = $estatus;
            $concepto_b = $concepto;
            $orden_b = $orden_folio;
            $origen_b = $origen;
            $archivo_b = $archivo_comprobante;

            $stmt->bind_param('sidsisssssssss', $folio_b, $prov_b, $monto_b, $fecha_b, $unidad_b, $tipo_gasto_b, $tipo_compra_b, $medio_b, $cuenta_b, $estatus_b, $concepto_b, $orden_b, $origen_b, $archivo_b);
            if (!$stmt->execute()) throw new Exception($stmt->error);

            $gasto_id = $conn->insert_id;
            if ($origen === 'Directo' && $archivo_comprobante) {
                $ab = $conn->prepare("INSERT INTO abonos_gastos (gasto_id, monto, fecha, comentario, archivo_comprobante) VALUES (?,?,?,?,?)");
                $comentario = '';
                $ab->bind_param('idsss', $gasto_id, $monto, $fecha_i, $comentario, $archivo_comprobante);
                $ab->execute();
            }
        }
    } else {
        // únicos
        $folio_u = $folio;
        $prov_u = $proveedor_id;
        $monto_u = $monto;
        $fecha_u = $fecha_pago;
        $unidad_u = $unidad_id;
        $tipo_gasto_u = $tipo_gasto;
        $tipo_compra_u = $tipo_compra;
        $medio_u = $medio_pago;
        $cuenta_u = $cuenta;
        $estatus_u = $estatus;
        $concepto_u = $concepto;
        $orden_u = $orden_folio;
        $origen_u = $origen;
        $archivo_u = $archivo_comprobante;
        $stmt->bind_param('sidsisssssssss', $folio_u, $prov_u, $monto_u, $fecha_u, $unidad_u, $tipo_gasto_u, $tipo_compra_u, $medio_u, $cuenta_u, $estatus_u, $concepto_u, $orden_u, $origen_u, $archivo_u);
        if (!$stmt->execute()) throw new Exception($stmt->error);

        $gasto_id = $conn->insert_id;
        if ($origen === 'Directo' && $archivo_comprobante) {
            $ab = $conn->prepare("INSERT INTO abonos_gastos (gasto_id, monto, fecha, comentario, archivo_comprobante) VALUES (?,?,?,?,?)");
            $comentario = '';
            $ab->bind_param('idsss', $gasto_id, $monto, $fecha_pago, $comentario, $archivo_comprobante);
            $ab->execute();
        }
    }

    $conn->commit();
    echo 'ok';
} catch (Exception $e) {
    $conn->rollback();
    echo 'Error al guardar: ' . $e->getMessage();
}
<<<<<<< Updated upstream
?>
=======
?>
>>>>>>> Stashed changes
