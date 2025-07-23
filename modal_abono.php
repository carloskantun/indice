<?php
include 'conexion.php';
$id = intval($_GET['id'] ?? 0);
$gasto = $conn->query("SELECT folio,monto FROM gastos WHERE id=$id")->fetch_assoc();
if(!$gasto){ echo '<div class="p-3">Registro no encontrado</div>'; exit; }
?>
<form id="formAbono" enctype="multipart/form-data">
    <input type="hidden" name="gasto_id" value="<?php echo $id; ?>">
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Abonar a <?php echo htmlspecialchars($gasto['folio']); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" step="0.01" name="monto" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Comentario</label>
            <textarea name="comentario" class="form-control" rows="3"></textarea>
        </div>
<!-- INPUT DE ARCHIVOS -->
<div class="mb-3">
  <label class="form-label">Comprobante</label>
  <input type="file" name="comprobante[]" class="form-control" accept="image/jpeg,image/png,application/pdf" multiple>
</div>

    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-success">Guardar</button>
    </div>
</form>
<script>
document.getElementById('formAbono').addEventListener('submit',function(e){
    e.preventDefault();
    var fd=new FormData(this);
    fetch('guardar_abono_gasto.php',{method:'POST',body:fd})
        .then(r=>r.text())
        .then(r=>{ if(r.trim()==='ok'){ alert('Abono registrado'); bootstrap.Modal.getInstance(document.getElementById('modalAbono')).hide(); location.reload(); } else { alert(r); } });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.querySelector('#formAbono')?.closest('.modal');
  const input = document.querySelector('input[name="comprobante[]"]');

  if (!modal || !input) return;

  modal.addEventListener('dragover', function (e) {
    e.preventDefault();
    modal.classList.add('dragging');
  });

  modal.addEventListener('dragleave', function (e) {
    e.preventDefault();
    modal.classList.remove('dragging');
  });

  modal.addEventListener('drop', function (e) {
    e.preventDefault();
    modal.classList.remove('dragging');

    const nuevosArchivos = e.dataTransfer.files;
    if (!nuevosArchivos.length) return;

    const dt = new DataTransfer();
    for (let i = 0; i < input.files.length; i++) dt.items.add(input.files[i]);
    for (let i = 0; i < nuevosArchivos.length; i++) dt.items.add(nuevosArchivos[i]);
    input.files = dt.files;
  });
});
</script>

