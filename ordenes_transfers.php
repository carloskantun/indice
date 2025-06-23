<?php
include 'auth.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';

if (isset($_GET['modal'])) {
?>
<form action="procesar_transfers.php" method="POST">
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
        <label class="form-label">No. Reserva</label>
        <input type="text" name="numero_reserva" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Pasajeros</label>
        <input type="number" name="pasajeros" class="form-control" required>
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
    <button type="submit" class="btn btn-success w-100">Guardar</button>
</form>
<?php
    exit;
}

echo "<p class='text-danger'>Este archivo se carga como modal.</p>";
?>
