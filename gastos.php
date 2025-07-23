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
    'tipo_compra' => 'g.tipo_compra',
    'medio'    => 'g.medio_pago',
    'cuenta'   => 'g.cuenta_bancaria',
    'concepto' => 'g.concepto',
    'estatus'  => 'g.estatus'
];
$columna_orden = $mapa_orden_sql[$orden] ?? 'g.fecha_pago';
$dir = $dir === 'ASC' ? 'ASC' : 'DESC';

$sql = "SELECT 
    g.id, 
    g.folio, 
    CASE 
  WHEN g.nota_credito_id IS NOT NULL THEN u.nombre
  ELSE p.nombre
END AS proveedor, 
    g.monto, 
    g.fecha_pago, 
    un.nombre AS unidad, 
    g.tipo_gasto,
    g.tipo_compra,
    g.medio_pago,
    g.cuenta_bancaria, 
    g.concepto, 
    g.estatus, 
    g.origen,
    (SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id) AS abonado_total,
    (g.monto - IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS saldo
FROM gastos g
LEFT JOIN proveedores p ON g.proveedor_id = p.id
LEFT JOIN unidades_negocio un ON g.unidad_negocio_id = un.id
LEFT JOIN notas_credito nc ON g.nota_credito_id = nc.id
LEFT JOIN usuarios u ON nc.usuario_responsable_id = u.id

$where
ORDER BY $columna_orden $dir";

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
<link href="css/style.css" rel="stylesheet">
<script src="includes/assets/js/kpis_gastos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

<div class="col text-end align-self-center d-flex justify-content-end gap-2">
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalOrden">
        Nueva Orden de Compra
    </button>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalGasto">
        Nuevo Gasto
    </button>
    <!-- 👇 Agrega este botón -->
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalKpisGastos">
        📊 Ver Análisis de KPIs
    </button>
</div>


    </div>
    <form class="row g-2 mb-4" id="form-filtros" method="GET">
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
            <a href="gastos.php" class="btn btn-outline-secondary w-100">Limpiar filtros</a>
        </div>
    </form>
    <div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        <a href="exportar_gastos_pdf.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="btn btn-outline-danger btn-sm">PDF</a>
        <a href="exportar_gastos.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="btn btn-outline-success btn-sm">CSV</a>
    <button id="btnEliminarSeleccionados" class="btn btn-danger d-none">
        Eliminar seleccionados
    </button>
    </div>
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
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="tipo_compra" checked> Tipo Compra/Gasto</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="medio" checked> Forma</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="cuenta" checked> Cuenta</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="concepto" checked> Concepto</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="estatus" checked> Estatus</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="abonado" checked> Abonado</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="saldo" checked> Saldo</label></li>
            <li><label class="dropdown-item"><input type="checkbox" class="col-toggle" data-col="comprobante" checked> Recibo</label></li>

        </ul>
    </div>
    <div class="table-responsive">
    <table class="table table-striped">
        <thead>
            
            <tr id="columnas-reordenables">
            <?php if ($_SESSION['user_role'] === 'superadmin'): ?>
    <th class="col-seleccion">
        <input type="checkbox" id="seleccionar-todos">
    </th>
<?php endif; ?>
<?php
$cols = [
    'folio'     => 'Folio',
    'proveedor' => 'Proveedor',
    'monto'     => 'Monto',
    'fecha'     => 'Día de pago',
    'unidad'    => 'Unidad',
    'tipo'      => 'Tipo',
    'tipo_compra' => 'Uso',
    'medio'     => 'Forma',
    'cuenta'    => 'Cuenta',
    'concepto'  => 'Concepto',
    'estatus'   => 'Estatus',
    'abonado'   => 'Abonado',
    'saldo'     => 'Saldo',
    'comprobante'=> 'Recibo',
    'accion'    => 'Pagar'
];
$orden_actual = $_GET['orden'] ?? '';
$dir_actual = $_GET['dir'] ?? 'ASC';
foreach ($cols as $c => $label):
    $params = $_GET;
    $params['orden'] = $c;
    $params['dir'] = ($orden_actual === $c && $dir_actual === 'ASC') ? 'DESC' : 'ASC';
    $url = '?' . http_build_query($params);
    $icon = ($orden_actual === $c) ? ($dir_actual === 'DESC' ? 'â–¼' : 'â–²') : '';
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
            <?php if ($_SESSION['user_role'] === 'superadmin'): ?>
    <td class="col-seleccion">
        <input type="checkbox" class="seleccionar-gasto" value="<?php echo $g['id']; ?>">
    </td>
<?php endif; ?>
                <td class="col-folio"><?php echo htmlspecialchars($g['folio']); ?></td>
                <td class="col-proveedor">
                            <?php
                                $mostrar = $g['proveedor'];
                                if ($g['nota_credito_id']) {
                                    $mostrar = '<span class="text-info">Usuario: ' . htmlspecialchars($mostrar) . '</span>';
                                }
                            echo $mostrar;
                            ?>
                        </td>
                <td class="col-monto monto">$<?php echo number_format($g['monto'],2); ?></td>
                <td class="col-abonado abono">$<?php echo number_format($g['abonado_total'] ?? 0, 2); ?></td>
                <td class="col-saldo saldo">$<?php echo number_format($g['saldo'] ?? ($g['monto'] - ($g['abonado_total'] ?? 0)), 2); ?></td>
                <td class="col-fecha"><?php echo htmlspecialchars($g['fecha_pago']); ?></td>
                <td class="col-unidad"><?php echo htmlspecialchars($g['unidad']); ?></td>
                <td class="col-tipo">
<?php
$origen = $g['origen'];
$tipo   = $g['tipo_gasto'];
$estatus = $g['estatus'];

if ($origen === 'Orden') {
    if ($estatus === 'Pagado') {
        echo "Orden ($tipo) â†’ Gasto";
    } else {
        echo "Orden ($tipo)";
    }
} else {
    echo "Gasto ($tipo)";
}
?>
                </td>
                <td class="col-tipo_compra">
<?php if ($_SESSION['user_role'] === 'superadmin'): ?>
    <select class="form-select form-select-sm editable-campo" data-id="<?= $g['id']; ?>" data-campo="tipo_compra">
        <?php foreach (['Venta', 'Administrativa', 'Operativo', 'Impuestos', 'Intereses/CrÃ©ditos'] as $op): ?>
            <option value="<?= $op ?>" <?= $g['tipo_compra'] === $op ? 'selected' : '' ?>><?= $op ?></option>
        <?php endforeach; ?>
    </select>
<?php else: ?>
    <?= htmlspecialchars($g['tipo_compra']) ?>
<?php endif; ?>
</td>

                <td class="col-medio">
<?php if ($_SESSION['user_role'] === 'superadmin'): ?>
    <select class="form-select form-select-sm editable-campo" data-id="<?= $g['id']; ?>" data-campo="medio_pago">
        <?php foreach (['Tarjeta', 'Transferencia', 'Efectivo'] as $op): ?>
            <option value="<?= $op ?>" <?= $g['medio_pago'] === $op ? 'selected' : '' ?>><?= $op ?></option>
        <?php endforeach; ?>
    </select>
<?php else: ?>
    <?= htmlspecialchars($g['medio_pago']) ?>
<?php endif; ?>
</td>

                <td class="col-cuenta">
<?php if ($_SESSION['user_role'] === 'superadmin'): ?>
    <input type="text" class="form-control form-control-sm editable-campo" data-id="<?= $g['id']; ?>" data-campo="cuenta_bancaria" value="<?= htmlspecialchars($g['cuenta_bancaria']) ?>">
<?php else: ?>
    <?= htmlspecialchars($g['cuenta_bancaria']) ?>
<?php endif; ?>
</td>

                <td class="col-concepto">
<?php if ($_SESSION['user_role'] === 'superadmin'): ?>
    <input type="text" class="form-control form-control-sm editable-campo" data-id="<?= $g['id']; ?>" data-campo="concepto" value="<?= htmlspecialchars($g['concepto']) ?>">
<?php else: ?>
    <?= htmlspecialchars($g['concepto']) ?>
<?php endif; ?>
</td>

                <td class="col-estatus">
<?php if ($_SESSION['user_role'] === 'superadmin'): ?>
    <select class="form-select form-select-sm editable-campo" data-id="<?= $g['id']; ?>" data-campo="estatus">
        <?php foreach (['Pagado', 'Por pagar', 'Pago parcial', 'Vencido'] as $op): ?>
            <option value="<?= $op ?>" <?= $g['estatus'] === $op ? 'selected' : '' ?>><?= $op ?></option>
        <?php endforeach; ?>
    </select>
<?php else: ?>
    <?= htmlspecialchars($g['estatus']) ?>
<?php endif; ?>
</td>

<td class="col-comprobante">
<?php
$sqlComps = "SELECT archivo_comprobante FROM abonos_gastos 
             WHERE gasto_id = {$g['id']} AND archivo_comprobante IS NOT NULL 
             ORDER BY id ASC";
$resComps = $conn->query($sqlComps);
$comps = [];
while ($row = $resComps->fetch_assoc()) {
    $comps[] = $row['archivo_comprobante'];
}
if (count($comps) === 1) {
    echo '<a href="' . htmlspecialchars($comps[0]) . '" target="_blank" class="btn btn-sm btn-outline-secondary">Ver</a>';
} elseif (count($comps) > 1) {
    echo '<button class="btn btn-sm btn-outline-secondary ver-comprobantes-btn" data-id="' . $g['id'] . '">Ver</button>';
} else {
    echo '<span class="text-muted">Sin archivo</span>';
}
?>
</td>

<td class="col-accion">
    <?php if($g['origen'] === 'Orden' && $g['estatus'] !== 'Pagado'): ?>
        <button class="btn btn-sm btn-outline-primary pagar-btn" data-id="<?php echo $g['id']; ?>">Pagar</button>
    <?php endif; ?>

    <?php if ($_SESSION['user_role'] === 'superadmin'): ?>
        <button class="btn btn-sm btn-outline-warning editar-gasto-btn" data-id="<?php echo $g['id']; ?>">Editar</button>
        <a href="eliminar_gasto.php?id=<?php echo $g['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Â¿EstÃ¡s seguro de eliminar este gasto?')">Eliminar</a>
    <?php endif; ?>
</td>

                <td class="col-pdf"><a class="btn btn-sm btn-outline-dark" target="_blank" href="generar_pdf_gasto.php?folio=<?php echo $g['folio']; ?>">PDF</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        
        <tfoot id="tfoot-dinamico"></tfoot>
  </table>
<div id="resumen-seleccionados" class="alert alert-light border-top border-dark fixed-bottom shadow-sm py-2 px-4 d-flex justify-content-between align-items-center d-none">
  <div>
    <strong>Totales Seleccionados:</strong>
    Monto: <span id="sel-monto">$0.00</span> —
    Abonado: <span id="sel-abono">$0.00</span> —
    Saldo: <span id="sel-saldo">$0.00</span>
  </div>
  <div>
    <button class="btn btn-sm btn-outline-danger me-2" id="btn-exportar-pdf">Exportar PDF</button>
    <button class="btn btn-sm btn-outline-success" id="btn-exportar-csv">Exportar CSV</button>
  </div>
</div>

  
</div>


    </div>
</div>

<div class="modal fade" id="modalOrden" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <?php include 'modal_orden.php'; ?>
    </div>
  </div>
</div>

<div class="modal fade" id="modalGasto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="contenidoGasto"></div>
  </div>
</div>
<div class="modal fade" id="modalAbono" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="contenidoAbono">Cargando...</div>
  </div>
</div>
<div class="modal fade" id="modalComprobantes" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" id="contenidoComprobantes">Cargando...</div>
  </div>
</div>

<div class="modal fade" id="modalEditarGasto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="contenidoEditarGasto">Cargando...</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
  // Activar select2 en filtros visibles
  $('.select2').select2({ width: '100%' });

  // Modal: Nuevo Gasto
  $('#modalGasto').on('show.bs.modal', function(){
    $('#contenidoGasto').load('modal_gasto.php?modal=1', function(){
      $('#contenidoGasto .select2').select2({
        width: '100%',
        dropdownParent: $('#modalGasto')
      });
    });
  });

  // Modal: Nueva Orden
  $('#modalOrden').on('show.bs.modal', function(){
    const cont = $('#modalOrden .modal-content');
    cont.html('Cargando...');
    cont.load('modal_orden.php?modal=1', function(){
      cont.find('.select2').select2({
        width: '100%',
        dropdownParent: $('#modalOrden')
      });

      // Mostrar u ocultar campos recurrentes
      const tipoSelect = cont.find('[name="tipo_gasto"]');
      const camposRecurrente = cont.find('#camposRecurrente');
      function toggleCampos() {
        camposRecurrente.toggle(tipoSelect.val() === 'Recurrente');
      }
      tipoSelect.on('change', toggleCampos);
      toggleCampos();
    });
  });

  // Modal: Editar Gasto
  $('#modalEditarGasto').on('shown.bs.modal', function(){
    $('#contenidoEditarGasto .select2').select2({
      width: '100%',
      dropdownParent: $('#modalEditarGasto')
    });
  });

  // Modal: Editar Orden (si lo usas)
  $('#modalEditarOrden').on('shown.bs.modal', function(){
    $('#contenidoEditarOrden .select2').select2({
      width: '100%',
      dropdownParent: $('#modalEditarOrden')
    });
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
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.quick-filter').forEach(btn => {
    btn.addEventListener('click', function () {
      const form = document.getElementById('form-filtros');
      if (!form) return;

      // Limpiar todos los campos del formulario
      form.querySelectorAll('select, input').forEach(el => {
        el.value = '';
      });

      // Asignar solo estatus y origen
      const est = this.dataset.estatus || '';
      const ori = this.dataset.origen || '';
      form.querySelector('[name="estatus"]').value = est;
      form.querySelector('[name="origen"]').value = ori;

      // Guardar en localStorage también
      const filtros = {
        estatus: est,
        origen: ori
      };
      localStorage.setItem('filtros_gastos', JSON.stringify(filtros));

      form.submit();
    });
  });
});
</script>

