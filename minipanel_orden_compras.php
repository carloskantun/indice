<?php
session_start();
include 'auth.php';
include 'conexion.php';

header('Content-Type: text/html; charset=utf-8');

// Utilidades de filtrado
$proveedor_filtro = $_GET['proveedor'] ?? '';
$estatus_filtro   = $_GET['estatus'] ?? '';
$usuario_filtro   = $_GET['usuario'] ?? '';
$unidad_filtro    = $_GET['unidad_negocio'] ?? '';
$fecha_inicio     = $_GET['fecha_inicio'] ?? '';
$fecha_fin        = $_GET['fecha_fin'] ?? '';
$order_by         = $_GET['orden'] ?? 'folio';
$dir              = strtoupper($_GET['dir'] ?? 'ASC');
$dir              = $dir === 'DESC' ? 'DESC' : 'ASC';

$mapa_orden = [
    'folio'      => 'oc.folio',
    'proveedor'  => 'p.nombre',
    'monto'      => 'oc.monto',
    'vencimiento'=> 'oc.vencimiento_pago',
    'concepto'   => 'oc.concepto_pago',
    'tipo'       => 'oc.tipo_pago',
    'factura'    => 'oc.genera_factura',
    'usuario'    => 'u.nombre',
    'unidad'     => 'un.nombre',
    'estatus'    => 'oc.estatus_pago'
];
$columna_orden = $mapa_orden[$order_by] ?? 'oc.folio';

$query = "SELECT oc.folio, p.nombre AS proveedor, oc.monto, oc.vencimiento_pago, oc.concepto_pago,
                 oc.tipo_pago, oc.genera_factura, u.nombre AS usuario, un.nombre AS unidad,
                 oc.estatus_pago,
                 c.id AS compra_id, nc.monto AS nota_credito
          FROM ordenes_compra oc
          LEFT JOIN proveedores p ON oc.proveedor_id=p.id
          LEFT JOIN usuarios u ON oc.usuario_solicitante_id=u.id
          LEFT JOIN unidades_negocio un ON oc.unidad_negocio_id=un.id
          LEFT JOIN compras c ON c.orden_id = oc.folio
          LEFT JOIN notas_credito nc ON nc.compra_id = c.id
          WHERE 1=1";

