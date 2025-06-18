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
    <div class="col-md-3"><input type="date" name="fecha_inicio" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="fecha_fin" class="form-control"></div>
    <div class="col-md-3 text-end"><button class="btn btn-primary" type="submit">Aplicar</button></div>
  </form>
  <div class="row" id="contenedor"></div>
  <canvas id="grafico"></canvas>
</div>
<script>
function cargar(){
  $.getJSON('kpis_transfers_data.php',$('#formFiltros').serialize(),function(d){
    $('#contenedor').html(`
      <div class='col'>Total: ${d.totales.total}</div>
      <div class='col'>Pendientes: ${d.totales.pendientes}</div>
      <div class='col'>En proceso: ${d.totales.proceso}</div>
      <div class='col'>Terminados: ${d.totales.terminados}</div>
    `);
    new Chart(document.getElementById('grafico'),{type:'pie',data:{labels:Object.keys(d.tipos),datasets:[{data:Object.values(d.tipos)}]}});
  });
}
$('#formFiltros').on('submit',function(e){e.preventDefault();cargar();});
$(cargar);
</script>
</body>
</html>
