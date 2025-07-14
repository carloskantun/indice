<?php include 'conexion.php'; ?>

<form id="formGasto" action="guardar_gasto.php" method="POST" enctype="multipart/form-data">
  <div class="modal-header">
    <h5 class="modal-title">Registrar Gasto</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>

  <div class="modal-body">
    <div class="mb-3">
      <label class="form-label">Proveedor</label>
      <select name="proveedor_id" class="form-select" required>
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
      <label class="form-label">Comprobante (PDF o imagen)</label>
      <input type="file" name="comprobante_gasto" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
    </div>

    <input type="hidden" name="origen" value="Directo">
    <input type="hidden" name="tipo_gasto" value="Unico">
  </div>

  <div class="modal-footer">
    <button type="submit" class="btn btn-success">Guardar Gasto</button>
  </div>
</form>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formGasto");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault(); // Evita redirección

    const datos = new FormData(form);

    fetch(form.action, {
      method: form.method,
      body: datos
    })
    .then(res => res.text())
    .then(respuesta => {
      if (respuesta.trim() === "ok") {
        alert("✅ Gasto guardado correctamente");
        const modal = bootstrap.Modal.getInstance(form.closest(".modal"));
        if (modal) modal.hide();

        // Mantener filtros activos
        const queryString = window.location.search;
        window.location.href = "gastos.php" + queryString;

      } else {
        alert("❌ Error: " + respuesta);
        console.error("Respuesta del servidor:", respuesta);
      }
    })
    .catch(err => {
      alert("❌ Error de conexión con el servidor.");
      console.error(err);
    });
  });
});
</script>
