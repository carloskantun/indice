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
<form action="procesar_servicio_cliente.php" method="POST" enctype="multipart/form-data">
    <!-- Alojamiento -->
    <div class="mb-3">
        <label for="alojamiento_id" class="form-label">Alojamiento / Departamento</label>
        <select name="alojamiento_id" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php
            $alojamientos = $conn->query("SELECT id, nombre FROM alojamientos");
            while ($alojamiento = $alojamientos->fetch_assoc()):
            ?>
                <option value="<?php echo $alojamiento['id']; ?>"><?php echo htmlspecialchars($alojamiento['nombre']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Fecha de la Tarea -->
    <div class="mb-3">
        <label for="fecha_reporte" class="form-label">Fecha de la Tarea</label>
        <input type="date" name="fecha_reporte" class="form-control" required>
    </div>

    <!-- Descripcin -->
    <div class="mb-3">
        <label for="descripcion_reporte" class="form-label">Descripción de la Tarea</label>
        <textarea name="descripcion_reporte" class="form-control" required></textarea>
    </div>

    <!-- Estatus -->
    <div class="mb-3">
        <label for="estatus" class="form-label">Estatus</label>
        <select name="estatus" class="form-control" required>
            <option value="Pendiente" selected>Pendiente</option>
            <option value="En proceso">En proceso</option>
            <option value="Terminado">Terminado</option>
            <option value="Cancelado">Cancelado</option>
        </select>
    </div>

    <!-- Subir foto -->
    <div class="mb-3">
        <label for="foto" class="form-label">Subir foto de la Tarea</label>
        <input type="file" name="foto" class="form-control">
    </div>

    <!-- Usuario solicitante -->
    <div class="mb-3">
        <label for="usuario_solicitante_id" class="form-label">Usuario solicitante</label>
        <select name="usuario_solicitante_id" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php
            $usuarios = $conn->query("SELECT id, nombre FROM usuarios");
            while ($usuario = $usuarios->fetch_assoc()):
            ?>
                <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Unidad de negocio -->
    <div class="mb-3">
        <label for="unidad_negocio_id" class="form-label">Unidad de Negocio</label>
        <select name="unidad_negocio_id" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php
            $unidades = $conn->query("SELECT id, nombre FROM unidades_negocio");
            while ($unidad = $unidades->fetch_assoc()):
            ?>
                <option value="<?php echo $unidad['id']; ?>"><?php echo htmlspecialchars($unidad['nombre']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
<!-- Notas (opcional) -->
<div class="mb-3">
    <label for="notas" class="form-label">Notas adicionales</label>
    <textarea name="notas" class="form-control"></textarea>
</div>

<!-- Fecha de vencimiento -->
<div class="mb-3">
    <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento</label>
    <input type="date" name="fecha_vencimiento" class="form-control">
</div>

<!-- Usuario delegado -->
<div class="mb-3">
    <label for="usuario_delegado_id" class="form-label">Delegar a usuario</label>
    <select name="usuario_delegado_id" class="form-control">
        <option value="">(Opcional)</option>
        <?php
        $usuarios = $conn->query("SELECT id, nombre FROM usuarios");
        while ($usuario = $usuarios->fetch_assoc()):
        ?>
            <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
        <?php endwhile; ?>
    </select>
</div>

    <!-- Botn -->
    <button type="submit" class="btn btn-success w-100">Guardar Tarea</button>
</form>
<?php
    exit;
}

// Vista completa (opcional o redirigir)
echo "<p class='text-danger'>Este archivo est pensado para cargarse como modal.</p>";
?>
