<?php
header('Content-Type: application/json');
include 'conexion.php';

$res = [];

$cond = "WHERE 1=1";
if (!empty($_GET['unidad_negocio'])) {
  $cond .= " AND unidad_negocio_id=".(int)$_GET['unidad_negocio'];
}
if (!empty($_GET['fecha_inicio'])) $cond .= " AND fecha>='".$conn->real_escape_string($_GET['fecha_inicio'])."'";
if (!empty($_GET['fecha_fin'])) $cond .= " AND fecha<='".$conn->real_escape_string($_GET['fecha_fin'])."'";

$totales = $conn->query("SELECT COUNT(*) AS total FROM ordenes_lavanderia $cond")->fetch_assoc();
$res['total_servicios'] = (int)$totales['total'];

$mes = date('Y-m');
$mes_total = $conn->query("SELECT COUNT(*) AS t FROM ordenes_lavanderia WHERE DATE_FORMAT(fecha,'%Y-%m')='$mes'")->fetch_assoc();
$res['total_mes'] = (int)$mes_total['t'];

$ingresos = $conn->query("SELECT SUM(monto) AS m FROM ordenes_lavanderia $cond")->fetch_assoc();
$res['ingresos'] = round((float)$ingresos['m'],2);

$prendas = [];
$q = $conn->query("SELECT prenda, SUM(cantidad) as c FROM ordenes_lavanderia $cond GROUP BY prenda ORDER BY c DESC LIMIT 5");
while($r=$q->fetch_assoc()){ $prendas[$r['prenda']] = (int)$r['c']; }
$res['prendas'] = $prendas;

echo json_encode($res);
?>