<script>
document.addEventListener('click',function(e){
    if(e.target.classList.contains('pagar-btn')){
        const id=e.target.dataset.id;
        const modal=document.getElementById('modalAbono');
        const cont=document.getElementById('contenidoAbono');
        cont.innerHTML='Cargando...';
        var myModal=new bootstrap.Modal(modal);
        myModal.show();
        // Cargar el contenido del formulario y ejecutar los scripts incluidos
        $(cont).load('modal_abono.php?id='+id);
    }
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('ver-comprobantes-btn')) {
        const id = e.target.dataset.id;
        const modal = document.getElementById('modalComprobantes');
        const cont = document.getElementById('contenidoComprobantes');
        cont.innerHTML = 'Cargando...';
        var myModal = new bootstrap.Modal(modal);
        myModal.show();
        $(cont).load('modal_comprobantes.php?id=' + id);
    }
});

</script>
<script>
function sumarTotales() {
    let totalMonto = 0;
    let totalAbono = 0;
    let totalSaldo = 0;

    document.querySelectorAll('.monto').forEach(el => {
        totalMonto += parseFloat(el.textContent.replace(/[$,]/g, '')) || 0;
    });

    document.querySelectorAll('.abono').forEach(el => {
        totalAbono += parseFloat(el.textContent.replace(/[$,]/g, '')) || 0;
    });

    document.querySelectorAll('.saldo').forEach(el => {
        totalSaldo += parseFloat(el.textContent.replace(/[$,]/g, '')) || 0;
    });
const elMonto = document.getElementById('total-monto');
if (elMonto) elMonto.textContent = totalMonto.toLocaleString('es-MX', {style:'currency', currency:'MXN'});

    document.getElementById('total-monto').textContent = totalMonto.toLocaleString('es-MX', {style:'currency', currency:'MXN'});
    document.getElementById('total-abono').textContent = totalAbono.toLocaleString('es-MX', {style:'currency', currency:'MXN'});
    document.getElementById('total-saldo').textContent = totalSaldo.toLocaleString('es-MX', {style:'currency', currency:'MXN'});
}

