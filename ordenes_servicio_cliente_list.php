<?php
session_start();
include 'auth.php';
include 'conexion.php';
include 'header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Lista de Órdenes de Servicio al Cliente</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Alojamiento</th>
                <th>Descripción</th>
                <th>Fecha</th>
                <th>Vencimiento</th>
                <th>Usuario</th>
                <th>Unidad de Negocio</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $ordenes = $conn->query("SELECT folio, descripcion_reporte, fecha_reporte, fecha_vencimiento, estatus,
                                     (SELECT nombre FROM alojamientos WHERE id = alojamiento_id) AS alojamiento,
                                     (SELECT nombre FROM usuarios WHERE id = usuario_solicitante_id) AS usuario,
                                     (SELECT nombre FROM unidades_negocio WHERE id = unidad_negocio_id) AS unidad_negocio
                               FROM ordenes_servicio_cliente");
            while ($orden = $ordenes->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($orden['folio']); ?></td>
                    <td><?php echo htmlspecialchars($orden['alojamiento']); ?></td>
                    <td><?php echo htmlspecialchars($orden['descripcion_reporte']); ?></td>
                    <td><?php echo htmlspecialchars($orden['fecha_reporte']); ?></td>
                    <td><?php echo htmlspecialchars($orden['fecha_vencimiento']); ?></td>
                    <td><?php echo htmlspecialchars($orden['usuario']); ?></td>
                    <td><?php echo htmlspecialchars($orden['unidad_negocio']); ?></td>
                    <td><?php echo htmlspecialchars($orden['estatus']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>

