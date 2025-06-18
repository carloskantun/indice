<?php
include 'conexion.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=transfers.csv');

$cols = ['folio','tipo','fecha','pickup','hotel','pasajeros','numero_reserva','vehiculo','conductor','agencia','estatus'];
$columnas = array_intersect($cols, explode(',', $_GET['columnas'] ?? implode(',', $cols)));
if(empty($columnas)) $columnas=$cols;

$where = "WHERE 1=1";
if (!empty($_GET['tipo'])) { $t=$conn->real_escape_string($_GET['tipo']); $where.=" AND tipo='$t'"; }
if (!empty($_GET['agencia'])) { $a=$conn->real_escape_string($_GET['agencia']); $where.=" AND agencia LIKE '%$a%'"; }
if (!empty($_GET['operador'])) { $op=(int)$_GET['operador']; $where.=" AND usuario_creador_id=$op"; }
if (!empty($_GET['fecha_inicio'])) { $fi=$conn->real_escape_string($_GET['fecha_inicio']); $where.=" AND fecha>='$fi'"; }
if (!empty($_GET['fecha_fin'])) { $ff=$conn->real_escape_string($_GET['fecha_fin']); $where.=" AND fecha<='$ff'"; }

$f = fopen('php://output','w');
fputcsv($f,$columnas);
$q="SELECT ".implode(',', $columnas)." FROM ordenes_transfers $where";
$res=$conn->query($q);
while($row=$res->fetch_assoc()){
    fputcsv($f,array_map('utf8_decode',$row));
}
exit;
?>
