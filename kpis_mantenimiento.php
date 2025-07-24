<?php
include 'auth.php';
include 'conexion.php';
require_once 'app/components/FiltrosBase.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>KPIs de Mantenimiento</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
  <style>
    .card-kpi {
      text-align: center;
      min-height: 130px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
    }
    .card-kpi h3 { font-size: 2rem; }
    .section-title { margin-top: 40px; margin-bottom: 20px; }
  </style>
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">📊 KPIs de Mantenimiento</h2>

  <!-- 🎛️ Filtros -->
  <?php
    $optsAloj = [];
    $res = $conn->query("SELECT id, nombre FROM alojamientos");
    while ($row = $res->fetch_assoc()) { $optsAloj[$row['id']] = $row['nombre']; }

    $optsUnidades = [];
    $res = $conn->query("SELECT id, nombre FROM unidades_negocio");
    while ($row = $res->fetch_assoc()) { $optsUnidades[$row['id']] = $row['nombre']; }

    $filtros = [
      ['type' => 'date',   'name' => 'fecha_inicio',   'label' => 'Fecha Inicio', 'col' => 'col-md-3'],
      ['type' => 'date',   'name' => 'fecha_fin',      'label' => 'Fecha Fin',    'col' => 'col-md-3'],
      [
        'type'  => 'select',
        'name'  => 'alojamiento[]',
        'id'    => 'alojamiento',
        'label' => 'Alojamiento',
        'class' => 'form-select select2',
        'options' => $optsAloj,
        'col'   => 'col-md-3'
      ],
      [
        'type'  => 'select',
        'name'  => 'unidad_negocio[]',
        'id'    => 'unidad_negocio',
        'label' => 'Unidad de Negocio',
        'class' => 'form-select select2',
        'options' => $optsUnidades,
        'col'   => 'col-md-3'
      ]
    ];
  ?>
  <form id="formFiltros" class="row g-3 mb-4" data-endpoint="kpis_mantenimiento_data.php">
    <?php echo FiltrosBase::render($filtros); ?>
    <div class="col-12 text-end">
      <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
      <button type="button" id="btnImprimir" class="btn btn-dark">🖨️ Vista Imprimible</button>
    </div>
  </form>

  <!-- 🧱 Secciones -->
  <div class="section-title"><strong>🛠️ Indicadores Operativos</strong></div>
  <div id="kpi-operativos" class="row g-4"></div>

  <div class="section-title"><strong>💰 Indicadores Financieros</strong></div>
  <div id="kpi-financieros" class="row g-4"></div>

  <div class="section-title"><strong>🧠 Calidad y Documentación</strong></div>
  <div id="kpi-calidad" class="row g-4"></div>

  <div class="section-title"><strong>📍 Análisis por Alojamiento</strong></div>
  
  <div class="row g-4" id="analisis-alojamientos">
    <div class="col-md-6">
      <h6>Top 5 con más Reportes</h6>
      <ul id="top-general" class="list-group"></ul>
    </div>
    <div class="col-md-6">
      <h6>Top 5 con más Pendientes</h6>
      <ul id="top-pendientes" class="list-group"></ul>
    </div>
    <div class="col-md-6">
      <h6>Top 5 con más Terminados</h6>
      <ul id="top-terminados" class="list-group"></ul>
    </div>
    <div class="col-md-6">
      <h6>Alojamientos sin ningún reporte</h6>
      <ul id="sin-reportes" class="list-group"></ul>
    </div>
  </div>

  <div class="section-title"><strong>📈 Tendencias Visuales</strong></div>
  <div class="row g-4">
    <div class="col-md-6">
      <canvas id="graficoMensual"></canvas>
    </div>
    <div class="col-md-6">
      <canvas id="graficoCosto"></canvas>
    </div>
    <div class="col-md-6">
      <canvas id="graficoEstatus"></canvas>
    </div>
    <div class="col-md-6">
      <canvas id="graficoUnidades"></canvas>
    </div>
    <div class="col-md-6">
      <canvas id="graficoCompletadasPorDia"></canvas>
    </div>
    <div class="col-md-6">
  <canvas id="graficoCompletadasUsuario"></canvas>
</div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="includes/assets/js/filtros.js"></script>

