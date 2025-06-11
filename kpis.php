<?php
session_start();
include 'auth.php';
include 'conexion.php';

// Obtener año y mes seleccionados (por defecto, el mes y año actual)
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');

// KPIs de órdenes de compra
$monto_vencidas_anual = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE estatus_pago = 'Vencido' AND YEAR(fecha_creacion) = $anio")->fetch_assoc()['total'] ?? 0;
$monto_vencidas_mes = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE estatus_pago = 'Vencido' AND YEAR(fecha_creacion) = $anio AND MONTH(fecha_creacion) = $mes")->fetch_assoc()['total'] ?? 0;
$monto_total_mes = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE YEAR(fecha_creacion) = $anio AND MONTH(fecha_creacion) = $mes")->fetch_assoc()['total'] ?? 0;
$monto_pagado_mes = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE estatus_pago = 'Pagado' AND YEAR(fecha_creacion) = $anio AND MONTH(fecha_creacion) = $mes")->fetch_assoc()['total'] ?? 0;
$porcentaje_liquidadas_mes = ($monto_total_mes > 0) ? round(($monto_pagado_mes / $monto_total_mes) * 100, 2) : 0;

// Montos por estatus
$monto_pendiente = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE estatus_pago = 'Por pagar' AND YEAR(fecha_creacion) = $anio AND MONTH(fecha_creacion) = $mes")->fetch_assoc()['total'] ?? 0;
$monto_por_vencer = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE estatus_pago = 'Por pagar' AND vencimiento_pago >= CURDATE() AND YEAR(fecha_creacion) = $anio AND MONTH(fecha_creacion) = $mes")->fetch_assoc()['total'] ?? 0;
$monto_vencido = $conn->query("SELECT SUM(monto) AS total FROM ordenes_compra WHERE estatus_pago = 'Vencido' AND YEAR(fecha_creacion) = $anio AND MONTH(fecha_creacion) = $mes")->fetch_assoc()['total'] ?? 0;

// Datos para gráficos
$ordenes_por_estatus = $conn->query("SELECT estatus_pago, SUM(monto) AS total FROM ordenes_compra WHERE YEAR(fecha_creacion) = $anio AND MONTH(fecha_creacion) = $mes GROUP BY estatus_pago");
$ordenes_por_tipo_pago = $conn->query("SELECT tipo_pago, SUM(monto) AS total FROM ordenes_compra WHERE YEAR(fecha_creacion) = $anio AND MONTH(fecha_creacion) = $mes GROUP BY tipo_pago");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPIs - Detalles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <nav class="navbar navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand">KPIs Detallados</a>
            <a href="minipanel.php" class="btn btn-outline-primary btn-sm">⬅ Regresar</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="text-center mb-4">Indicadores Clave de Rendimiento</h2>
        <div class="row row-cols-1 row-cols-md-3 g-3">
            <div class="col"><div class="card bg-light mb-3"><div class="card-body"><h6>Órdenes de compra vencidas (Anual)</h6><p>$<?php echo number_format($monto_vencidas_anual, 2); ?></p></div></div></div>
            <div class="col"><div class="card bg-light mb-3"><div class="card-body"><h6>Órdenes de compra vencidas (Mes)</h6><p>$<?php echo number_format($monto_vencidas_mes, 2); ?></p></div></div></div>
            <div class="col"><div class="card bg-light mb-3"><div class="card-body"><h6>Total de órdenes de compra (Mes)</h6><p>$<?php echo number_format($monto_total_mes, 2); ?></p></div></div></div>
            <div class="col"><div class="card bg-light mb-3"><div class="card-body"><h6>% de órdenes del mes liquidadas</h6><p><?php echo $porcentaje_liquidadas_mes; ?>%</p></div></div></div>
        </div>

        <h3 class="mt-4">Montos Totales</h3>
        <table class="table table-bordered">
            <tr><th>Total Gastado</th><td>$<?php echo number_format($monto_total_mes, 2); ?></td></tr>
            <tr><th>Gastos Pendientes</th><td>$<?php echo number_format($monto_pendiente, 2); ?></td></tr>
            <tr><th>Gastos por Vencer</th><td>$<?php echo number_format($monto_por_vencer, 2); ?></td></tr>
            <tr><th>Gastos Vencidos</th><td>$<?php echo number_format($monto_vencido, 2); ?></td></tr>
        </table>

        <h3 class="mt-4">Órdenes por Estatus</h3>
        <canvas id="chartEstatus"></canvas>
        <h3 class="mt-4">Órdenes por Tipo de Pago</h3>
        <canvas id="chartTipoPago"></canvas>
    </div>

    <script>
        new Chart(document.getElementById('chartEstatus').getContext('2d'), {
            type: 'bar',
            data: { 
                labels: [<?php while ($row = $ordenes_por_estatus->fetch_assoc()) echo "'".$row['estatus_pago']."',"; ?>], 
                datasets: [{
                    label: 'Monto ($)',
                    data: [<?php $ordenes_por_estatus->data_seek(0); while ($row = $ordenes_por_estatus->fetch_assoc()) echo $row['total'].","; ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            }
        });

        new Chart(document.getElementById('chartTipoPago').getContext('2d'), {
            type: 'pie',
            data: { 
                labels: [<?php while ($row = $ordenes_por_tipo_pago->fetch_assoc()) echo "'".$row['tipo_pago']."',"; ?>], 
                datasets: [{
                    data: [<?php $ordenes_por_tipo_pago->data_seek(0); while ($row = $ordenes_por_tipo_pago->fetch_assoc()) echo $row['total'].","; ?>],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                }]
            }
        });
    </script>
</body>
</html>
