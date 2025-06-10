<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'auth.php';
include 'conexion.php';

if (!isset($_GET['modal'])) {
    echo "Acceso directo no permitido.";
    exit;
error_log("DEBUG: cargando formulario de compra correctamente");
?>
<form id="formCompra" method="POST">
    <!-- Orden de Compra Relacionada (opcional) -->
    <div class="mb-3">
        <label for="orden_folio" class="form-label">Orden de Compra Relacionada (opcional)</label>
        <select name="orden_folio" id="orden_folio" class="form-select">
            <option value="">— Compra directa —</option>
            <?php
            $ordenes = $conn->query("SELECT folio FROM ordenes_compra ORDER BY fecha_creacion DESC");
            while ($orden = $ordenes->fetch_assoc()):
            ?>
                <option value="<?php echo htmlspecialchars($orden['folio']); ?>"><?php echo htmlspecialchars($orden['folio']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <!-- Proveedor -->
        <label for="proveedor_id" class="form-label">Proveedor</label>
        <select name="proveedor_id" id="proveedor_id" class="form-select" required>
            <option value="">Seleccione proveedor</option>
            $proveedores = $conn->query("SELECT id, nombre FROM proveedores ORDER BY nombre");
            while ($p = $proveedores->fetch_assoc()):
                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
    <!-- Fecha -->
        <label for="fecha_compra" class="form-label">Fecha de Compra</label>
        <input type="date" name="fecha_compra" id="fecha_compra" class="form-control" required>
    <!-- Monto -->
        <label for="monto" class="form-label">Monto</label>
        <input type="number" name="monto" id="monto" class="form-control" step="0.01" required>
    <!-- Nota de Crédito -->
        <label for="nota_credito_id" class="form-label">Aplicar Nota de Crédito (opcional)</label>
        <select name="nota_credito_id" id="nota_credito_id" class="form-select">
            <option value="">— Ninguna —</option>
            $notas = $conn->query("SELECT id, folio, monto FROM notas_credito WHERE monto > 0 ORDER BY id DESC");
            while ($nc = $notas->fetch_assoc()):
                <option value="<?php echo $nc['id']; ?>">
                    <?php echo htmlspecialchars($nc['folio']) . " — $" . number_format($nc['monto'], 2); ?>
                </option>
    <!-- Comprobante -->
        <label for="comprobante" class="form-label">Comprobante</label>
        <input type="text" name="comprobante" id="comprobante" class="form-control">
    <!-- Observaciones -->
        <label for="observaciones" class="form-label">Observaciones</label>
        <textarea name="observaciones" id="observaciones" rows="2" class="form-control"></textarea>
    <!-- Botón -->
    <div class="text-end">
        <button type="submit" class="btn btn-success">Guardar Compra</button>
</form>
<?php exit; ?>
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
