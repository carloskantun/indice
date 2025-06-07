<?php
include 'conexion.php';
$is_modal = isset($_GET['modal']);

if (!$is_modal) {
    echo "Acceso directo no permitido.";
    exit;
}
?>

<form id="formNota" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="compra_id" class="form-label">Compra Relacionada</label>
        <select name="compra_id" id="compra_id" class="form-select" required>
            <option value="">Seleccione compra</option>
            <?php
            $compras = $conn->query("SELECT id, folio FROM compras ORDER BY fecha_compra DESC");
            while ($c = $compras->fetch_assoc()):
            ?>
                <option value="<?php echo $c['id']; ?>"><?php echo $c['folio']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="fecha_nota" class="form-label">Fecha de Nota</label>
        <input type="date" name="fecha_nota" id="fecha_nota" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="monto_nota" class="form-label">Monto</label>
        <input type="number" name="monto_nota" id="monto_nota" class="form-control" step="0.01" required>
    </div>

    <div class="mb-3">
        <label for="motivo" class="form-label">Motivo</label>
        <textarea name="motivo" id="motivo" class="form-control" rows="3" required></textarea>
    </div>

    <div class="mb-3">
        <label for="archivo" class="form-label">Archivo Adjunto (opcional)</label>
        <input type="file" name="archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-warning">Guardar Nota</button>
    </div>
</form>

<script>
document.getElementById("formNota").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("guardar_nota.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        if (res.trim() === "ok") {
            alert("Nota de crédito registrada.");
            bootstrap.Modal.getInstance(document.getElementById("modalAgregarNota")).hide();
            location.reload();
        } else {
            alert("Error: " + res);
        }
    });
});
</script>