<script>
$(function () {
  $(".select2").select2({ width: '100%' });

  function renderKpis(res) {
      // KPIs cards
      $("#kpi-operativos").html(`
        ${crearCard('Total Reportes', res.total, 'primary')}
        ${crearCard('Pendientes', res.pendientes, 'secondary')}
        ${crearCard('En Proceso', res.en_proceso, 'warning')}
        ${crearCard('Terminados', res.terminados, 'success')}
        ${crearCard('Cancelados', res.cancelados, 'danger')}
        ${crearCard('Vencidos', res.vencidos, 'dark')}
      `);
      $("#kpi-financieros").html(`
        ${crearCard('Costo Total', '$' + res.costo_total.toLocaleString(), 'info')}
        ${crearCard('Costo Promedio', '$' + res.costo_promedio.toLocaleString(), 'secondary')}
      `);
      $("#kpi-calidad").html(`
  ${crearCard('Promedio de Días', res.promedio_dias + ' días', 'info')}
  ${crearCard('% Cumplimiento Mes', res.cumplimiento_mes + '%', 'success')}
  ${crearCard('Coef. Productividad', res.productividad + '%', 'primary')}
  ${crearCard('Coef. Productividad Ponderado', res.ponderado + '%', 'warning')}
  ${crearCard('Ponderado (En Proceso)', res.total_ponderado_proceso ?? 0, 'info')}
  ${crearCard('Ponderado (Pendiente)', res.total_ponderado_pendiente ?? 0, 'secondary')}
  ${crearCard('Ponderado (Terminado)', res.total_ponderado_terminado ?? 0, 'success')}
  ${crearCard('Ponderado (Total)', res.total_ponderado_general ?? 0, 'dark')}
`);


      // Alojamientos
      $("#top-general").html(res.top_general.map(r => `<li class="list-group-item">${r.nombre} (${r.total})</li>`).join(""));
      $("#top-pendientes").html(res.top_pendientes.map(r => `<li class="list-group-item">${r.nombre} (${r.total})</li>`).join(""));
      $("#top-terminados").html(res.top_terminados.map(r => `<li class="list-group-item">${r.nombre} (${r.total})</li>`).join(""));
      $("#sin-reportes").html(res.sin_reportes.map(r => `<li class="list-group-item">${r}</li>`).join(""));

      // Gráficos
      actualizarGraficos(res);
  }

  function crearCard(titulo, valor, color) {
    return `
      <div class="col-md-4">
        <div class="card border-${color} card-kpi">
          <div class="card-body">
            <h6>${titulo}</h6>
            <h3>${valor}</h3>
          </div>
        </div>
      </div>
    `;
  }

  function actualizarGraficos(res) {
    crearGraficoLineal('graficoMensual', res.mensual.labels, res.mensual.valores, 'Órdenes por Mes');
    crearGraficoLineal('graficoCosto', res.costo_mensual.labels, res.costo_mensual.valores, 'Costo Mensual', true);
    crearGraficoLineal('graficoCompletadasPorDia', res.completadas_dia.labels, res.completadas_dia.valores, 'Completadas por Día');
    crearGraficoPie('graficoUnidades', res.unidades.labels, res.unidades.valores, 'Distribución por Unidad de Negocio');
    crearGraficoPie('graficoCompletadasUsuario', res.completadas_usuario.labels, res.completadas_usuario.valores, 'Órdenes por Usuario');
    crearGraficoPie('graficoEstatus', res.estatus.labels, res.estatus.valores, '% por Estatus');
  }

  function crearGraficoLineal(id, labels, data, label, formatoMoneda = false) {
    new Chart(document.getElementById(id), {
      type: 'line',
      data: {
        labels,
        datasets: [{ label, data, borderWidth: 2, tension: 0.4 }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            ticks: {
              callback: v => formatoMoneda ? '$' + v.toLocaleString() : v
            }
          }
        }
      }
    });
  }

  function crearGraficoPie(id, labels, data, label) {
    new Chart(document.getElementById(id), {
      type: 'doughnut',
      data: { labels, datasets: [{ label, data }] },
      options: { responsive: true }
    });
  }

  const form = document.getElementById('formFiltros');
  form.addEventListener('filtros:data', e => renderKpis(e.detail));
});

$("#btnImprimir").on("click", function () {
  const nombres = [
    'graficoMensual',
    'graficoCosto',
    'graficoUnidades',
    'graficoEstatus',
    'graficoCompletadasPorDia',
    'graficoCompletadasUsuario'
  ];

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'kpis_mantenimiento_printable.php<?php echo strpos($_SERVER['REQUEST_URI'], '?') ? '&' : '?'; ?>' + $("#formFiltros").serialize();
  form.target = '_blank';

  nombres.forEach(id => {
    const canvas = document.getElementById(id);
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = id;
    input.value = canvas ? canvas.toDataURL("image/png") : '';
    form.appendChild(input);
  });

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
});
</script>
</body>
</html>