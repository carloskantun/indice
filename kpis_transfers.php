<?php
include 'auth.php';
include 'conexion.php';
require_once 'app/components/FiltrosBase.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>KPIs Transfers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">📊 KPIs Transfers</h2>

  <?php
    $filtros = [
      ['type' => 'date', 'name' => 'fecha_inicio', 'col' => 'col-md-3'],
      ['type' => 'date', 'name' => 'fecha_fin',    'col' => 'col-md-3']
    ];
  ?>
  <form class="row g-2 mb-4" id="formFiltros" data-endpoint="kpis_transfers_data.php">
    <?php echo FiltrosBase::render($filtros); ?>
    <div class="col-md-3 text-end">
      <button class="btn btn-primary" type="submit">Aplicar</button>
    </div>
  </form>

  <div class="row mb-4" id="contenedor"></div>
  <canvas id="grafico" height="100"></canvas>
</div>

<script src="includes/assets/js/filtros.js"></script>
<script>
const form = document.getElementById('formFiltros');
form.addEventListener('filtros:data', e => {
  const d = e.detail;
  document.getElementById('contenedor').innerHTML = `
      <div class='col'>Total: <strong>${d.totales.total}</strong></div>
      <div class='col'>Pendientes: <strong>${d.totales["Pendiente"]}</strong></div>
      <div class='col'>En proceso: <strong>${d.totales["En proceso"]}</strong></div>
      <div class='col'>Terminados: <strong>${d.totales["Terminado"]}</strong></div>
      <div class='col'>Cancelados: <strong>${d.totales["Cancelado"]}</strong></div>
  `;

  if (window.myChart) window.myChart.destroy();
  const ctx = document.getElementById('grafico').getContext('2d');
  window.myChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: Object.keys(d.tipos),
      datasets: [{
        label: 'Tipo de Transfer',
        data: Object.values(d.tipos),
        backgroundColor: ['#007bff','#28a745','#ffc107'],
        borderWidth: 1
      }]
    }
  });
});
</script>
</body>
</html>
