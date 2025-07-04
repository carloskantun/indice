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

$anio = date('Y');
$prefix = "G-$anio-";
$count = $conn->query("SELECT COUNT(*) AS total FROM gastos WHERE folio LIKE '$prefix%'")->fetch_assoc()['total'] + 1;
$folio = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);

$estatus = 'Pagado';
if ($origen === 'Orden') {
    $estatus = 'Abonado';
}

$dias_periodicidad = [
    'Diario'     => 1,
    'Semanal'    => 7,
    'Quincenal'  => 15,
    'Mensual'    => 30
];
$meses_plazo = [
    'Trimestral' => 3,
    'Semestral'  => 6,
    'Anual'      => 12
];

$conn->begin_transaction();
try{
    $stmt = $conn->prepare("INSERT INTO gastos (folio, proveedor_id, monto, fecha_pago, unidad_negocio_id, tipo_gasto, medio_pago, cuenta_bancaria, estatus, concepto, orden_folio, origen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if($tipo_gasto==='Recurrente'){
        if(!isset($dias_periodicidad[$periodicidad]) || !isset($meses_plazo[$plazo])){
            throw new Exception('Datos de recurrencia inválidos');
        }
        $repeticiones = intval(($meses_plazo[$plazo]*30) / $dias_periodicidad[$periodicidad]);
        for($i=0;$i<$repeticiones;$i++){
            $sufijo = letraSufijo($i);
            $folio_i = $folio.'-'.$sufijo;
            $fecha_i = date('Y-m-d', strtotime($fecha_pago.' +'.($i*$dias_periodicidad[$periodicidad]).' days'));
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