if ($proveedor_filtro !== '') {
    $pid = intval($proveedor_filtro);
    $query .= " AND oc.proveedor_id=$pid";
}
if ($estatus_filtro !== '') {
    $estatus = $conn->real_escape_string($estatus_filtro);
    $query .= " AND oc.estatus_pago='$estatus'";
}
if ($usuario_filtro !== '') {
    $uid = intval($usuario_filtro);
    $query .= " AND oc.usuario_solicitante_id=$uid";
}
if ($unidad_filtro !== '') {
    $unid = intval($unidad_filtro);
    $query .= " AND oc.unidad_negocio_id=$unid";
}
if ($fecha_inicio !== '') {
    $fi = $conn->real_escape_string($fecha_inicio);
    $query .= " AND oc.vencimiento_pago >= '$fi'";
}
if ($fecha_fin !== '') {
    $ff = $conn->real_escape_string($fecha_fin);
    $query .= " AND oc.vencimiento_pago <= '$ff'";
}
$query .= " ORDER BY $columna_orden $dir";
$res = $conn->query($query);
$ordenes = [];
$suma_monto = 0;
while ($row = $res->fetch_assoc()) {
    $ordenes[] = $row;
    $suma_monto += (float)$row['monto'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Órdenes de Compra</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
th { cursor:pointer; }
.sum-row td{font-weight:bold;}
</style>
</head>
<body class="container-fluid p-4">
<h2>Órdenes de Compra</h2>
<div class="mb-3">
    <form class="row g-2" method="GET" id="filtros">
        <div class="col-md-2">
            <select name="proveedor" class="form-select select2-single" data-placeholder="Proveedor">
                <option value="">Proveedor</option>
                <?php $pr=$conn->query("SELECT id,nombre FROM proveedores ORDER BY nombre");
                while($p=$pr->fetch_assoc()):?>
                <option value="<?php echo $p['id']; ?>" <?php echo $proveedor_filtro==$p['id']?'selected':'';?>><?php echo htmlspecialchars($p['nombre']);?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="estatus" class="form-select select2-single" data-placeholder="Estatus de pago">
                <option value="">Estatus de pago</option>
                <?php $ests=['Por pagar','Pagado','Vencido','Pago parcial','Nota de credito abierta','Cancelado'];
                foreach($ests as $e):?>
                <option value="<?php echo $e; ?>" <?php echo $estatus_filtro==$e?'selected':'';?>><?php echo $e; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="usuario" class="form-select select2-single" data-placeholder="Usuario">
                <option value="">Usuario</option>
                <?php $us=$conn->query("SELECT id,nombre FROM usuarios ORDER BY nombre");
                while($u=$us->fetch_assoc()):?>
                <option value="<?php echo $u['id']; ?>" <?php echo $usuario_filtro==$u['id']?'selected':'';?>><?php echo htmlspecialchars($u['nombre']);?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="unidad_negocio" class="form-select select2-single" data-placeholder="Unidad">
                <option value="">Unidad</option>
                <?php $un=$conn->query("SELECT id,nombre FROM unidades_negocio ORDER BY nombre");
                while($u=$un->fetch_assoc()):?>
                <option value="<?php echo $u['id']; ?>" <?php echo $unidad_filtro==$u['id']?'selected':'';?>><?php echo htmlspecialchars($u['nombre']);?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($fecha_inicio);?>" placeholder="Desde">
        </div>
        <div class="col-md-2">
            <input type="date" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($fecha_fin);?>" placeholder="Hasta">
        </div>
        <div class="col-md-12 mt-2">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="minipanel_orden_compras.php" class="btn btn-secondary">Limpiar</a>
            <button type="button" id="btnExportarPDF" class="btn btn-outline-danger">PDF</button>
            <button type="button" id="btnExportarCSV" class="btn btn-outline-success">CSV</button>
        </div>
    </form>
</div>
<div class="mb-3">
    <button class="btn btn-sm btn-outline-dark quick" data-estatus="Por pagar">Órdenes por pagar</button>
    <button class="btn btn-sm btn-outline-dark quick" data-estatus="Vencido">Vencidas</button>
    <button class="btn btn-sm btn-outline-dark quick" data-estatus="Pago parcial">Pago parcial</button>
    <button class="btn btn-sm btn-outline-dark quick" data-estatus="Pagado">Gastos</button>
</div>
<table class="table table-striped" id="tabla">
<thead>
<tr id="columnas-reordenables">
    <th class="col-folio"><a href="?orden=folio&dir=<?php echo $dir==='ASC'?'DESC':'ASC'; ?>">Folio</a></th>
    <th class="col-proveedor"><a href="?orden=proveedor&dir=<?php echo $dir==='ASC'?'DESC':'ASC'; ?>">Proveedor</a></th>
    <th class="col-monto"><a href="?orden=monto&dir=<?php echo $dir==='ASC'?'DESC':'ASC'; ?>">Monto</a></th>
    <th class="col-vencimiento"><a href="?orden=vencimiento&dir=<?php echo $dir==='ASC'?'DESC':'ASC'; ?>">Vencimiento</a></th>
    <th class="col-concepto">Concepto</th>
    <th class="col-tipo">Tipo</th>
    <th class="col-factura">Factura</th>
    <th class="col-usuario">Usuario</th>
    <th class="col-unidad">Unidad</th>
    <th class="col-estatus">Estatus</th>
    <th class="col-compra">Compra</th>
    <th class="col-abono">Abono</th>
    <th class="col-nota">Nota de crédito</th>
    <th class="col-verpdf">Ver PDF</th>
</tr>
</thead>
<tbody>
<?php foreach($ordenes as $o): ?>
<tr>
    <td class="col-folio"><?php echo htmlspecialchars($o['folio']); ?></td>
    <td class="col-proveedor"><?php echo htmlspecialchars($o['proveedor']); ?></td>
    <td class="col-monto">$<?php echo number_format($o['monto'],2); ?></td>
    <td class="col-vencimiento"><?php echo htmlspecialchars($o['vencimiento_pago']); ?></td>
    <td class="col-concepto"><?php echo htmlspecialchars($o['concepto_pago']); ?></td>
    <td class="col-tipo"><?php echo htmlspecialchars($o['tipo_pago']); ?></td>
    <td class="col-factura"><?php echo htmlspecialchars($o['genera_factura']); ?></td>
    <td class="col-usuario"><?php echo htmlspecialchars($o['usuario']); ?></td>
    <td class="col-unidad"><?php echo htmlspecialchars($o['unidad']); ?></td>
    <td class="col-estatus"><?php echo htmlspecialchars($o['estatus_pago']); ?></td>
    <td class="col-compra"><?php echo $o['compra_id']? 'Compra #'.$o['compra_id']:'—'; ?></td>
    <td class="col-abono"><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAbono" data-folio="<?php echo $o['folio']; ?>">Abonar</button></td>
    <td class="col-nota"><?php echo $o['nota_credito']? '$'.number_format($o['nota_credito'],2):'—'; ?></td>
    <td class="col-verpdf"><a class="btn btn-sm btn-outline-dark" target="_blank" href="generar_pdf_compra.php?folio=<?php echo $o['folio']; ?>">Ver PDF</a></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr class="sum-row">
    <td colspan="2">Total</td>
    <td class="col-monto">$<?php echo number_format($suma_monto,2); ?></td>
</tr>
</tfoot>
</table>

<div class="modal fade" id="modalAbono" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formAbono">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Registrar Abono</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="folio" id="abono_folio">
          <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" name="monto" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Comentario</label>
            <textarea name="comentario" class="form-control"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Comprobante</label>
            <input type="file" name="comprobante" class="form-control" accept="image/*,application/pdf">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){
    $('.select2-single').select2({width:'100%'});

    document.querySelectorAll('.quick').forEach(btn=>{
        btn.addEventListener('click',()=>{
            const est=btn.getAttribute('data-estatus');
            const form=document.getElementById('filtros');
            form.querySelector('select[name="estatus"]').value=est;
            form.submit();
        });
    });

    var modal = document.getElementById('modalAbono');
    modal.addEventListener('show.bs.modal',function(e){
        var folio=e.relatedTarget.getAttribute('data-folio');
        document.getElementById('abono_folio').value=folio;
    });
    document.getElementById('formAbono').addEventListener('submit',function(e){
        e.preventDefault();
        var data=new FormData(this);
        fetch('guardar_abono.php',{method:'POST',body:data})
        .then(r=>r.text())
        .then(r=>{ if(r.trim()==='ok'){location.reload();}else{alert(r);} });
    });

    const STORAGE_KEY='oc_columnas';
    function saveConfig(){
        const cfg={};
        document.querySelectorAll('.col-toggle').forEach(c=>{cfg[c.dataset.col]=c.checked;});
        localStorage.setItem(STORAGE_KEY,JSON.stringify(cfg));
    }
    function loadConfig(){
        const cfg=localStorage.getItem(STORAGE_KEY); if(!cfg)return; const obj=JSON.parse(cfg);
        document.querySelectorAll('.col-toggle').forEach(c=>{
            if(obj.hasOwnProperty(c.dataset.col)){c.checked=obj[c.dataset.col];
                document.querySelectorAll('.col-'+c.dataset.col).forEach(td=>{td.style.display=c.checked?'':'none';});
            }
        });
    }
    loadConfig();
    document.querySelectorAll('.col-toggle').forEach(c=>{
        c.addEventListener('change',function(){
            document.querySelectorAll('.col-'+this.dataset.col).forEach(td=>{td.style.display=this.checked?'':'none';});
            saveConfig();
        });
    });
    const columnas=document.getElementById('columnas-reordenables');
    if(columnas){
        Sortable.create(columnas,{animation:150,onEnd:guardarOrden});
        function guardarOrden(){
            let orden=[];columnas.querySelectorAll('th').forEach(th=>orden.push(th.className));
            localStorage.setItem('oc_orden',JSON.stringify(orden));
            ordenarFilas(orden);
        }
        function ordenarFilas(orden){
            let filas=document.querySelectorAll('#tabla tbody tr');
            filas.forEach(tr=>{
                let celdas=Array.from(tr.children);
                let nuevo=[];
                orden.forEach(cls=>{let cel=celdas.find(td=>td.classList.contains(cls)); if(cel)nuevo.push(cel);});
                nuevo.forEach(td=>tr.appendChild(td));
            });
            let foot=document.querySelector('#tabla tfoot tr');
            if(foot){
                let celdas=Array.from(foot.children); let nuevo=[];
                orden.forEach(cls=>{let cel=celdas.find(td=>td.classList.contains(cls)); if(cel)nuevo.push(cel);});
                nuevo.forEach(td=>foot.appendChild(td));
            }
        }
        let guardado=JSON.parse(localStorage.getItem('oc_orden')||'null');
        if(guardado){ ordenarFilas(guardado); }
    }

    document.getElementById('btnExportarPDF').addEventListener('click',function(){
        const url=new URL('exportar_compras_pdf.php',location.origin);
        new URLSearchParams(new FormData(document.getElementById('filtros'))).forEach((v,k)=>url.searchParams.append(k,v));
        window.open(url.toString(),'_blank');
    });
    document.getElementById('btnExportarCSV').addEventListener('click',function(){
        const url=new URL('exportar_compras.php',location.origin);
        new URLSearchParams(new FormData(document.getElementById('filtros'))).forEach((v,k)=>url.searchParams.append(k,v));
        window.open(url.toString(),'_blank');
    });
});
</script>
</body>
</html>
