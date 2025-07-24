<?php
include 'auth.php';
require_once 'app/components/FiltrosBase.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>KPIs Lavandería</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="mb-4">📈 KPIs Lavandería</h3>
  <?php
    $filtros = [
      ['type' => 'date', 'name' => 'fecha_inicio', 'col' => 'col-md-3'],
      ['type' => 'date', 'name' => 'fecha_fin',    'col' => 'col-md-3']
    ];
  ?>
  <form id="filtros" class="row g-2 mb-3" data-endpoint="kpis_lavanderia_data.php">
    <?php echo FiltrosBase::render($filtros); ?>
    <div class="col-md-3"><button class="btn btn-primary" type="submit">Aplicar</button></div>
  </form>
  <div id="resumen" class="mb-3"></div>
  <canvas id="grafico" height="120"></canvas>
</div>
<script src="includes/assets/js/filtros.js"></script>
<script>
const form = document.getElementById('filtros');
form.addEventListener('filtros:data', e => {
  const d = e.detail;
  document.getElementById('resumen').innerHTML = `Total Servicios: <strong>${d.total_servicios}</strong> | Este Mes: <strong>${d.total_mes}</strong> | Ingresos: <strong>$${d.ingresos}</strong>`;
  if(window.myChart) window.myChart.destroy();
  const ctx = document.getElementById('grafico');
  window.myChart = new Chart(ctx, {type:'bar',data:{labels:Object.keys(d.prendas),datasets:[{label:'Prendas',data:Object.values(d.prendas),backgroundColor:'#0d6efd'}]}});
});
</script>
</body>
</html>
