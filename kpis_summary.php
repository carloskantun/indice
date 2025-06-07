<?php
session_start();
include 'auth.php';
include 'conexion.php';

header('Content-Type: application/json');

// Obtener año y mes seleccionados (por defecto, el mes y año actual)
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');

// KPIs de montos de órdenes de compra vencidas
$monto_vencidas_anual = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE estatus_pago = 'Vencido' AND YEAR(vencimiento_pago) = $anio")->fetch_assoc()['total'] ?? 0;
$monto_vencidas_mes = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE estatus_pago = 'Vencido' AND YEAR(vencimiento_pago) = $anio AND MONTH(vencimiento_pago) = $mes")->fetch_assoc()['total'] ?? 0;

// Total de órdenes de compra del mes (en montos)
$monto_total_mes = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE YEAR(vencimiento_pago) = $anio AND MONTH(vencimiento_pago) = $mes")->fetch_assoc()['total'] ?? 0;

// % de órdenes del mes liquidadas (en montos)
$monto_pagado_mes = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE estatus_pago = 'Pagado' AND YEAR(vencimiento_pago) = $anio AND MONTH(vencimiento_pago) = $mes")->fetch_assoc()['total'] ?? 0;
$porcentaje_liquidadas_mes = $monto_total_mes > 0 ? round(($monto_pagado_mes / $monto_total_mes) * 100, 2) : 0;

// Respuesta en JSON con montos correctamente formateados
echo json_encode([
    "monto_vencidas_anual" => number_format($monto_vencidas_anual, 2, '.', ''),
    "monto_vencidas_mes" => number_format($monto_vencidas_mes, 2, '.', ''),
    "monto_total_mes" => number_format($monto_total_mes, 2, '.', ''),
    "porcentaje_liquidadas_mes" => $porcentaje_liquidadas_mes
]);
?>
