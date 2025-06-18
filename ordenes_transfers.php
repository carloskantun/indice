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
        <label for="tipo_servicio" class="form-label">Tipo de Servicio</label>
        <select name="tipo_servicio" class="form-control" required>
            <option value="Llegada">Llegada</option>
            <option value="Salida">Salida</option>
            <option value="Roundtrip">Roundtrip</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="fecha_servicio" class="form-label">Fecha del Servicio</label>
        <input type="date" name="fecha_servicio" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="pickup" class="form-label">Hora Pickup</label>
        <input type="time" name="pickup" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="hotel_pickup" class="form-label">Hotel Pickup</label>
        <textarea name="hotel_pickup" class="form-control" required></textarea>
    </div>
    <div class="mb-3">
        <label for="direccion" class="form-label">Dirección</label>
        <textarea name="direccion" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label for="nombre_pasajeros" class="form-label">Nombre Pasajeros</label>
        <textarea name="nombre_pasajeros" class="form-control" required></textarea>
    </div>
    <div class="mb-3">
        <label for="num_pasajeros" class="form-label">Número Pasajeros</label>
        <input type="number" name="num_pasajeros" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="habitacion" class="form-label">Habitación</label>
        <input type="text" name="habitacion" class="form-control">
    </div>
    <div class="mb-3">
        <label for="vehiculo" class="form-label">Vehículo</label>
        <input type="text" name="vehiculo" class="form-control">
    </div>
    <div class="mb-3">
        <label for="placas" class="form-label">Placas</label>
        <input type="text" name="placas" class="form-control">
    </div>
    <div class="mb-3">
        <label for="numero_economico" class="form-label">Número Económico</label>
        <input type="text" name="numero_economico" class="form-control">
    </div>
    <div class="mb-3">
        <label for="conductor" class="form-label">Conductor</label>
        <input type="text" name="conductor" class="form-control">
    </div>
    <div class="mb-3">
        <label for="agencia" class="form-label">Agencia</label>
        <input type="text" name="agencia" class="form-control">
    </div>
    <div class="mb-3">
        <label for="observaciones" class="form-label">Observaciones</label>
        <textarea name="observaciones" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label for="estatus" class="form-label">Estatus</label>
        <select name="estatus" class="form-control">
            <option value="Pendiente">Pendiente</option>
            <option value="Realizado">Realizado</option>
            <option value="Cancelado">Cancelado</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="usuario_solicitante_id" class="form-label">Usuario solicitante</label>
        <select name="usuario_solicitante_id" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php
            $usuarios = $conn->query("SELECT id, nombre FROM usuarios");
            while ($u = $usuarios->fetch_assoc()):
            ?>
                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nombre']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="unidad_negocio_id" class="form-label">Unidad de Negocio</label>
        <select name="unidad_negocio_id" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php
            $unidades = $conn->query("SELECT id, nombre FROM unidades_negocio");
            while ($un = $unidades->fetch_assoc()):
            ?>
                <option value="<?php echo $un['id']; ?>"><?php echo htmlspecialchars($un['nombre']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-success w-100">Guardar</button>
</form>
<?php
    exit;
}

echo "<p class='text-danger'>Este archivo se carga como modal.</p>";
?>
