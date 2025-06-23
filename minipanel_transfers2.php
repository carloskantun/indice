<?php
session_start();
include 'auth.php';
include 'conexion.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$registros_por_pagina = 500;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

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

$query = "SELECT * FROM ordenes_transfers $where ORDER BY id DESC LIMIT $registros_por_pagina OFFSET $offset";
$ordenes = $conn->query($query);
$total = $conn->query("SELECT COUNT(*) AS total FROM ordenes_transfers $where")->fetch_assoc()['total'];
$total_paginas = ceil($total / $registros_por_pagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<div class="container-fluid mt-4">
  <?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>
  <div class="d-flex justify-content-between mb-3">
    <h4>Transfers</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalIngresarOrden">Nuevo</button>
  </div>
  <form class="row g-2 mb-3" method="GET">
    <div class="col-md-2">
      <select name="tipo" class="form-select">
        <option value="">Tipo</option>
        <option value="Llegada" <?= ($_GET['tipo']??'')=='Llegada'?'selected':'' ?>>Llegada</option>
        <option value="Salida" <?= ($_GET['tipo']??'')=='Salida'?'selected':'' ?>>Salida</option>
        <option value="Roundtrip" <?= ($_GET['tipo']??'')=='Roundtrip'?'selected':'' ?>>Roundtrip</option>
      </select>
    </div>
    <div class="col-md-2">
      <input type="text" name="agencia" class="form-control" placeholder="Agencia" value="<?= htmlspecialchars($_GET['agencia']??'') ?>">
    </div>
    <div class="col-md-2">
      <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($_GET['fecha_inicio']??'') ?>">
    </div>
    <div class="col-md-2">
      <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($_GET['fecha_fin']??'') ?>">
    </div>
    <div class="col-md-2">
      <select name="operador" class="form-select">
        <option value="">Operador</option>
        <?php
        $us = $conn->query("SELECT id,nombre FROM usuarios");
        while($u=$us->fetch_assoc()): ?>
          <option value="<?= $u['id'] ?>" <?= isset($_GET['operador']) && $_GET['operador']==$u['id']?'selected':'' ?>><?= htmlspecialchars($u['nombre']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2 text-end">
      <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped table-bordered" id="tabla">
      <thead class="table-dark">
        <tr>
          <th class="col-folio">Folio</th>
          <th class="col-tipo">Tipo</th>
          <th class="col-fecha">Fecha</th>
          <th class="col-pickup">Pickup</th>
          <th class="col-hotel">Hotel</th>
          <th class="col-pasajeros">Pasajeros</th>
          <th class="col-numero_reserva">No. Reserva</th>
          <th class="col-vehiculo">Vehículo</th>
          <th class="col-conductor">Conductor</th>
          <th class="col-agencia">Agencia</th>
          <th class="col-estatus">Estatus</th>
          <th class="col-pdf">PDF</th>
        </tr>
      </thead>
      <tbody>
        <?php while($o = $ordenes->fetch_assoc()): ?>
        <tr>
          <td class="col-folio"><?= htmlspecialchars($o['folio']) ?></td>
          <td class="col-tipo"><?= htmlspecialchars($o['tipo']) ?></td>
          <td class="col-fecha"><?= htmlspecialchars($o['fecha']) ?></td>
          <td class="col-pickup"><?= htmlspecialchars($o['pickup']) ?></td>
          <td class="col-hotel"><?= htmlspecialchars($o['hotel']) ?></td>
          <td class="col-pasajeros"><?= htmlspecialchars($o['pasajeros']) ?></td>
          <td class="col-numero_reserva"><?= htmlspecialchars($o['numero_reserva']) ?></td>
          <td class="col-vehiculo"><?= htmlspecialchars($o['vehiculo']) ?></td>
          <td class="col-conductor"><?= htmlspecialchars($o['conductor']) ?></td>
          <td class="col-agencia"><?= htmlspecialchars($o['agencia']) ?></td>
          <td class="col-estatus">
            <select class="form-select form-select-sm estatus-select" data-id="<?= $o['folio'] ?>">
              <option value="Pendiente" <?= $o['estatus']=='Pendiente'?'selected':'' ?>>Pendiente</option>
              <option value="En proceso" <?= $o['estatus']=='En proceso'?'selected':'' ?>>En proceso</option>
              <option value="Terminado" <?= $o['estatus']=='Terminado'?'selected':'' ?>>Terminado</option>
              <option value="Cancelado" <?= $o['estatus']=='Cancelado'?'selected':'' ?>>Cancelado</option>
            </select>
          </td>
          <td class="col-pdf"><a href="generar_pdf_transfers.php?folio=<?= $o['folio'] ?>" target="_blank" class="btn btn-sm btn-outline-dark">PDF</a></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <nav>
    <ul class="pagination">
      <?php for($i=1;$i<=$total_paginas;$i++): ?>
        <li class="page-item <?= $i==$pagina_actual? 'active':'' ?>">
          <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>

<div class="modal fade" id="modalIngresarOrden" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo Transfer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="contenidoOrden">
        <p class="text-center">Cargando...</p>
      </div>
    </div>
  </div>
</div>

<?php include 'script_modales_transfers.js'; ?>
<script>
$(document).on('change','.estatus-select',function(){
   var est=$(this).val();
   var id=$(this).data('id');
   $.post('actualizar_estatus_transfer.php',{orden_id:id,estatus:est});
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
