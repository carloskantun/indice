<?php
include 'conexion.php';
header('Content-Type: application/json');
$where="WHERE 1=1";
if(!empty($_GET['fecha_inicio'])){ $fi=$conn->real_escape_string($_GET['fecha_inicio']); $where.=" AND fecha>='$fi'"; }
if(!empty($_GET['fecha_fin'])){ $ff=$conn->real_escape_string($_GET['fecha_fin']); $where.=" AND fecha<='$ff'"; }

function contar($conn,$where,$status=''){ $add=$status?" AND estatus='$status'":''; return (int)$conn->query("SELECT COUNT(*) AS c FROM ordenes_transfers $where$add")->fetch_assoc()['c']; }

$totales=[
    'total'=>contar($conn,$where),
    'pendientes'=>contar($conn,$where,"Pendiente"),
    'proceso'=>contar($conn,$where,"En proceso"),
    'terminados'=>contar($conn,$where,"Terminado"),
    'cancelados'=>contar($conn,$where,"Cancelado")
];

$agencias=[];
$res=$conn->query("SELECT agencia, COUNT(*) c FROM ordenes_transfers $where GROUP BY agencia");
while($r=$res->fetch_assoc()){ $agencias[$r['agencia']] = (int)$r['c']; }

$tipos=[];
$res=$conn->query("SELECT tipo, COUNT(*) c FROM ordenes_transfers $where GROUP BY tipo");
while($r=$res->fetch_assoc()){ $tipos[$r['tipo']] = (int)$r['c']; }

$data=['totales'=>$totales,'agencias'=>$agencias,'tipos'=>$tipos];
echo json_encode($data);
?>
