<?php
header('Content-Type: application/json');
include 'conexion.php';

$response = [
    'totales' => [],
    'tipos' => []
];

// Conteo por estatus
$sql1 = "SELECT estatus, COUNT(*) as total FROM ordenes_transfers GROUP BY estatus";
$res1 = $conn->query($sql1);
$totales = ['Pendiente' => 0, 'En proceso' => 0, 'Terminado' => 0, 'Cancelado' => 0];
while ($row = $res1->fetch_assoc()) {
    $totales[$row['estatus']] = (int) $row['total'];
}
$totales['total'] = array_sum($totales);
$response['totales'] = $totales;

// Conteo por tipo de servicio
$sql2 = "SELECT tipo_servicio, COUNT(*) as total FROM ordenes_transfers GROUP BY tipo_servicio";
$res2 = $conn->query($sql2);
$tipos = [];
while ($row = $res2->fetch_assoc()) {
    $tipos[$row['tipo_servicio']] = (int) $row['total'];
}
$response['tipos'] = $tipos;

echo json_encode($response);
?>