document.addEventListener('DOMContentLoaded', sumarTotales);
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.querySelector('table');
    const cuerpo = tabla.querySelector('tbody');
    const filas = cuerpo.querySelectorAll('tr');

    let totalMonto = 0, totalAbono = 0, totalSaldo = 0;

    filas.forEach(tr => {
        const monto = parseFloat(tr.querySelector('.col-monto')?.textContent.replace(/[$,]/g, '') || 0);
        const abonado = parseFloat(tr.querySelector('.col-abonado')?.textContent.replace(/[$,]/g, '') || 0);
        const saldo = parseFloat(tr.querySelector('.col-saldo')?.textContent.replace(/[$,]/g, '') || 0);

        totalMonto += monto;
        totalAbono += abonado;
        totalSaldo += saldo;
    });

    const columnas = tabla.querySelectorAll('thead th');
    const tfoot = document.getElementById('tfoot-dinamico');
    const fila = document.createElement('tr');

    columnas.forEach(th => {
        const td = document.createElement('td');
        const clase = th.className;

        if (clase.includes('col-monto')) {
            td.innerHTML = `<strong>$${totalMonto.toLocaleString('es-MX', {minimumFractionDigits:2})}</strong>`;
        } else if (clase.includes('col-abonado')) {
            td.innerHTML = `<strong>$${totalAbono.toLocaleString('es-MX', {minimumFractionDigits:2})}</strong>`;
        } else if (clase.includes('col-saldo')) {
            td.innerHTML = `<strong>$${totalSaldo.toLocaleString('es-MX', {minimumFractionDigits:2})}</strong>`;
        } else if (clase.includes('col-folio')) {
            td.innerHTML = '<strong>Totales:</strong>';
        } else {
            td.innerHTML = '';
        }

        fila.appendChild(td);
    });

    tfoot.innerHTML = '';
    tfoot.appendChild(fila);
});
</script>

