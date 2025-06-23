<?php
include 'auth.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Transfer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">📋 Nuevo Transfer</h2>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_GET['msg']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <form action="procesar_transfers.php" method="POST">
    <input type="hidden" name="origen" value="registrar">

    <div class="mb-3">
      <label class="form-label">Tipo de Servicio</label>
      <select name="tipo_servicio" class="form-control" required>
        <option value="Llegada">Llegada</option>
        <option value="Salida">Salida</option>
        <option value="Roundtrip">Roundtrip</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Fecha</label>
      <input type="date" name="fecha" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Hora Pickup</label>
      <input type="time" name="pickup" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Hotel</label>
      <input type="text" name="hotel" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Nombre de Pasajeros</label>
      <input type="text" name="pasajeros" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Número de Pasajeros</label>
      <input type="number" name="numero_reserva" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Vehículo</label>
      <input type="text" name="vehiculo" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Conductor</label>
      <input type="text" name="conductor" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Agencia</label>
      <input type="text" name="agencia" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Estatus</label>
      <select name="estatus" class="form-control">
        <option value="Pendiente">Pendiente</option>
        <option value="En proceso">En proceso</option>
        <option value="Terminado">Terminado</option>
        <option value="Cancelado">Cancelado</option>
      </select>
    </div>

    <button type="submit" class="btn btn-success w-100">Guardar Transfer</button>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
