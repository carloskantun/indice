<?php
include 'conexion.php';
session_start();

function letraSufijo(int $num): string {
    $s = '';
    $numOrig = $num;
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
        $dias_map = ['lunes'=>1,'martes'=>2,'miércoles'=>3,'jueves'=>4,'viernes'=>5,'sábado'=>6,'domingo'=>0];
        $dias = array_map(fn($d)=>$dias_map[strtolower($d)], $patron['dias_semana'] ?? []);
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
                    $fecha = new DateTime($inicio->format('Y-m-') . str_pad($dia, 2, '0', STR_PAD_LEFT));
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

$proveedor_id = $_POST['proveedor_id'] ?? null;
$monto = $_POST['monto'] ?? null;
$fecha_pago = $_POST['fecha_pago'] ?? null;
$unidad_id = $_POST['unidad_negocio_id'] ?? null;
$tipo_gasto = $_POST['tipo_gasto'] ?? 'Unico';
$periodicidad = $_POST['periodicidad'] ?? null;
$plazo = $_POST['plazo'] ?? null;
$medio_pago = $_POST['medio_pago'] ?? 'Transferencia';
$cuenta = $_POST['cuenta_bancaria'] ?? null;
$concepto = $_POST['concepto'] ?? null;
$origen = $_POST['origen'] ?? 'Directo';
$orden_folio = $_POST['orden_folio'] ?? null;

if (!$proveedor_id || !$monto || !$fecha_pago || !$unidad_id) {
    echo 'Faltan datos';
    exit;
}

$nuevo_id = $conn->query("SELECT IFNULL(MAX(id),0)+1 AS nuevo_id FROM gastos")->fetch_assoc()['nuevo_id'];
$prefijo = ($_POST['origen'] === 'Orden') ? 'OC-' : 'G-';
$folio = $prefijo . str_pad($nuevo_id, 3, '0', STR_PAD_LEFT);

$estatus = 'Pagado';
if ($origen === 'Orden') {
    $estatus = 'Abonado';
}

$meses_plazo = [
    'Trimestral' => 3,
    'Semestral'  => 6,
    'Anual'      => 12
];

$conn->begin_transaction();
try{
    $stmt = $conn->prepare("INSERT INTO gastos (folio, proveedor_id, monto, fecha_pago, unidad_negocio_id, tipo_gasto, medio_pago, cuenta_bancaria, estatus, concepto, orden_folio, origen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if($tipo_gasto==='Recurrente'){
        $plazo_meses = intval($_POST['plazo_meses'] ?? ($meses_plazo[$plazo] ?? 0));
        $map_freq = ['Diario'=>'diaria','Semanal'=>'semanal','Quincenal'=>'quincenal_calendario','Mensual'=>'mensual'];
        $frecuencia = $_POST['frecuencia'] ?? ($map_freq[$periodicidad] ?? 'diaria');
        $patron = [
            'dias_mes' => $_POST['dias_mes'] ?? [],
            'dias_semana' => $_POST['dias_semana'] ?? [],
            'fechas_exactas' => $_POST['fechas_exactas'] ?? []
        ];
        if(empty($patron['dias_semana']) && $frecuencia==='semanal'){
            $dias_nombre = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
            $patron['dias_semana'] = [$dias_nombre[(int)date('w', strtotime($fecha_pago))]];
        }
        if(empty($patron['dias_mes']) && $frecuencia==='mensual'){
            $patron['dias_mes'] = [intval(date('d', strtotime($fecha_pago)))];
        }

        $config = [
            'fecha_inicio' => $fecha_pago,
            'plazo_meses' => $plazo_meses,
            'frecuencia' => $frecuencia,
            'patron' => $patron
        ];

        $fechas = generarFechasRecurrentes($config);

        foreach($fechas as $i => $fecha_i){
            $sufijo = letraSufijo($i);
            $folio_i = $folio.'-'.$sufijo;
            $stmt->bind_param('sidsisssssss', $folio_i, $proveedor_id, $monto, $fecha_i, $unidad_id, $tipo_gasto, $medio_pago, $cuenta, $estatus, $concepto, $orden_folio, $origen);
            if(!$stmt->execute()) throw new Exception($stmt->error);
        }
    }else{
        $stmt->bind_param('sidsisssssss', $folio, $proveedor_id, $monto, $fecha_pago, $unidad_id, $tipo_gasto, $medio_pago, $cuenta, $estatus, $concepto, $orden_folio, $origen);
        if(!$stmt->execute()) throw new Exception($stmt->error);
    }
    $conn->commit();
    echo 'ok';
}catch(Exception $e){
    $conn->rollback();
    echo 'Error al guardar: '.$e->getMessage();
}
?>
