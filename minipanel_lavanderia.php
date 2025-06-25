<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
include 'auth.php';
include 'conexion.php';

$registros_por_pagina = 500;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$where = "WHERE 1=1";
if (!empty($_GET['cliente'])) {
    $c = $conn->real_escape_string($_GET['cliente']);
    $where .= " AND cliente LIKE '%$c%'";
}
if (!empty($_GET['servicio'])) {
    $s = $conn->real_escape_string($_GET['servicio']);
    $where .= " AND servicio LIKE '%$s%'";
}
if (!empty($_GET['estatus'])) {
    $e = $conn->real_escape_string($_GET['estatus']);
    $where .= " AND estatus='$e'";
}
if (!empty($_GET['fecha_inicio'])) {
    $fi = $conn->real_escape_string($_GET['fecha_inicio']);
    $where .= " AND fecha >= '$fi'";
}
if (!empty($_GET['fecha_fin'])) {
    $ff = $conn->real_escape_string($_GET['fecha_fin']);
    $where .= " AND fecha <= '$ff'";
}
if (!empty($_GET['unidad_negocio'])) {
    $un = (int)$_GET['unidad_negocio'];
    $where .= " AND unidad_negocio_id=$un";
}

$mapa_orden_sql = [
  'folio' => 'folio',
  'fecha' => 'fecha',
  'cliente' => 'cliente',
  'servicio' => 'servicio',
  'prenda' => 'prenda',
  'cantidad' => 'cantidad',
  'monto' => 'monto',
  'estatus' => 'estatus'
];
$orden_key = $_GET['orden'] ?? 'id';
$columna_orden = $mapa_orden_sql[$orden_key] ?? 'id';
$direccion = strtoupper($_GET['dir'] ?? 'ASC');
$direccion = ($direccion === 'DESC') ? 'DESC':'ASC';

$query = "SELECT * FROM ordenes_lavanderia $where ORDER BY $columna_orden $direccion LIMIT $registros_por_pagina OFFSET $offset";
$ordenes = $conn->query($query);
$total = $conn->query("SELECT COUNT(*) AS total FROM ordenes_lavanderia $where")->fetch_assoc()['total'];
$total_paginas = ceil($total / $registros_por_pagina);

$suma_monto = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lavandería - PocketTrack</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">🧺 Módulo Lavandería</h2>
  <?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>
  <form class="row g-2 mb-3">
    <div class="col-md-2"><input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($_GET['fecha_inicio']??'') ?>"></div>
    <div class="col-md-2"><input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($_GET['fecha_fin']??'') ?>"></div>
    <div class="col-md-2"><input type="text" name="cliente" class="form-control" placeholder="Cliente" value="<?= htmlspecialchars($_GET['cliente']??'') ?>"></div>
    <div class="col-md-2"><input type="text" name="servicio" class="form-control" placeholder="Servicio" value="<?= htmlspecialchars($_GET['servicio']??'') ?>"></div>
    <div class="col-md-2">
      <select name="estatus" class="form-select">
        <option value="">Estatus</option>
        <?php $es = ['Pendiente','En proceso','Terminado','Cancelado'];
        foreach($es as $e){$sel = ($_GET['estatus']??'')==$e?'selected':'';echo "<option $sel>$e</option>";} ?>
      </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
  </form>
  <?php if(in_array($_SESSION['user_role']??'', ['superadmin','administrador','gerente','admin'])): ?>
  <div class="mb-3">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalIngreso">Registrar Pedido</button>
    <a class="btn btn-dark" id="btnExportarCSV" href="#">Exportar CSV</a>
    <a class="btn btn-danger" id="btnExportarPDF" href="#">Exportar PDF</a>
    <a class="btn btn-info" href="kpis_lavanderia.php">Ver KPIs</a>
  </div>
  <?php endif; ?>
  <div class="table-responsive">
  <table class="table table-sm table-striped">
    <thead>
      <tr>
<?php
foreach($mapa_orden_sql as $col => $label){
  $params = $_GET; $params['orden']=$col; $params['dir']=($orden_key==$col && $direccion=='ASC')?'DESC':'ASC';
  $icon = ($orden_key==$col)?($direccion=='DESC'?'↓':'↑'):'';
  echo "<th class='col-$col'><a href='?".http_build_query($params)."' class='text-decoration-none'>$col $icon</a></th>";
}
?>
      </tr>
    </thead>
    <tbody>
<?php if($ordenes->num_rows==0): ?>
<tr><td colspan="8" class="text-center">Sin resultados</td></tr>
<?php endif; ?>
<?php while($o=$ordenes->fetch_assoc()): $suma_monto += (float)$o['monto']; ?>
<tr>
  <td><?= htmlspecialchars($o['folio']) ?></td>
  <td><?= htmlspecialchars($o['fecha']) ?></td>
  <td><?= htmlspecialchars($o['cliente']) ?></td>
  <td><?= htmlspecialchars($o['servicio']) ?></td>
  <td><?= htmlspecialchars($o['prenda']) ?></td>
  <td><?= (int)$o['cantidad'] ?></td>
  <td>$<?= number_format($o['monto'],2) ?></td>
  <td><?= htmlspecialchars($o['estatus']) ?></td>
</tr>
<?php endwhile; ?>
    </tbody>
    <tfoot><tr>
      <td colspan="6" class="text-end"><strong>Total:</strong></td>
      <td colspan="2"><strong id="totalMonto">$<?= number_format($suma_monto,2) ?></strong></td>
    </tr></tfoot>
  </table>
  </div>
  <nav>
    <ul class="pagination">
<?php for($i=1;$i<=$total_paginas;$i++): ?>
      <li class="page-item <?= $i==$pagina_actual?'active':'' ?>">
        <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['pagina'=>$i])) ?>"><?= $i ?></a>
      </li>
<?php endfor; ?>
    </ul>
  </nav>
</div>

<div class="modal fade" id="modalIngreso" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <form class="needs-validation" novalidate method="POST" action="procesar_lavanderia.php">
    <div class="modal-header bg-success text-white"><h5 class="modal-title">Nuevo Pedido</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="origen" value="minipanel_lavanderia.php">
      <div class="mb-2"><label class="form-label">Fecha</label><input type="date" name="fecha" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Cliente</label><input type="text" name="cliente" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Servicio</label><input type="text" name="servicio" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Prenda</label><input type="text" name="prenda" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Cantidad</label><input type="number" name="cantidad" class="form-control" value="1" required></div>
      <div class="mb-2"><label class="form-label">Monto</label><input type="number" step="0.01" name="monto" class="form-control" required></div>
      <div class="mb-2"><label class="form-label">Unidad de Negocio</label>
        <select name="unidad_negocio_id" class="form-select" required>
<?php
$unidades=$conn->query("SELECT id,nombre FROM unidades_negocio ORDER BY nombre");
while($u=$unidades->fetch_assoc()){echo "<option value='{$u['id']}'>{$u['nombre']}</option>";}
?>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
      <button class="btn btn-primary" type="submit">Guardar</button>
    </div>
    </form>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#btnExportarCSV').on('click',function(e){e.preventDefault();window.open('exportar_lavanderia.php?'+new URLSearchParams(window.location.search).toString(),'_blank');});
$('#btnExportarPDF').on('click',function(e){e.preventDefault();window.open('exportar_lavanderia_pdf.php?'+new URLSearchParams(window.location.search).toString(),'_blank');});
</script>
</body>
</html>