<script>

document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.seleccionar-gasto');
    const btnEliminar = document.getElementById('btnEliminarSeleccionados');
    const chkTodos = document.getElementById('seleccionar-todos');

    function actualizarBoton() {
        const algunoMarcado = Array.from(checkboxes).some(cb => cb.checked);
        btnEliminar.classList.toggle('d-none', !algunoMarcado);
    }

    checkboxes.forEach(cb => cb.addEventListener('change', actualizarBoton));

    chkTodos?.addEventListener('change', function () {
        checkboxes.forEach(cb => cb.checked = chkTodos.checked);
        actualizarBoton();
    });

    // âœ… SOLO esta funciÃ³n:
    btnEliminar?.addEventListener('click', function () {
        const ids = Array.from(document.querySelectorAll('.seleccionar-gasto'))
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (!ids.length) return;

        if (!confirm('Â¿EstÃ¡s seguro de eliminar los gastos seleccionados?')) return;

        const params = new URLSearchParams();
        ids.forEach(id => params.append('ids[]', id));

        fetch('eliminar_gastos_multiples.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: params.toString()
        })
        .then(res => res.text())
        .then(res => {
            if (res.trim() === 'ok') {
                location.reload();
            } else {
                alert('Error: ' + res);
            }
        })
        .catch(() => alert('Error en la conexiÃ³n'));
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const modalOrden = document.getElementById("modalOrden");

    modalOrden.addEventListener("show.bs.modal", function () {
        const cont = document.getElementById("contenidoOrden");
        cont.innerHTML = "Cargando...";

        fetch("modal_orden.php")
            .then(res => res.text())
            .then(html => {
                cont.innerHTML = html;

                const tipoSelect = cont.querySelector('select[name="tipo_gasto"]');
                const camposRecurrente = cont.querySelector('#camposRecurrente');
                const form = cont.querySelector('#formOrden');

                function toggleCampos() {
                    camposRecurrente.style.display = (tipoSelect.value === 'Recurrente') ? 'block' : 'none';
                }

                tipoSelect?.addEventListener('change', toggleCampos);
                toggleCampos(); // por si ya está seleccionado

                form?.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const datos = new FormData(form);

                    fetch("guardar_gasto.php", {
                        method: "POST",
                        body: datos
                    })
                    .then(res => res.text())
                    .then(respuesta => {
                        if (respuesta.trim() === 'ok') {
                            alert("✅ Orden registrada correctamente");
                            bootstrap.Modal.getInstance(modalOrden).hide();
                            location.reload(); // o actualizar parte de la tabla
                        } else {
                            alert("❌ Error: " + respuesta);
                        }
                    })
                    .catch(() => alert("❌ Error de conexión"));
                });
            })
            .catch(() => {
                cont.innerHTML = "<div class='p-3 text-danger'>Error al cargar el formulario.</div>";
            });
    });
});


