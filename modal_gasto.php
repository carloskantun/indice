<?php
include 'conexion.php';
if (!isset($_GET['modal'])) {
    echo "Acceso directo no permitido.";
    exit;
}
?>
<form id="formGasto" method="POST" action="guardar_gasto.php">
    <div class="mb-3">
        <label class="form-label">Tipo de registro</label>
        <select id="tipoRegistro" class="form-select">
            <option value="Directo">Gasto Directo</option>
            <option value="Orden">Orden de Compra</option>
        </select>
    </div>
    <div class="mb-3 d-none" id="campoOrden">
        <label class="form-label">Orden relacionada</label>
        <select name="orden_folio" class="form-select">
            <option value="">Seleccione orden</option>
            <?php
            $ord=$conn->query("SELECT oc.folio, p.nombre AS prov FROM ordenes_compra oc JOIN proveedores p ON oc.proveedor_id=p.id WHERE oc.estatus_pago IN ('Por pagar','Vencido','Pago parcial') ORDER BY oc.folio DESC");
            while($row=$ord->fetch_assoc()): ?>
            <option value="<?php echo $row['folio']; ?>"><?php echo htmlspecialchars($row['folio'].' - '.$row['prov']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Proveedor</label>
        <select name="proveedor_id" class="form-select" required>
            <option value="">Seleccione proveedor</option>
            <?php $p=$conn->query("SELECT id,nombre FROM proveedores ORDER BY nombre");
            while($row=$p->fetch_assoc()): ?>
            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nombre']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Monto</label>
        <input type="number" step="0.01" name="monto" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Fecha de Pago</label>
        <input type="date" name="fecha_pago" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Unidad de Negocio</label>
        <select name="unidad_negocio_id" class="form-select" required>
            <option value="">Seleccione unidad</option>
            <?php $u=$conn->query("SELECT id,nombre FROM unidades_negocio ORDER BY nombre");
            while($row=$u->fetch_assoc()): ?>
            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nombre']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Tipo de Gasto</label>
        <select name="tipo_gasto" class="form-select">
            <option value="Recurrente">Recurrente</option>
            <option value="Unico">Único</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Medio de Pago</label>
        <select name="medio_pago" class="form-select">
            <option value="Tarjeta">Tarjeta</option>
            <option value="Transferencia">Transferencia</option>
            <option value="Efectivo">Efectivo</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Cuenta Bancaria</label>
        <input type="text" name="cuenta_bancaria" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Concepto</label>
        <textarea name="concepto" class="form-control"></textarea>
    </div>
    <input type="hidden" name="origen_id" value="">
    <div class="text-end">
        <button type="submit" class="btn btn-success">Guardar</button>
    </div>
</form>
<script>
document.getElementById('formGasto').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('guardar_gasto.php', {method: 'POST', body: formData})
        .then(r => r.text())
        .then(r => {
            if (r.trim() === 'ok') {
                alert('Gasto guardado');
                bootstrap.Modal.getInstance(document.getElementById('modalGasto')).hide();
                location.reload();
            } else {
                alert(r);
            }
        });
});

// Cambiar tipo de registro
const tipoReg=document.getElementById('tipoRegistro');
const campoOrden=document.getElementById('campoOrden');
const inputOrigen=document.createElement('input');
inputOrigen.type='hidden';
inputOrigen.name='origen';
document.getElementById('formGasto').appendChild(inputOrigen);
tipoReg.addEventListener('change',actualizar);
function actualizar(){
    if(tipoReg.value==='Orden'){
        campoOrden.classList.remove('d-none');
        inputOrigen.value='Orden';
    }else{
        campoOrden.classList.add('d-none');
        inputOrigen.value='Directo';
    }
}
actualizar();
</script>
<?php exit; ?>
