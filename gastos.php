<?php
session_start();
include 'auth.php';
include 'conexion.php';

$proveedor = $_GET['proveedor'] ?? '';
$unidad = $_GET['unidad'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$estatus = $_GET['estatus'] ?? '';
$origen = $_GET['origen'] ?? '';
$orden = $_GET['orden'] ?? 'fecha';
$dir = strtoupper($_GET['dir'] ?? 'DESC');

$cond = [];
if ($proveedor !== '') {
    $cond[] = 'g.proveedor_id=' . intval($proveedor);
}
if ($unidad !== '') {
    $cond[] = 'g.unidad_negocio_id=' . intval($unidad);
}
if ($fecha_inicio !== '') {
    $cond[] = "g.fecha_pago >= '".$conn->real_escape_string($fecha_inicio)."'";
}
if ($fecha_fin !== '') {
    $cond[] = "g.fecha_pago <= '".$conn->real_escape_string($fecha_fin)."'";
}
if ($estatus !== '') {
    $cond[] = "g.estatus='".$conn->real_escape_string($estatus)."'";
}
if ($origen !== '') {
    $cond[] = "g.origen='".$conn->real_escape_string($origen)."'";
}
$where = $cond ? 'WHERE '.implode(' AND ',$cond) : '';

$mapa_orden_sql = [
    'folio'    => 'g.folio',
    'proveedor'=> 'p.nombre',
    'monto'    => 'g.monto',
    'fecha'    => 'g.fecha_pago',
    'unidad'   => 'un.nombre',
    'tipo'     => 'g.tipo_gasto',
    'medio'    => 'g.medio_pago',
    'cuenta'   => 'g.cuenta_bancaria',
    'concepto' => 'g.concepto',
    'estatus'  => 'g.estatus'
];
$columna_orden = $mapa_orden_sql[$orden] ?? 'g.fecha_pago';
$dir = $dir === 'ASC' ? 'ASC' : 'DESC';

$sql = "SELECT g.folio, p.nombre AS proveedor, g.monto, g.fecha_pago, un.nombre AS unidad, g.tipo_gasto, g.medio_pago, g.cuenta_bancaria, g.concepto, g.estatus FROM gastos g LEFT JOIN proveedores p ON g.proveedor_id=p.id LEFT JOIN unidades_negocio un ON g.unidad_negocio_id=un.id $where ORDER BY $columna_orden $dir";
$res = $conn->query($sql);
$gastos = $res->fetch_all(MYSQLI_ASSOC);

$kpi_mes = $conn->query("SELECT SUM(monto) AS total FROM gastos WHERE MONTH(fecha_pago)=MONTH(CURDATE()) AND YEAR(fecha_pago)=YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;
$kpi_anio = $conn->query("SELECT SUM(monto) AS total FROM gastos WHERE YEAR(fecha_pago)=YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gastos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-light bg-white shadow-sm">
    <div class="container">
        <span class="navbar-brand">Módulo de Gastos</span>
        <a href="menu_principal.php" class="btn btn-outline-primary btn-sm">Menú</a>
    </div>
</nav>
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col">
            <div class="card">
                <div class="card-body text-center">
                    <strong>Gastos del Mes</strong><br>$<?php echo number_format($kpi_mes,2); ?>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body text-center">
                    <strong>Gastos del Año</strong><br>$<?php echo number_format($kpi_anio,2); ?>
                </div>
            </div>
        </div>
        <div class="col text-end align-self-center">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalGasto">Agregar Gasto</button>
        </div>
    </div>
    <form class="row g-2 mb-4" id="filtros" method="GET">
        <div class="col-md">
            <select name="proveedor" class="form-select select2" data-placeholder="Proveedor">
                <option value="">Proveedor</option>
                <?php $pro=$conn->query("SELECT id,nombre FROM proveedores ORDER BY nombre");
                while($p=$pro->fetch_assoc()): ?>
                <option value="<?php echo $p['id']; ?>" <?php if($proveedor==$p['id']) echo 'selected';?>><?php echo htmlspecialchars($p['nombre']);?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md">
            <select name="unidad" class="form-select" data-placeholder="Unidad">
                <option value="">Unidad</option>
                <?php $un=$conn->query("SELECT id,nombre FROM unidades_negocio ORDER BY nombre");
                while($u=$un->fetch_assoc()): ?>
                <option value="<?php echo $u['id']; ?>" <?php if($unidad==$u['id']) echo 'selected';?>><?php echo htmlspecialchars($u['nombre']);?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md">
            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($fecha_inicio);?>">
        </div>
        <div class="col-md">
            <input type="date" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($fecha_fin);?>">
        </div>
        <div class="col-md">
            <select name="estatus" class="form-select">
                <option value="">Estatus</option>
                <?php $ests=['Pagado','Por pagar','Pago parcial','Vencido'];
                foreach($ests as $e): ?>
                <option value="<?php echo $e; ?>" <?php if($estatus==$e) echo 'selected';?>><?php echo $e; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md">
            <select name="origen" class="form-select">
                <option value="">Tipo</option>
                <option value="Directo" <?php if($origen==='Directo') echo 'selected';?>>Directo</option>
                <option value="Orden" <?php if($origen==='Orden') echo 'selected';?>>Orden</option>
            </select>
        </div>
        <div class="col-md">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>
    <div class="mb-3">
        <a href="exportar_gastos_pdf.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="btn btn-outline-danger btn-sm">PDF</a>
        <a href="exportar_gastos.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="btn btn-outline-success btn-sm">CSV</a>
    </div>
    <div class="mb-3">
        <button type="button" class="btn btn-sm btn-outline-dark quick-filter" data-origen="Orden" data-estatus="Por pagar">Órdenes por pagar</button>
        <button type="button" class="btn btn-sm btn-outline-dark quick-filter" data-origen="" data-estatus="Pagado">Gastos</button>
        <button type="button" class="btn btn-sm btn-outline-dark quick-filter" data-origen="Orden" data-estatus="Vencido">Órdenes vencidas</button>
        <button type="button" class="btn btn-sm btn-outline-dark quick-filter" data-origen="Orden" data-estatus="Pago parcial">Órdenes en pago parcial</button>
    </div>
    <div class="dropdown mb-3">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            Columnas
        </button>
        <ul class="dropdown-menu">
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="folio" checked> Folio</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="proveedor" checked> Proveedor</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="monto" checked> Monto</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="fecha" checked> Fecha de pago</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="unidad" checked> Unidad</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="tipo" checked> Tipo</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="medio" checked> Medio de pago</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="cuenta" checked> Cuenta</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="concepto" checked> Concepto</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="estatus" checked> Estatus</label></li>
        </ul>
    </div>
    <div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr id="columnas-reordenables">
<?php
$cols = [
    'folio'     => 'Folio',
    'proveedor' => 'Proveedor',
    'monto'     => 'Monto',
    'fecha'     => 'Fecha de pago',
    'unidad'    => 'Unidad',
    'tipo'      => 'Tipo',
    'medio'     => 'Medio de pago',
    'cuenta'    => 'Cuenta',
    'concepto'  => 'Concepto',
    'estatus'   => 'Estatus'
];
$orden_actual = $_GET['orden'] ?? '';
$dir_actual = $_GET['dir'] ?? 'ASC';
foreach ($cols as $c => $label):
    $params = $_GET;
    $params['orden'] = $c;
    $params['dir'] = ($orden_actual === $c && $dir_actual === 'ASC') ? 'DESC' : 'ASC';
    $url = '?' . http_build_query($params);
    $icon = ($orden_actual === $c) ? ($dir_actual === 'DESC' ? '▼' : '▲') : '';
?>
                <th class="col-<?php echo $c; ?>">
                    <a href="<?php echo htmlspecialchars($url); ?>" style="text-decoration:none;color:inherit;">
                        <?php echo $label . ' ' . $icon; ?>
                    </a>
                </th>
<?php endforeach; ?>
                <th class="col-pdf">PDF</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($gastos as $g): ?>
            <tr>
                <td class="col-folio"><?php echo htmlspecialchars($g['folio']); ?></td>
                <td class="col-proveedor"><?php echo htmlspecialchars($g['proveedor']); ?></td>
                <td class="col-monto">$<?php echo number_format($g['monto'],2); ?></td>
                <td class="col-fecha"><?php echo htmlspecialchars($g['fecha_pago']); ?></td>
                <td class="col-unidad"><?php echo htmlspecialchars($g['unidad']); ?></td>
                <td class="col-tipo"><?php echo htmlspecialchars($g['tipo_gasto']); ?></td>
                <td class="col-medio"><?php echo htmlspecialchars($g['medio_pago']); ?></td>
                <td class="col-cuenta"><?php echo htmlspecialchars($g['cuenta_bancaria']); ?></td>
                <td class="col-concepto"><?php echo htmlspecialchars($g['concepto']); ?></td>
                <td class="col-estatus"><?php echo htmlspecialchars($g['estatus']); ?></td>
                <td class="col-pdf"><a class="btn btn-sm btn-outline-dark" target="_blank" href="generar_pdf_gasto.php?folio=<?php echo $g['folio']; ?>">PDF</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<div class="modal fade" id="modalGasto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="contenidoGasto"></div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
    $('.select2').select2({width:'100%'});
    $('#modalGasto').on('show.bs.modal', function(){
        $('#contenidoGasto').load('modal_gasto.php?modal=1');
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded',function(){
  const KEY='gastos_columnas';
  function save(){const c={};document.querySelectorAll('.col-toggle').forEach(cb=>{c[cb.dataset.col]=cb.checked;});localStorage.setItem(KEY,JSON.stringify(c));}
  function restore(){const c=JSON.parse(localStorage.getItem(KEY)||'{}');document.querySelectorAll('.col-toggle').forEach(cb=>{if(c.hasOwnProperty(cb.dataset.col)){cb.checked=c[cb.dataset.col];}document.querySelectorAll('.col-'+cb.dataset.col).forEach(el=>{el.style.display=cb.checked?'':'none';if(c.hasOwnProperty(cb.dataset.col))el.style.display=c[cb.dataset.col]?'':'none';});});}
  restore();
  document.querySelectorAll('.col-toggle').forEach(cb=>cb.addEventListener('change',function(){document.querySelectorAll('.col-'+this.dataset.col).forEach(el=>{el.style.display=this.checked?'':'none';});save();}));
});
</script>
<script>
document.addEventListener('DOMContentLoaded',function(){
  if(typeof Sortable!=='undefined'){
    const columnas=document.getElementById('columnas-reordenables');
    const tabla=document.querySelector('table');
    Sortable.create(columnas,{animation:150,onEnd:()=>{let order=[];columnas.querySelectorAll('th').forEach(th=>order.push(th.className));localStorage.setItem('orden_columnas_gastos',JSON.stringify(order));let filas=tabla.querySelectorAll('tbody tr');filas.forEach(tr=>{let celdas=Array.from(tr.children);let nuevo=[];order.forEach(cls=>{let cel=celdas.find(td=>td.classList.contains(cls));if(cel)nuevo.push(cel);});nuevo.forEach(td=>tr.appendChild(td));});}});
    let saved=JSON.parse(localStorage.getItem('orden_columnas_gastos')||'[]');
    if(saved.length>0){let ths=Array.from(columnas.children);let nuevo=[];saved.forEach(cls=>{let th=ths.find(el=>el.classList.contains(cls));if(th)nuevo.push(th);});nuevo.forEach(th=>columnas.appendChild(th));let filas=tabla.querySelectorAll('tbody tr');filas.forEach(tr=>{let celdas=Array.from(tr.children);let nuevo=[];saved.forEach(cls=>{let cel=celdas.find(td=>td.classList.contains(cls));if(cel)nuevo.push(cel);});nuevo.forEach(td=>tr.appendChild(td));});}
  }
});
</script>
<script>
document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('.quick-filter').forEach(btn=>{
        btn.addEventListener('click',function(){
            const form=document.getElementById('filtros');
            if(form){
                const est=this.dataset.estatus||'';
                const ori=this.dataset.origen||'';
                form.querySelector('[name="estatus"]').value=est;
                form.querySelector('[name="origen"]').value=ori;
                form.submit();
            }
        });
    });
});
</script>
</body>
</html>
