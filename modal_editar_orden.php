<?php
include 'conexion.php';

$id = intval($_GET['id'] ?? 0);
$orden = $conn->query("SELECT * FROM gastos WHERE id = $id AND origen = 'Orden'")->fetch_assoc();

if (!$orden) {
    echo "<div class='p-3 text-danger'>Orden no encontrada.</div>";
    exit;
}
?>

<form id="formEditarOrden">
  <input type="hidden" name="id" value="<?= $orden['id'] ?>">
  <div class="modal-header">
    <h5 class="modal-title">Editar Orden de Compra</h5>
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
        <option value="<?= $p['id'] ?>" <?= $p['id'] == $orden['proveedor_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['nombre']) ?>
        </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Monto</label>
      <input type="number" name="monto" class="form-control" value="<?= $orden['monto'] ?>" required min="0" step="0.01">
    </div>

    <div class="mb-3">
      <label class="form-label">Fecha de Pago</label>
      <input type="date" name="fecha_pago" class="form-control" value="<?= $orden['fecha_pago'] ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Unidad de Negocio</label>
      <select name="unidad_negocio_id" class="form-select" required>
        <option value="">Seleccione unidad</option>
        <?php
        $unidades = $conn->query("SELECT id, nombre FROM unidades_negocio ORDER BY nombre");
        while ($u = $unidades->fetch_assoc()):
        ?>
        <option value="<?= $u['id'] ?>" <?= $u['id'] == $orden['unidad_negocio_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($u['nombre']) ?>
        </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Tipo de Orden</label>
      <select name="tipo_gasto" class="form-select" required>
        <option value="Unico" <?= $orden['tipo_gasto'] === 'Unico' ? 'selected' : '' ?>>Orden (Única)</option>
        <option value="Recurrente" <?= $orden['tipo_gasto'] === 'Recurrente' ? 'selected' : '' ?>>Orden (Recurrente)</option>
      </select>
    </div>

    <input type="hidden" name="origen" value="Orden">
  </div>

  <div class="modal-footer">
    <button type="submit" class="btn btn-warning">Actualizar Orden</button>
  </div>
</form>
