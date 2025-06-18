<?php
session_start();
include 'auth.php';
include 'conexion.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$registros_por_pagina = 500;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$query = "SELECT ot.*, u.nombre AS usuario, un.nombre AS unidad
          FROM ordenes_transfers ot
          LEFT JOIN usuarios u ON ot.usuario_solicitante_id = u.id
          LEFT JOIN unidades_negocio un ON ot.unidad_negocio_id = un.id
          ORDER BY ot.id DESC
          LIMIT $registros_por_pagina OFFSET $offset";

$ordenes = $conn->query($query);
$total = $conn->query("SELECT COUNT(*) AS total FROM ordenes_transfers")->fetch_assoc()['total'];
$total_paginas = ceil($total / $registros_por_pagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid mt-4">
  <div class="d-flex justify-content-between mb-3">
    <h4>Transfers</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalIngresarOrden">Nuevo</button>
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>Folio</th>
          <th>Tipo</th>
          <th>Fecha</th>
          <th>Pickup</th>
          <th>Hotel</th>
          <th>Pasajeros</th>
          <th>No.</th>
          <th>Vehículo</th>
          <th>Conductor</th>
          <th>Estatus</th>
        </tr>
      </thead>
      <tbody>
        <?php while($o = $ordenes->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($o['folio']); ?></td>
          <td><?php echo htmlspecialchars($o['tipo_servicio']); ?></td>
          <td><?php echo htmlspecialchars($o['fecha_servicio']); ?></td>
          <td><?php echo htmlspecialchars($o['pickup']); ?></td>
          <td><?php echo htmlspecialchars($o['hotel_pickup']); ?></td>
          <td><?php echo nl2br(htmlspecialchars($o['nombre_pasajeros'])); ?></td>
          <td><?php echo htmlspecialchars($o['num_pasajeros']); ?></td>
          <td><?php echo htmlspecialchars($o['vehiculo']); ?></td>
          <td><?php echo htmlspecialchars($o['conductor']); ?></td>
          <td>
            <select class="form-select form-select-sm estatus-select" data-id="<?php echo $o['folio']; ?>">
              <option value="Pendiente" <?php echo $o['estatus']==='Pendiente'? 'selected':''; ?>>Pendiente</option>
              <option value="Realizado" <?php echo $o['estatus']==='Realizado'? 'selected':''; ?>>Realizado</option>
              <option value="Cancelado" <?php echo $o['estatus']==='Cancelado'? 'selected':''; ?>>Cancelado</option>
            </select>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <nav>
    <ul class="pagination">
      <?php for($i=1;$i<=$total_paginas;$i++): ?>
        <li class="page-item <?php echo $i==$pagina_actual? 'active':''; ?>">
          <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).on('change','.estatus-select',function(){
   var est=$(this).val();
   var id=$(this).data('id');
   $.post('actualizar_estatus_transfer.php',{orden_id:id,estatus:est});
});
</script>
</body>
</html>

