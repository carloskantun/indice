<?php
include 'auth.php';
include 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>KPIs Transfers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">📊 KPIs Transfers</h2>

  <form class="row g-2 mb-4" id="formFiltros">
    <div class="col-md-3">
      <input type="date" name="fecha_inicio" class="form-control">
    </div>
    <div class="col-md-3">
      <input type="date" name="fecha_fin" class="form-control">
    </div>
    <div class="col-md-3 text-end">
      <button class="btn btn-primary" type="submit">Aplicar</button>
    </div>
  </form>

  <div class="row mb-4" id="contenedor"></div>
  <canvas id="grafico" height="100"></canvas>
</div>

<script>
function cargarKPIs() {
  $.getJSON('kpis_transfers_data.php', $('#formFiltros').serialize(), function(d) {
    $('#contenedor').html(`
      <div class='col'>Total: <strong>${d.totales.total}</strong></div>
      <div class='col'>Pendientes: <strong>${d.totales["Pendiente"]}</strong></div>
      <div class='col'>En proceso: <strong>${d.totales["En proceso"]}</strong></div>
      <div class='col'>Terminados: <strong>${d.totales["Terminado"]}</strong></div>
      <div class='col'>Cancelados: <strong>${d.totales["Cancelado"]}</strong></div>
    `);

    if (window.myChart) window.myChart.destroy(); // Si existe, destruir para redibujar
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
}

$('#formFiltros').on('submit', function(e) {
  e.preventDefault();
  cargarKPIs();
});

$(document).ready(cargarKPIs);
</script>
</body>
</html>
