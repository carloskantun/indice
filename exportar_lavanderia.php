<?php
include 'conexion.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=lavanderia.csv');

$cols = [
  'folio' => 'folio',
  'fecha' => 'fecha',
  'cliente' => 'cliente',
  'servicio' => 'servicio',
  'prenda' => 'prenda',
  'cantidad' => 'cantidad',
  'monto' => 'monto',
  'estatus' => 'estatus'
];
$seleccion = isset($_GET['columnas']) ? array_intersect(explode(',', $_GET['columnas']), array_keys($cols)) : array_keys($cols);
$seleccionadas = array_intersect_key($cols, array_flip($seleccion));

$where = "WHERE 1=1";
if (!empty($_GET['cliente'])) $where .= " AND cliente LIKE '%".$conn->real_escape_string($_GET['cliente'])."%'";
if (!empty($_GET['servicio'])) $where .= " AND servicio LIKE '%".$conn->real_escape_string($_GET['servicio'])."%'";
if (!empty($_GET['estatus'])) $where .= " AND estatus='".$conn->real_escape_string($_GET['estatus'])."'";
if (!empty($_GET['fecha_inicio'])) $where .= " AND fecha>='".$conn->real_escape_string($_GET['fecha_inicio'])."'";
if (!empty($_GET['fecha_fin'])) $where .= " AND fecha<='".$conn->real_escape_string($_GET['fecha_fin'])."'";

$orden = $cols[$_GET['orden'] ?? 'folio'] ?? 'folio';
$dir = (strtoupper($_GET['dir'] ?? '') === 'DESC')? 'DESC':'ASC';

$sql = "SELECT ".implode(',', $seleccionadas)." FROM ordenes_lavanderia $where ORDER BY $orden $dir";
$res = $conn->query($sql);

$out = fopen('php://output','w');
fputcsv($out, array_keys($seleccionadas));
while($row=$res->fetch_assoc()){
  fputcsv($out, $row);
}
?>
