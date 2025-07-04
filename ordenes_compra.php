<?php
session_start();
include 'auth.php';
include 'conexion.php'; // Conexión centralizada a la base de datos

// 📌 Si la petición viene del modal (`ordenes_compra.php?modal=1`), solo devuelve el formulario
if (isset($_GET['modal'])) {
?>
    <form action="procesar_orden.php" method="POST">
        <!-- Proveedor -->
        <div class="mb-3">
            <label for="proveedor_id" class="form-label">Proveedor</label>
            <select name="proveedor_id" class="form-control" required>
                <option value="">Seleccionar</option>
                <?php
                $proveedores = $conn->query("SELECT id, nombre FROM proveedores");
                while ($proveedor = $proveedores->fetch_assoc()):
                ?>
                    <option value="<?php echo $proveedor['id']; ?>"><?php echo htmlspecialchars($proveedor['nombre']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <!-- Monto del Pago -->
        <div class="mb-3">
            <label for="monto" class="form-label">Monto del Pago</label>
            <input type="number" name="monto" class="form-control" required>
        </div>
        <!-- Vencimiento del Pago -->
        <div class="mb-3">
            <label for="vencimiento_pago" class="form-label">Fecha de Vencimiento</label>
            <input type="date" name="vencimiento_pago" class="form-control" required>
        </div>
        <!-- Concepto de Pago -->
        <div class="mb-3">
            <label for="concepto_pago" class="form-label">Concepto de Pago</label>
            <textarea name="concepto_pago" class="form-control" required></textarea>
        </div>
        <!-- Tipo de Pago -->
        <div class="mb-3">
            <label for="tipo_pago" class="form-label">Tipo de Pago</label>
            <select name="tipo_pago" class="form-control" required>
                <option value="Recurrente Mensual">Recurrente Mensual</option>
                <option value="Recurrente Semanal">Recurrente Semanal</option>
                <option value="Recurrente Quincenal">Recurrente Quincenal</option>
                <option value="Pago Único">Pago Único</option>
                <option value="Nota de Crédito">Nota de Crédito</option>
            </select>
        </div>
        <!-- Genera Factura -->
        <div class="mb-3">
            <label for="genera_factura" class="form-label">Genera Factura</label>
            <select name="genera_factura" class="form-control">
                <option value="No">No</option>
                <option value="Sí">Sí</option>
            </select>
        </div>
        <!-- Usuario Solicitante -->
        <div class="mb-3">
            <label for="usuario_solicitante_id" class="form-label">Usuario Solicitante</label>
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
        <!-- Unidad de Negocio -->
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
        <!-- Botón de Enviar -->
        <button type="submit" class="btn btn-success w-100">Guardar Orden</button>
    </form>
<?php
    exit; // Evita que se cargue toda la página si se usa en un modal
}

// 📌 Si no es un modal, cargar la vista completa con la lista de órdenes de compra
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
                <th>Fecha Reporte</th>
                <th>Estatus</th>
                <th>Usuario</th>
                <th>Unidad de Negocio</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $ordenes = $conn->query("SELECT folio, fecha_reporte, descripcion_reporte, estatus,
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
                    <td><?php echo htmlspecialchars($orden['estatus']); ?></td>
                    <td><?php echo htmlspecialchars($orden['usuario']); ?></td>
                    <td><?php echo htmlspecialchars($orden['unidad_negocio']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
