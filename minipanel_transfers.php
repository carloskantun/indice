<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'auth.php';
include 'conexion.php';

// Paginación
$registros_por_pagina = 500;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Filtros
$where = "WHERE 1=1";
if (!empty($_GET['tipo'])) {
    $t = $conn->real_escape_string($_GET['tipo']);
    $where .= " AND tipo='$t'";
}
if (!empty($_GET['agencia'])) {
    $a = $conn->real_escape_string($_GET['agencia']);
    $where .= " AND agencia LIKE '%$a%'";
}
if (!empty($_GET['operador'])) {
    $op = (int)$_GET['operador'];
    $where .= " AND usuario_creador_id=$op";
}
if (!empty($_GET['fecha_inicio'])) {
    $fi = $conn->real_escape_string($_GET['fecha_inicio']);
    $where .= " AND fecha >= '$fi'";
}
if (!empty($_GET['fecha_fin'])) {
    $ff = $conn->real_escape_string($_GET['fecha_fin']);
    $where .= " AND fecha <= '$ff'";
}

// Ordenamiento dinámico
$mapa_orden_sql = [
    'folio'          => 'folio',
    'tipo'           => 'tipo',
    'fecha'          => 'fecha',
    'pickup'         => 'pickup',
    'hotel'          => 'hotel',
    'pasajeros'      => 'pasajeros',
    'numero_reserva' => 'numero_reserva',
    'vehiculo'       => 'vehiculo',
    'conductor'      => 'conductor',
    'agencia'        => 'agencia',
    'estatus'        => 'estatus',
    'pdf'            => 'folio'
];
$orden_key   = $_GET['orden'] ?? 'id';
$columna_orden = $mapa_orden_sql[$orden_key] ?? 'id';
$direccion   = strtoupper($_GET['dir'] ?? 'ASC');
$direccion   = ($direccion === 'DESC') ? 'DESC' : 'ASC';

