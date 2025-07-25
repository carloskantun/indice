<?php
include 'conexion.php';
require_once __DIR__.'/../../components/FormularioBase.php';

$optsProv = [];
$res = $conn->query("SELECT id,nombre FROM proveedores ORDER BY nombre");
while($row = $res->fetch_assoc()) $optsProv[$row['id']] = $row['nombre'];

$optsUnidades = [];
$res = $conn->query("SELECT id,nombre FROM unidades_negocio ORDER BY nombre");
while($row = $res->fetch_assoc()) $optsUnidades[$row['id']] = $row['nombre'];

$campos = [
    ['type'=>'select','name'=>'proveedor_id','label'=>'Proveedor','options'=>$optsProv,'required'=>true,'class'=>'form-select select2'],
    ['type'=>'number','name'=>'monto','label'=>'Monto','required'=>true,'attrs'=>'min="0" step="0.01"'],
    ['type'=>'date','name'=>'fecha_pago','label'=>'Fecha de Pago','required'=>true],
    ['type'=>'select','name'=>'unidad_negocio_id','label'=>'Unidad de Negocio','options'=>$optsUnidades,'required'=>true,'class'=>'form-select select2'],
    ['type'=>'file','name'=>'comprobante[]','label'=>'Comprobante (PDF o imagen)','attrs'=>'accept=".pdf,.jpg,.jpeg,.png" multiple']
];
?>
<form id="formGasto" action="app/modules/gastos/guardar_gasto.php" method="POST" enctype="multipart/form-data">
  <div class="modal-header">
    <h5 class="modal-title">Registrar Gasto</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body">
    <?= FormularioBase::render($campos) ?>
    <input type="hidden" name="origen" value="Directo">
    <input type="hidden" name="tipo_gasto" value="Unico">
  </div>
  <div class="modal-footer">
    <button type="submit" class="btn btn-success w-100">Guardar Gasto</button>
  </div>
</form>
<script>
$(function(){
  const modal = $('#modalGasto');
  $('.select2').select2({width:'100%', dropdownParent: modal});

  $('#formGasto').on('submit', function(e){
    e.preventDefault();
    const datos = new FormData(this);
    fetch(this.action + '?ajax=1', { method: this.method, body: datos })
      .then(r=>r.text())
      .then(t=>{
        if(t.trim()==='ok'){
          alert('✅ Gasto guardado correctamente');
          bootstrap.Modal.getInstance(modal[0]).hide();
          if(window.refreshModule) window.refreshModule();
        }else{
          alert('❌ Error: '+t);
          console.error(t);
        }
      })
      .catch(err=>{ alert('❌ Error de conexión'); console.error(err); });
  });

  const input = modal.find('input[name="comprobante[]"]')[0];
  if(input){
    modal.on('dragover', e=>{ e.preventDefault(); modal.addClass('dragging'); });
    modal.on('dragleave', e=>{ e.preventDefault(); modal.removeClass('dragging'); });
    modal.on('drop', e=>{
      e.preventDefault();
      modal.removeClass('dragging');
      const files = e.originalEvent.dataTransfer.files;
      if(!files.length) return;
      const dt = new DataTransfer();
      for(let i=0;i<input.files.length;i++) dt.items.add(input.files[i]);
      for(let i=0;i<files.length;i++) dt.items.add(files[i]);
      input.files = dt.files;
    });
  }
});
</script>
