<?php include 'conexion.php'; ?>

<form id="formOrden" enctype="multipart/form-data">
  <div class="modal-header">
    <h5 class="modal-title">Registrar Orden de Compra</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>

  <div class="modal-body">
    <div class="mb-3">
      <label class="form-label">Proveedor</label>
      <select name="proveedor_id" class="form-select select2" required>
        <option value="">Seleccione proveedor</option>
        <?php
        $prov = $conn->query("SELECT id, nombre FROM proveedores ORDER BY nombre");
        while ($p = $prov->fetch_assoc()):
        ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Monto</label>
      <input type="number" name="monto" class="form-control" required min="0" step="0.01">
    </div>

    <div class="mb-3">
      <label class="form-label">Fecha de Pago</label>
      <input type="date" name="fecha_pago" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Unidad de Negocio</label>
      <select name="unidad_negocio_id" class="form-select" required>
        <option value="">Seleccione unidad</option>
        <?php
        $unidades = $conn->query("SELECT id, nombre FROM unidades_negocio ORDER BY nombre");
        while ($u = $unidades->fetch_assoc()):
        ?>
        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Tipo de Orden</label>
      <select name="tipo_gasto" class="form-select" required id="tipoOrden">
        <option value="Unico">Orden (Única)</option>
        <option value="Recurrente">Orden (Recurrente)</option>
      </select>
    </div>

    <div id="camposRecurrente" style="display:none;">
      <div class="mb-3">
        <label class="form-label">Periodicidad</label>
        <select name="periodicidad" class="form-select">
          <option value="">Seleccione</option>
          <option value="Mensual">Mensual</option>
          <option value="Quincenal">Quincenal</option>
          <option value="Semanal">Semanal</option>
          <option value="Diario">Diario</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Plazo</label>
        <select name="plazo" class="form-select">
          <option value="">Seleccione</option>
          <option value="Trimestral">3 meses</option>
          <option value="Semestral">6 meses</option>
          <option value="Anual">12 meses</option>
        </select>
      </div>
    </div>

    <input type="hidden" name="origen" value="Orden">
  </div>

  <div class="modal-footer">
    <button type="submit" class="btn btn-success">Guardar Orden</button>
  </div>
</form>

<script>
(function () {
  const tipoSelect = document.getElementById("tipoOrden");
  const campos = document.getElementById("camposRecurrente");
  const form = document.getElementById("formOrden");

  if (!tipoSelect || !campos || !form) return;

  function toggleCampos() {
    campos.style.display = tipoSelect.value === "Recurrente" ? "block" : "none";
  }

  tipoSelect.addEventListener("change", toggleCampos);
  toggleCampos(); // activar al cargar

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const tipo = form.querySelector('[name="tipo_gasto"]').value;
    const periodicidad = form.querySelector('[name="periodicidad"]').value;
    const plazo = form.querySelector('[name="plazo"]').value;

    if (tipo === "Recurrente" && (!periodicidad || !plazo)) {
      alert("⚠️ Debes seleccionar periodicidad y plazo para una orden recurrente.");
      return;
    }

    const datos = new FormData(form);
    datos.append('ajax', '1');

    fetch("guardar_gasto.php", {
      method: "POST",
      body: datos
    })
    .then(res => res.json())
    .then(respuesta => {
      if (respuesta.status === "ok") {
        bootstrap.Modal.getInstance(document.getElementById("modalOrden")).hide();
        location.reload();
      } else {
        alert("❌ Error al guardar: " + JSON.stringify(respuesta));
      }
    })
    .catch(() => {
      alert("❌ Error de conexión");
    });
  });
})();
</script>
