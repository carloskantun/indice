<?php
include '../../conexion.php';

$nota_id = $_GET['id'] ?? null;
if (!$nota_id) {
    echo '<div class="p-3 text-danger">Nota no encontrada</div>';
    exit;
}
?>

<div class="modal-header">
    <h5 class="modal-title">Agregar abono a la Nota</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="formAbonoNota" enctype="multipart/form-data">
    <div class="modal-body">
        <input type="hidden" name="nota_id" value="<?= intval($nota_id) ?>">

        <div class="mb-2">
            <label>Monto del abono</label>
            <input type="number" name="monto" class="form-control" step="0.01" required>
        </div>

        <div class="mb-2">
            <label>Comentario</label>
            <textarea name="comentario" class="form-control" rows="2"></textarea>
        </div>

        <div class="mb-2">
            <label>Comprobante (imagen o PDF)</label>
            <input type="file" name="archivo_comprobante" class="form-control" accept="image/*,application/pdf" required>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    </div>
</form>

<script>
document.getElementById('formAbonoNota').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = this;
    const data = new FormData(form);

    fetch('includes/controllers/guardar_abono_nota.php', {
        method: 'POST',
        body: data
    })
    .then(res => res.text())
    .then(resp => {
        if (resp.trim() === 'ok') {
            alert('✅ Abono registrado');
            bootstrap.Modal.getInstance(form.closest('.modal')).hide();
            location.reload();
        } else {
            alert('❌ Error: ' + resp);
        }
    })
    .catch(() => alert('❌ Error de conexión'));
});
</script>
