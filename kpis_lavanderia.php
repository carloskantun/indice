<?php
include 'auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>KPIs Lavandería</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="mb-4">📈 KPIs Lavandería</h3>
  <form id="filtros" class="row g-2 mb-3">
    <div class="col-md-3"><input type="date" name="fecha_inicio" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="fecha_fin" class="form-control"></div>
    <div class="col-md-3"><button class="btn btn-primary" type="submit">Aplicar</button></div>
  </form>
  <div id="resumen" class="mb-3"></div>
  <canvas id="grafico" height="120"></canvas>
</div>
<script>
function cargar(){
 $.getJSON('kpis_lavanderia_data.php',$('#filtros').serialize(),function(d){
   $('#resumen').html(`Total Servicios: <strong>${d.total_servicios}</strong> | Este Mes: <strong>${d.total_mes}</strong> | Ingresos: <strong>$${d.ingresos}</strong>`);
   if(window.myChart) window.myChart.destroy();
   const ctx=document.getElementById('grafico');
   window.myChart=new Chart(ctx,{type:'bar',data:{labels:Object.keys(d.prendas),datasets:[{label:'Prendas',data:Object.values(d.prendas),backgroundColor:'#0d6efd'}]}});
 });
}
$('#filtros').on('submit',function(e){e.preventDefault();cargar();});
$(document).ready(cargar);
</script>
</body>
</html>
