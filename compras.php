<?php
include 'conexion.php';

// Cargar opciones dinámicas
$proveedores = $conn->query("SELECT id, nombre FROM proveedores");
$notas = $conn->query("SELECT id FROM notas_credito");

?>

<form method="POST" action="guardar_compra.php" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="form-label">Orden de Compra Relacionada (opcional)</label>
    <select name="orden_id" class="form-select">
      <option value="">— Compra directa —</option>
      <!-- Puedes cargar dinámicamente órdenes disponibles si deseas -->
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Proveedor</label>
    <select name="proveedor_id" class="form-select" required>
      <option value="">Seleccione proveedor</option>
      <?php while ($p = $proveedores->fetch_assoc()): ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Fecha de Compra</label>
    <input type="date" name="fecha_compra" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Monto</label>
    <input type="number" name="monto_total" class="form-control" step="0.01" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Aplicar Nota de Crédito (opcional)</label>
    <select name="nota_credito_id" class="form-select">
      <option value="">— Ninguna —</option>
      <?php while ($n = $notas->fetch_assoc()): ?>
        <option value="<?= $n['id'] ?>">Nota #<?= $n['id'] ?></option>
      <?php endwhile; ?>
    </select>
  </div>

  <!-- ✅ BOTÓN DE GUARDAR -->
  <div class="text-end mt-4">
    <button type="submit" class="btn btn-success">Guardar Compra</button>
  </div>
</form>

<script>
document.getElementById("formCompra").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("guardar_compra.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        if (res.trim() === "ok") {
            alert("Compra guardada correctamente.");
            bootstrap.Modal.getInstance(document.getElementById("modalAgregarCompra")).hide();
            location.reload();
        } else {
            alert("Error: " + res);
        }
    });
});
</script>
<?php exit; ?>
