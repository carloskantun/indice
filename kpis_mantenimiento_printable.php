<?php
include 'auth.php';
include 'conexion.php';

// Reutilizamos la lógica de cálculo como en kpis_mantenimiento_data.php
//include 'kpis_mantenimiento_data_core.php'; // Este archivo debe devolver $kpis (array de datos ya calculados)
$kpis = include 'kpis_mantenimiento_data_core.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Impresión KPIs Mantenimiento</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #fff; font-family: Arial; }
    .card-kpi {
      text-align: center;
      min-height: 130px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      border: 1px solid #ccc;
    }
    .card-kpi h3 { font-size: 1.8rem; }
    .section-title { margin-top: 40px; margin-bottom: 20px; font-weight: bold; font-size: 1.3rem; }
    .chart-container { margin-bottom: 40px; }
  </style>
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4 text-center">📊 KPIs de Mantenimiento (Vista para impresión)</h2>

  <!-- KPIs -->
  <div class="section-title">🛠️ Indicadores Operativos</div>
  <div class="row g-4 mb-4">
    <?php
    function card($label, $valor, $color) {
      echo "<div class='col-md-4'><div class='card-kpi text-$color'><h6>$label</h6><h3>$valor</h3></div></div>";
    }
    card('Total Reportes', $kpis['total'], 'primary');
    card('Pendientes', $kpis['pendientes'], 'secondary');
    card('En Proceso', $kpis['en_proceso'], 'warning');
    card('Terminados', $kpis['terminados'], 'success');
    card('Cancelados', $kpis['cancelados'], 'danger');
    card('Vencidos', $kpis['vencidos'], 'dark');
    ?>
  </div>

  <div class="section-title">💰 Indicadores Financieros</div>
  <div class="row g-4 mb-4">
    <?php
    card('Costo Total', '$' . number_format($kpis['costo_total'], 2), 'info');
    card('Costo Promedio', '$' . number_format($kpis['costo_promedio'], 2), 'secondary');
    ?>
  </div>

  <div class="section-title">🧠 Calidad y Documentación</div>
  <div class="row g-4 mb-4">
    <?php
    card('Promedio de Días', round($kpis['promedio_dias'], 1) . ' días', 'info');
    card('% Cumplimiento Mes', $kpis['cumplimiento_mes'] . '%', 'success');
    card('Productividad', round($kpis['productividad'], 1) . '%', 'primary');
    ?>
  </div>

  <div class="section-title">📈 Gráficos Visuales</div>
  <div class="row chart-container">
    <div class="col-md-6"><canvas id="graficoMensual"></canvas></div>
    <div class="col-md-6"><canvas id="graficoCosto"></canvas></div>
    <div class="col-md-6"><canvas id="graficoEstatus"></canvas></div>
    <div class="col-md-6"><canvas id="graficoUnidades"></canvas></div>
    <div class="col-md-6"><canvas id="graficoCompletadas"></canvas></div>
    <div class="col-md-6"><canvas id="graficoUsuarios"></canvas></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const kpis = <?= json_encode($kpis) ?>;

const crearGraficoLineal = (id, labels, data, label, formatoMoneda = false) => {
  new Chart(document.getElementById(id), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: label,
        data: data,
        borderWidth: 2,
        tension: 0.3
      }]
    },
    options: {
      scales: {
        y: {
          ticks: {
            callback: v => formatoMoneda ? '$' + v.toLocaleString() : v
          }
        }
      }
    }
  });
};

const crearGraficoPie = (id, labels, data, label) => {
  new Chart(document.getElementById(id), {
    type: 'doughnut',
    data: { labels: labels, datasets: [{ label, data }] },
    options: { responsive: true }
  });
};

// Renderizar gráficos
crearGraficoLineal('graficoMensual', kpis.mensual.labels, kpis.mensual.valores, 'Órdenes por Mes');
crearGraficoLineal('graficoCosto', kpis.costo_mensual.labels, kpis.costo_mensual.valores, 'Costo Mensual', true);
crearGraficoPie('graficoUnidades', kpis.unidades.labels, kpis.unidades.valores, 'Distribución por Unidad');
crearGraficoPie('graficoEstatus', kpis.estatus.labels, kpis.estatus.valores, '% por Estatus');
crearGraficoLineal('graficoCompletadas', kpis.completadas_dia.labels, kpis.completadas_dia.valores, 'Completadas por Día');
crearGraficoPie('graficoUsuarios', kpis.completadas_usuario.labels, kpis.completadas_usuario.valores, 'Completadas por Usuario');
</script>
</body>
</html>