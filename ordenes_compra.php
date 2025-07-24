<?php
session_start();
include 'auth.php';
include 'conexion.php'; // Conexión centralizada a la base de datos

// 📌 Si la petición viene del modal (`ordenes_compra.php?modal=1`), solo devuelve el formulario
if (isset($_GET['modal'])) {
    require_once 'app/components/FormularioBase.php';

    $optsProveedores = [];
    $resProv = $conn->query("SELECT id, nombre FROM proveedores");
    while ($row = $resProv->fetch_assoc()) {
        $optsProveedores[$row['id']] = $row['nombre'];
    }

    $optsUsuarios = [];
    $resUsr = $conn->query("SELECT id, nombre FROM usuarios");
    while ($row = $resUsr->fetch_assoc()) {
        $optsUsuarios[$row['id']] = $row['nombre'];
    }

    $optsUnidades = [];
    $resUni = $conn->query("SELECT id, nombre FROM unidades_negocio");
    while ($row = $resUni->fetch_assoc()) {
        $optsUnidades[$row['id']] = $row['nombre'];
    }

    $campos = [
        ['type' => 'select', 'name' => 'proveedor_id', 'label' => 'Proveedor', 'options' => $optsProveedores, 'required' => true],
        ['type' => 'number', 'name' => 'monto', 'label' => 'Monto del Pago', 'required' => true],
        ['type' => 'date', 'name' => 'vencimiento_pago', 'label' => 'Fecha de Vencimiento', 'required' => true],
        ['type' => 'textarea', 'name' => 'concepto_pago', 'label' => 'Concepto de Pago', 'required' => true],
        [
            'type'    => 'select',
            'name'    => 'tipo_pago',
            'label'   => 'Tipo de Pago',
            'options' => [
                'Recurrente Mensual'  => 'Recurrente Mensual',
                'Recurrente Semanal'  => 'Recurrente Semanal',
                'Recurrente Quincenal' => 'Recurrente Quincenal',
                'Pago Único'          => 'Pago Único',
                'Nota de Crédito'     => 'Nota de Crédito'
            ],
            'required' => true
        ],
        [
            'type'    => 'select',
            'name'    => 'genera_factura',
            'label'   => 'Genera Factura',
            'options' => ['No' => 'No', 'Sí' => 'Sí']
        ],
        ['type' => 'select', 'name' => 'usuario_solicitante_id', 'label' => 'Usuario Solicitante', 'options' => $optsUsuarios, 'required' => true],
        ['type' => 'select', 'name' => 'unidad_negocio_id', 'label' => 'Unidad de Negocio', 'options' => $optsUnidades, 'required' => true],
    ];

    echo '<form action="procesar_orden.php" method="POST">';
    echo FormularioBase::render($campos);
    echo '<button type="submit" class="btn btn-success w-100">Guardar Orden</button>';
    echo '</form>';

    exit; // Evita que se cargue toda la página si se usa en un modal
}

// 📌 Si no es un modal, cargar la vista completa con la lista de órdenes de compra
include 'header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Lista de Órdenes de Compra</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Proveedor</th>
                <th>Monto</th>
                <th>Vencimiento</th>
                <th>Concepto</th>
                <th>Tipo de Pago</th>
                <th>Usuario</th>
                <th>Unidad de Negocio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $ordenes = $conn->query("SELECT folio, monto, vencimiento_pago, concepto_pago, tipo_pago,
                                    (SELECT nombre FROM proveedores WHERE id = proveedor_id) AS proveedor, 
                                    (SELECT nombre FROM usuarios WHERE id = usuario_solicitante_id) AS usuario,
                                    (SELECT nombre FROM unidades_negocio WHERE id = unidad_negocio_id) AS unidad_negocio
                              FROM ordenes_compra");
            while ($orden = $ordenes->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($orden['folio']); ?></td>
                    <td><?php echo htmlspecialchars($orden['proveedor']); ?></td>
                    <td>$<?php echo number_format($orden['monto'], 2); ?></td>
                    <td><?php echo htmlspecialchars($orden['vencimiento_pago']); ?></td>
                    <td><?php echo htmlspecialchars($orden['concepto_pago']); ?></td>
                    <td><?php echo htmlspecialchars($orden['tipo_pago']); ?></td>
                    <td><?php echo htmlspecialchars($orden['usuario']); ?></td>
                    <td><?php echo htmlspecialchars($orden['unidad_negocio']); ?></td>
                    <td>
                        <a href="editar_orden.php?id=<?php echo $orden['folio']; ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="eliminar_orden.php?id=<?php echo $orden['folio']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar esta orden?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
