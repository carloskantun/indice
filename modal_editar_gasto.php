<?php
include 'conexion.php';

$id = intval($_GET['id'] ?? 0);
$gasto = $conn->query("SELECT * FROM gastos WHERE id = $id")->fetch_assoc();

if (!$gasto) {
    echo "<div class='p-3 text-danger'>Gasto no encontrado.</div>";
    exit;
}
?>

<form id="formEditarGasto" action="actualizar_gasto.php" method="POST">
  <input type="hidden" name="id" value="<?= $gasto['id'] ?>">
  <div class="modal-header">
    <h5 class="modal-title">Editar Gasto</h5>
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
        <option value="<?= $p['id'] ?>" <?= $p['id'] == $gasto['proveedor_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['nombre']) ?>
        </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Monto</label>
      <input type="number" name="monto" class="form-control" value="<?= $gasto['monto'] ?>" required min="0" step="0.01">
    </div>

    <div class="mb-3">
      <label class="form-label">Fecha de Pago</label>
      <input type="date" name="fecha_pago" class="form-control" value="<?= $gasto['fecha_pago'] ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Unidad de Negocio</label>
      <select name="unidad_negocio_id" class="form-select" required>
        <option value="">Seleccione unidad</option>
        <?php
        $unidades = $conn->query("SELECT id, nombre FROM unidades_negocio ORDER BY nombre");
        while ($u = $unidades->fetch_assoc()):
        ?>
        <option value="<?= $u['id'] ?>" <?= $u['id'] == $gasto['unidad_negocio_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($u['nombre']) ?>
        </option>
        <?php endwhile; ?>
      </select>
    </div>

    <input type="hidden" name="origen" value="Directo">
    <input type="hidden" name="tipo_gasto" value="Unico">

  </div>
  <div class="modal-footer">
    <button type="submit" class="btn btn-warning">Actualizar Gasto</button>
  </div>
</form>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formEditarGasto");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const datos = new FormData(form);

    fetch(form.action, {
      method: form.method,
      body: datos
    })
    .then(res => res.text())
    .then(respuesta => {
      if (respuesta.trim() === "ok") {
        alert("✅ Gasto actualizado correctamente");
        const modal = bootstrap.Modal.getInstance(form.closest(".modal"));
        if (modal) modal.hide();

        const queryString = window.location.search;
        window.location.href = "gastos.php" + queryString;
      } else {
        alert("❌ Error: " + respuesta);
      }
    })
    .catch(() => alert("❌ Error de conexión"));
  });
});
</script>
