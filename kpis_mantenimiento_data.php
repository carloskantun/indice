<?php
include 'auth.php';
include 'conexion.php';
header('Content-Type: application/json');

// Delegar la obtención de métricas al archivo core
$kpis = include 'kpis_mantenimiento_data_core.php';

echo json_encode($kpis);

