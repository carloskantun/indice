<?php
session_start();
include 'auth.php';
include 'conexion.php';

// 📌 Si es modal, solo carga el formulario
if (isset($_GET['modal'])) {
?>
    <form action="procesar_alojamiento.php" method="POST">
        <div class="mb-3">
            <label>Nombre del Alojamiento</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Dirección</label>
            <textarea name="direccion" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Unidad de Negocio</label>
            <select name="unidad_negocio_id" class="form-control">
                <option value="">Seleccionar...</option>
                <?php
                $unidades = $conn->query("SELECT id, nombre FROM unidades_negocio");
                while ($unidad = $unidades->fetch_assoc()):
                ?>
                    <option value="<?php echo $unidad['id']; ?>"><?php echo htmlspecialchars($unidad['nombre']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Notas</label>
            <textarea name="notas" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-warning w-100">Guardar</button>
    </form>
<?php
    exit;
}

// 📌 Vista completa si no es modal
include 'header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Lista de Alojamientos</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Unidad de Negocio</th>
                <th>Notas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $alojamientos = $conn->query("
                SELECT a.id, a.nombre, a.direccion, a.notas, u.nombre AS unidad
                FROM alojamientos a
                LEFT JOIN unidades_negocio u ON a.unidad_negocio_id = u.id
            ");
            while ($a = $alojamientos->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($a['direccion']); ?></td>
                    <td><?php echo htmlspecialchars($a['unidad']); ?></td>
                    <td><?php echo htmlspecialchars($a['notas']); ?></td>
                    <td>
                        <a href="editar_alojamiento.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="eliminar_alojamiento.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este alojamiento?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