</script>
<script>
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("editar-gasto-btn")) {
        const id = e.target.dataset.id;
        const modal = document.getElementById("modalEditarGasto");
        const cont = document.getElementById("contenidoEditarGasto");
        cont.innerHTML = "Cargando...";
        const myModal = new bootstrap.Modal(modal);
        myModal.show();

        fetch("modal_editar_gasto.php?id=" + id)
            .then(res => res.text())
            .then(html => {
                cont.innerHTML = html;
            })
            .catch(() => {
                cont.innerHTML = "<div class='p-3 text-danger'>Error al cargar el formulario.</div>";
            });
    }
});
</script>
<script>
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('editable-campo')) {
        const campo = e.target.dataset.campo;
        const id = e.target.dataset.id;
        const valor = e.target.value;

        fetch('actualizar_campo_gasto.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({ id, campo, valor })
        })
        .then(res => res.text())
        .then(res => {
            if (res.trim() !== 'ok') {
                alert('Error al guardar: ' + res);
            }
        })
        .catch(() => alert('Error en la conexión'));
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const KEY = 'filtros_gastos';
  const form = document.getElementById('form-filtros');

  function getURLParams() {
    const params = new URLSearchParams(window.location.search);
    let data = {};
    let hasParams = false;

    for (const param of ['proveedor', 'unidad', 'estatus', 'fecha_inicio', 'fecha_fin', 'origen']) {
      const val = params.get(param);
      if (val !== null) {
        data[param] = val;
        hasParams = true;
      }
    }

    if (hasParams) {
      localStorage.setItem(KEY, JSON.stringify(data)); // Guardar como filtro actual
    }

    return hasParams;
  }

  function restore() {
    const data = JSON.parse(localStorage.getItem(KEY) || '{}');
    form.querySelectorAll('select, input').forEach(el => {
      if (data[el.name] !== undefined) el.value = data[el.name];
    });
  }

  function save() {
    const data = {};
    form.querySelectorAll('select, input').forEach(el => {
      data[el.name] = el.value;
    });
    localStorage.setItem(KEY, JSON.stringify(data));
  }

  // Limpiar localStorage si se visita con URL limpia (desde botón "Limpiar filtros")
  if (window.location.search === '') {
    localStorage.removeItem(KEY);
  }

  const urlTieneFiltros = getURLParams(); // También guarda si hay
  restore();
  form.querySelectorAll('select, input').forEach(el => el.addEventListener('change', save));
});
</script>

<script src="includes/assets/js/gastos_sumatoria_seleccionados.js"></script>

<!-- Modal KPIs -->
<?php include 'includes/modals/modal_kpis_gastos.php'; ?>


</body>
</html>