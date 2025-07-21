<!-- Modal KPIs del módulo de gastos -->
<div class="modal fade" id="modalKpisGastos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">📊 Análisis de KPIs - Módulo de Gastos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <!-- Filtros -->
        <form id="formFiltrosKpi" class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Fecha inicio</label>
            <input type="date" name="fecha_inicio" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Fecha fin</label>
            <input type="date" name="fecha_fin" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Unidad</label>
            <select name="unidad_negocio_id" class="form-select">
              <option value="">Todas</option>
              <?php
              include_once __DIR__ . '/../../conexion.php';
              $un = $conn->query("SELECT id, nombre FROM unidades_negocio ORDER BY nombre");
              while ($u = $un->fetch_assoc()):
              ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-3 align-self-end d-grid">
            <button type="submit" class="btn btn-primary">Actualizar KPIs</button>
          </div>
        </form>

        <!-- Botones de exportación -->
        <div class="mb-3 text-end">
          <!-- Botones de exportación -->
<div class="mb-3 text-end">
  <a class="btn btn-outline-success btn-sm me-2" 
     id="exportKpisCsv" 
     target="_blank"
     href="#">
    📄 Exportar CSV
  </a>
  <a class="btn btn-outline-danger btn-sm"
     id="exportKpisPdf"
     target="_blank"
     href="#">
    🧾 Exportar PDF
  </a>
</div>

        </div>

        <!-- KPIs visuales -->
        <div id="contenedorKpis">
          <div class="row g-4" id="kpiCardsContainer">
            <!-- Aquí se cargarán los indicadores -->
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