$query = "SELECT folio, tipo_servicio AS tipo, fecha_servicio AS fecha, pickup, hotel_pickup AS hotel, nombre_pasajeros AS pasajeros, num_pasajeros AS numero_reserva, vehiculo, conductor, agencia, estatus FROM ordenes_transfers $where ORDER BY $columna_orden $direccion LIMIT $registros_por_pagina OFFSET $offset";
$ordenes = $conn->query($query);
$total   = $conn->query("SELECT COUNT(*) AS total FROM ordenes_transfers $where")->fetch_assoc()['total'];
$total_paginas = ceil($total / $registros_por_pagina);
$rol = $_SESSION['user_role'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        th, td { white-space: nowrap; }
        .table-responsive { overflow-x: auto; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">Transfers</a>
        <div>
            <span class="me-3">Bienvenido, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="admin_panel.php" class="btn btn-secondary btn-sm">Panel de Administracion</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesion</a>
        </div>
    </div>
</nav>
<div class="container-fluid mt-5">

<div class="row g-2 mb-4">
<?php if ($rol === 'superadmin'): ?>
    <div class="col-12 col-md-auto"><button class="btn btn-primary btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">Agregar Usuario</button></div>
<?php endif; ?>
    <div class="col-12 col-md-auto"><button class="btn btn-success btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalIngresarOrden">Registrar Transfer</button></div>
    <div class="col-12 col-md-auto"><button class="btn btn-info btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalKPIs">Resumen de KPIs</button></div>
    <div class="col-12 col-md-auto"><a href="kpis_transfers.php" class="btn btn-primary btn-custom w-100">Ver Detalles de KPIs</a></div>
</div>

<h4 class="mb-3">Órdenes de Transfer</h4>

<div class="accordion mb-4" id="accordionFiltros">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingFiltros">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltros" aria-expanded="true" aria-controls="collapseFiltros">
        Filtros
      </button>
    </h2>
    <div id="collapseFiltros" class="accordion-collapse collapse" aria-labelledby="headingFiltros" data-bs-parent="#accordionFiltros">
      <div class="accordion-body">
        <form method="GET">
          <div class="row g-3">
            <div class="col-12 col-md-3">
              <label class="form-label">Tipo de Servicio</label>
              <select class="form-select select2-single" name="tipo">
                <option value="">Todos</option>
                <option value="Llegada" <?= ($_GET['tipo']??'')=='Llegada'?'selected':'' ?>>Llegada</option>
                <option value="Salida" <?= ($_GET['tipo']??'')=='Salida'?'selected':'' ?>>Salida</option>
                <option value="Roundtrip" <?= ($_GET['tipo']??'')=='Roundtrip'?'selected':'' ?>>Roundtrip</option>
              </select>
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Agencia</label>
              <input type="text" class="form-control" name="agencia" value="<?= htmlspecialchars($_GET['agencia']??'') ?>">
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Operador</label>
              <select class="form-select select2-single" name="operador">
                <option value="">Todos</option>
                <?php $us = $conn->query("SELECT id,nombre FROM usuarios"); while($u=$us->fetch_assoc()): ?>
                  <option value="<?= $u['id'] ?>" <?= (isset($_GET['operador']) && $_GET['operador']==$u['id'])?'selected':'' ?>><?= htmlspecialchars($u['nombre']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Fecha Inicio</label>
              <input type="date" class="form-control" name="fecha_inicio" value="<?= htmlspecialchars($_GET['fecha_inicio']??'') ?>">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Fecha Fin</label>
              <input type="date" class="form-control" name="fecha_fin" value="<?= htmlspecialchars($_GET['fecha_fin']??'') ?>">
            </div>
          </div>
          <div class="text-end mt-2"><a href="minipanel_transfers.php" class="btn btn-outline-secondary">Limpiar Filtros</a></div>
          <div class="text-end mt-3"><button type="submit" class="btn btn-primary">Aplicar Filtros</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="dropdown mb-3">
  <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownColumnas" data-bs-toggle="dropdown" aria-expanded="false">
    Columnas
  </button>
  <ul class="dropdown-menu" aria-labelledby="dropdownColumnas">

    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="folio"> Folio</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="tipo"> Tipo</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="fecha"> Fecha</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="pickup"> Pickup</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="hotel"> Hotel</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="pasajeros"> Pasajeros</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="numero_reserva"> Reserva</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="vehiculo"> Vehículo</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="conductor"> Conductor</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="agencia"> Agencia</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="estatus"> Estatus</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="pdf"> PDF</label></li>
  </ul>
</div>

<div class="row g-2 mb-4">
  <div class="col-12 col-md-auto"><button id="btnExportarCSV" class="btn btn-dark btn-custom w-100">Exportar Resultados</button></div>
  <div class="col-12 col-md-auto"><a id="btnExportarPDF" class="btn btn-danger btn-custom w-100" href="#">Exportar PDF</a></div>
</div>

<div class="table-responsive">
  <table class="table table-striped table-sm">
    <thead>
      <tr id="columnas-reordenables">
<?php
$columnas_ordenables = [
  'folio'          => 'Folio',
  'tipo'           => 'Tipo',
  'fecha'          => 'Fecha',
  'pickup'         => 'Pickup',
  'hotel'          => 'Hotel',
  'pasajeros'      => 'Pasajeros',
  'numero_reserva' => 'Reserva',
  'vehiculo'       => 'Vehículo',
  'conductor'      => 'Conductor',
  'agencia'        => 'Agencia',
  'estatus'        => 'Estatus',
  'pdf'            => 'PDF'
];
$orden_actual = $_GET['orden'] ?? '';
$dir_actual   = $_GET['dir'] ?? 'ASC';
foreach ($columnas_ordenables as $col => $label) {
    $params = $_GET;
    $params['orden'] = $col;
    $params['dir']   = ($orden_actual === $col && $dir_actual === 'ASC') ? 'DESC' : 'ASC';
    $url = '?' . http_build_query($params);
    $icon = ($orden_actual === $col) ? ($dir_actual === 'DESC' ? '↓' : '↑') : '';
    echo "<th class='col-$col'><a href='$url' style='text-decoration:none;color:inherit;'>$label $icon</a></th>";
}
?>
      </tr>
    </thead>
    <tbody id="tabla-ordenes">
<?php if ($ordenes->num_rows === 0): ?>
      <tr><td colspan="12" class="text-center text-danger py-4">No se encontraron resultados con los filtros aplicados.</td></tr>
<?php endif; ?>
<?php while ($o = $ordenes->fetch_assoc()): ?>
      <tr>
        <td class="col-folio"><?= htmlspecialchars($o['folio']) ?></td>
        <td class="col-tipo"><?= htmlspecialchars($o['tipo']) ?></td>
        <td class="col-fecha"><?= htmlspecialchars($o['fecha']) ?></td>
        <td class="col-pickup"><?= htmlspecialchars($o['pickup']) ?></td>
        <td class="col-hotel"><?= htmlspecialchars($o['hotel']) ?></td>
        <td class="col-pasajeros"><?= htmlspecialchars($o['pasajeros']) ?></td>
        <td class="col-numero_reserva"><?= htmlspecialchars($o['numero_reserva']) ?></td>
<?php if (in_array($rol, ['superadmin','admin','supervisor operador'])): ?>
        <td class="col-vehiculo"><input type="text" class="form-control form-control-sm vehiculo-input" data-id="<?= $o['folio'] ?>" value="<?= htmlspecialchars($o['vehiculo']) ?>"></td>
        <td class="col-conductor"><input type="text" class="form-control form-control-sm conductor-input" data-id="<?= $o['folio'] ?>" value="<?= htmlspecialchars($o['conductor']) ?>"></td>
<?php else: ?>
        <td class="col-vehiculo"><?= htmlspecialchars($o['vehiculo']) ?></td>
        <td class="col-conductor"><?= htmlspecialchars($o['conductor']) ?></td>
<?php endif; ?>
        <td class="col-agencia"><?= htmlspecialchars($o['agencia']) ?></td>
<?php if (in_array($rol, ['superadmin','admin','supervisor operador'])): ?>
        <td class="col-estatus">
          <select class="form-select form-select-sm estatus-select" data-id="<?= $o['folio'] ?>">
            <option value="Pendiente" <?= $o['estatus']=='Pendiente'?'selected':'' ?>>Pendiente</option>
            <option value="En proceso" <?= $o['estatus']=='En proceso'?'selected':'' ?>>En proceso</option>
            <option value="Terminado" <?= $o['estatus']=='Terminado'?'selected':'' ?>>Terminado</option>
            <option value="Cancelado" <?= $o['estatus']=='Cancelado'?'selected':'' ?>>Cancelado</option>
          </select>
        </td>
<?php else: ?>
        <td class="col-estatus"><?= htmlspecialchars($o['estatus']) ?></td>
<?php endif; ?>
        <td class="col-pdf"><a href="generar_pdf_transfers.php?folio=<?= $o['folio'] ?>" target="_blank" class="btn btn-sm btn-outline-dark">PDF</a></td>
      </tr>
<?php endwhile; ?>
    </tbody>
  </table>
<?php if ($pagina_actual * $registros_por_pagina < $total): ?>
  <div class="text-center mt-3">
    <button id="ver-mas" class="btn btn-primary" data-pagina="<?= $pagina_actual + 1 ?>">Ver Más</button>
  </div>
<?php endif; ?>
</div>

<div class="modal fade" id="modalAgregarUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-primary text-white"><h5 class="modal-title">Agregar Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="contenidoUsuario"><p class="text-center">Cargando...</p></div>
  </div></div>
</div>

<div class="modal fade" id="modalIngresarOrden" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-success text-white"><h5 class="modal-title">Registrar Transfer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="contenidoOrden"><p class="text-center">Cargando...</p></div>
  </div></div>
</div>

<div class="modal fade" id="modalKPIs" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-info text-white"><h5 class="modal-title">Resumen de KPIs</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div id="kpi-summary-content" class="text-center"><p>Cargando resumen...</p></div></div>
  </div></div>
</div>

<script src="script_modales_transfers.js"></script>

<script>
document.addEventListener('DOMContentLoaded',function(){
  function guardar(){const c={};document.querySelectorAll('.col-toggle').forEach(cb=>{c[cb.dataset.col]=cb.checked;});localStorage.setItem('transfer_cols',JSON.stringify(c));}
  function restaurar(){const c=JSON.parse(localStorage.getItem('transfer_cols')||'{}');document.querySelectorAll('.col-toggle').forEach(cb=>{if(c.hasOwnProperty(cb.dataset.col)){cb.checked=c[cb.dataset.col];document.querySelectorAll('.col-'+cb.dataset.col).forEach(el=>{el.style.display=cb.checked?'':'none';});}});}
  restaurar();
  document.querySelectorAll('.col-toggle').forEach(cb=>cb.addEventListener('change',function(){document.querySelectorAll('.col-'+this.dataset.col).forEach(el=>{el.style.display=this.checked?'':'none';});guardar();}));

  if(typeof Sortable!=='undefined'){const columnas=document.getElementById('columnas-reordenables');const tabla=document.querySelector('table');Sortable.create(columnas,{animation:150,onEnd:()=>{let order=[];columnas.querySelectorAll('th').forEach(th=>order.push(th.className));localStorage.setItem('orden_columnas_transfers',JSON.stringify(order));let filas=tabla.querySelectorAll('tbody tr');filas.forEach(tr=>{let celdas=Array.from(tr.children);let nuevo=[];order.forEach(cls=>{let cel=celdas.find(td=>td.classList.contains(cls));if(cel)nuevo.push(cel);});nuevo.forEach(td=>tr.appendChild(td));});}});let saved=JSON.parse(localStorage.getItem('orden_columnas_transfers')||'[]');if(saved.length>0){let ths=Array.from(columnas.children);let nuevo=[];saved.forEach(cls=>{let th=ths.find(el=>el.classList.contains(cls));if(th)nuevo.push(th);});nuevo.forEach(th=>columnas.appendChild(th));let filas=tabla.querySelectorAll('tbody tr');filas.forEach(tr=>{let celdas=Array.from(tr.children);let nuevo=[];saved.forEach(cls=>{let cel=celdas.find(td=>td.classList.contains(cls));if(cel)nuevo.push(cel);});nuevo.forEach(td=>tr.appendChild(td));});}}

  document.querySelectorAll('.estatus-select').forEach(sel=>sel.addEventListener('change',function(){$.post('actualizar_estatus_transfer.php',{orden_id:this.dataset.id,estatus:this.value});}));
  document.querySelectorAll('.vehiculo-input').forEach(inp=>inp.addEventListener('change',function(){$.post('actualizar_vehiculo_transfer.php',{orden_id:this.dataset.id,vehiculo:this.value});}));
  document.querySelectorAll('.conductor-input').forEach(inp=>inp.addEventListener('change',function(){$.post('actualizar_conductor_transfer.php',{orden_id:this.dataset.id,conductor:this.value});}));

  document.getElementById('btnExportarPDF').addEventListener('click',function(){let cols=[];document.querySelectorAll('thead tr th').forEach(th=>{const c=th.className.trim();if(c&&th.offsetParent!==null)cols.push(c.replace('col-',''));});const f=new URLSearchParams(window.location.search);f.set('columnas',cols.join(','));const ord=document.querySelector('thead th a[href*="orden="]');if(ord){const u=new URL(ord.href);const o=u.searchParams.get('orden');const d=u.searchParams.get('dir');if(o)f.set('orden',o);if(d)f.set('dir',d);}window.open('exportar_transfers_pdf.php?'+f.toString(),'_blank');});
  document.getElementById('btnExportarCSV').addEventListener('click',function(){let cols=[];document.querySelectorAll('thead tr th').forEach(th=>{const c=th.className.trim();if(c&&th.offsetParent!==null)cols.push(c.replace('col-',''));});const f=new URLSearchParams(window.location.search);f.set('columnas',cols.join(','));const ord=document.querySelector('thead th a[href*="orden="]');if(ord){const u=new URL(ord.href);const o=u.searchParams.get('orden');const d=u.searchParams.get('dir');if(o)f.set('orden',o);if(d)f.set('dir',d);}window.open('exportar_transfers.php?'+f.toString(),'_blank');});

  $('.select2-single').select2({width:'100%',allowClear:true});
});

document.querySelectorAll('.vehiculo-input').forEach(inp =>
  inp.addEventListener('change', function () {
    $.post('actualizar_vehiculo_transfer.php', {
      orden_id: this.dataset.id,
      vehiculo: this.value
    });
  })
);

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
