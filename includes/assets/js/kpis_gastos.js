document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('formFiltrosKpi');
  const contenedor = document.getElementById('kpiCardsContainer');
  const modal = document.getElementById('modalKpisGastos');

  // Función para obtener el mes actual por defecto
  function setFechasDefault() {
    const hoy = new Date();
    const inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    const fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
    form.fecha_inicio.value = inicio.toISOString().slice(0, 10);
    form.fecha_fin.value = fin.toISOString().slice(0, 10);
  }

  // Función principal para cargar KPIs
  async function cargarKPIs() {
    const formData = new FormData(form);
    const res = await fetch('includes/controllers/analisis_kpis_gastos.php', {
      method: 'POST',
      body: formData
    });

    const data = await res.json();
    console.log('Datos de KPIs recibidos:', data); // 👈 Para depuración

    if (data.error) {
      contenedor.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
      return;
    }

    // Limpiar contenedor
    contenedor.innerHTML = '';

    // Gasto total
    contenedor.innerHTML += `
      <div class="col-md-4">
        <div class="card shadow-sm">
          <div class="card-body text-center">
            <h6>Total del periodo</h6>
            <h4 class="text-primary">$${data.gasto_total.toLocaleString('es-MX', {minimumFractionDigits: 2})}</h4>
          </div>
        </div>
      </div>
    `;

    // Gráficos dinámicos
    function crearGrafico(id, titulo, labels, valores, tipo = 'pie') {
      const canvasId = `chart_${id}`;
      contenedor.innerHTML += `
        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6>${titulo}</h6>
              <canvas id="${canvasId}" height="200"></canvas>
            </div>
          </div>
        </div>
      `;
      setTimeout(() => {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        new Chart(ctx, {
          type: tipo,
          data: {
            labels,
            datasets: [{
              data: valores,
              backgroundColor: [
                '#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b',
                '#858796','#5a5c69','#ff6384','#17a2b8','#fd7e14'
              ]
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { position: 'bottom' }
            }
          }
        });
      }, 50);
    }

    // Tipo de gasto
    if (data.por_tipo?.length) {
      crearGrafico(
        'tipo',
        'Distribución por tipo de gasto',
        data.por_tipo.map(t => t.tipo || 'Sin tipo'),
        data.por_tipo.map(t => t.total)
      );
    }

    // Unidad de negocio
    if (data.por_unidad?.length) {
      crearGrafico(
        'unidad',
        'Gasto por unidad de negocio',
        data.por_unidad.map(t => t.unidad || 'Sin unidad'),
        data.por_unidad.map(t => t.total),
        'bar'
      );
    }

    // Estatus
    if (data.por_estatus?.length) {
      crearGrafico(
        'estatus',
        'Estatus de gastos',
        data.por_estatus.map(t => t.estatus || 'Sin estatus'),
        data.por_estatus.map(t => t.total)
      );
    }

    // Proveedor
    if (data.por_proveedor?.length) {
      crearGrafico(
        'proveedor',
        'Gasto por proveedor',
        data.por_proveedor.map(t => t.proveedor || 'Sin proveedor'),
        data.por_proveedor.map(t => t.total)
      );
    }

    // Abonos vs Saldo
    crearGrafico(
      'abonos',
      'Abonado vs Saldo',
      ['Abonado', 'Saldo'],
      [data.abonos.abonado, data.abonos.saldo]
    );
  }

  // Precarga al mostrar modal
  document.addEventListener('shown.bs.modal', function (event) {
    if (event.target.id === 'modalKpisGastos') {
      setFechasDefault();
      cargarKPIs();
    }
  });

  // Filtrar manual
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    cargarKPIs();
  });
  
    // Filtrar manual
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    cargarKPIs();
  });
  // 👇 Agregamos la funcionalidad de los botones de exportación
  const exportCsvBtn = document.getElementById('exportKpisCsv');
  const exportPdfBtn = document.getElementById('exportKpisPdf');

  function exportarKpis(url) {
    const formData = new FormData(form);
    const params = new URLSearchParams();
    for (let pair of formData.entries()) {
      params.append(pair[0], pair[1]);
    }
    window.open(`${url}?${params.toString()}`, '_blank');
  }

  exportCsvBtn?.addEventListener('click', () => exportarKpis('includes/controllers/exportar_kpis_csv.php'));
  exportPdfBtn?.addEventListener('click', () => exportarKpis('includes/controllers/exportar_kpis_pdf.php'));

});

document.addEventListener('DOMContentLoaded', () => {
  const btnCsv = document.getElementById('exportKpisCsv');
  const btnPdf = document.getElementById('exportKpisPdf');
  const form = document.getElementById('formFiltrosKpi');

  function actualizarExportLinks() {
    const datos = new URLSearchParams(new FormData(form)).toString();
    btnCsv.href = `includes/controllers/exportar_kpis_csv.php?${datos}`;
    btnPdf.href = `includes/controllers/exportar_kpis_pdf.php?${datos}`;
  }

  form.addEventListener('submit', actualizarExportLinks);

  document.addEventListener('shown.bs.modal', function (event) {
    if (event.target.id === 'modalKpisGastos') {
      setTimeout(actualizarExportLinks, 100); // Asegura fechas cargadas
    }
  });
});

