<?php
include 'conexion.php';
require_once __DIR__.'/../../components/FormularioBase.php';

$id = intval($_GET['id'] ?? 0);
$gasto = $conn->query("SELECT * FROM gastos WHERE id = $id")->fetch_assoc();
if(!$gasto){
    echo "<div class='p-3 text-danger'>Gasto no encontrado.</div>";
    exit;
}

$optsProv = [];
$res = $conn->query("SELECT id,nombre FROM proveedores ORDER BY nombre");
while($row = $res->fetch_assoc()) $optsProv[$row['id']] = $row['nombre'];

$optsUnidades = [];
$res = $conn->query("SELECT id,nombre FROM unidades_negocio ORDER BY nombre");
while($row = $res->fetch_assoc()) $optsUnidades[$row['id']] = $row['nombre'];

$campos = [
    ['type'=>'hidden','name'=>'id'],
    ['type'=>'select','name'=>'proveedor_id','label'=>'Proveedor','options'=>$optsProv,'required'=>true,'class'=>'form-select select2'],
    ['type'=>'number','name'=>'monto','label'=>'Monto','required'=>true,'attrs'=>'min="0" step="0.01"'],
    ['type'=>'date','name'=>'fecha_pago','label'=>'Fecha de Pago','required'=>true],
    ['type'=>'select','name'=>'unidad_negocio_id','label'=>'Unidad de Negocio','options'=>$optsUnidades,'required'=>true,'class'=>'form-select select2']
];
?>
<form id="formEditarGasto" action="app/modules/gastos/actualizar_gasto.php" method="POST">
  <div class="modal-header">
    <h5 class="modal-title">Editar Gasto</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body">
    <?= FormularioBase::render($campos, $gasto) ?>
    <input type="hidden" name="origen" value="Directo">
    <input type="hidden" name="tipo_gasto" value="Unico">
  </div>
  <div class="modal-footer">
    <button type="submit" class="btn btn-warning w-100">Actualizar Gasto</button>
  </div>
</form>
<script>
$(function(){
  const modal = $('#modalEditarGasto');
  $('.select2').select2({width:'100%', dropdownParent: modal});

  $('#formEditarGasto').on('submit', function(e){
    e.preventDefault();
    const datos = new FormData(this);
    fetch(this.action + '?ajax=1', { method: this.method, body: datos })
      .then(r=>r.text())
      .then(t=>{
        if(t.trim()==='ok'){
          alert('✅ Gasto actualizado correctamente');
          bootstrap.Modal.getInstance(modal[0]).hide();
          if(window.refreshModule) window.refreshModule();
        }else{
          alert('❌ Error: '+t);
        }
      })
      .catch(()=> alert('❌ Error de conexión'));
  });
});
</script>
