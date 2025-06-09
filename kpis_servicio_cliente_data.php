<?php
include 'auth.php';
include 'conexion.php';
header('Content-Type: application/json');

$kpis = include 'kpis_servicio_cliente_data_core.php';

echo json_encode($kpis);

