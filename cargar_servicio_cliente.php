<?php
session_start();
include 'auth.php';
include 'conexion.php';

$registros_por_pagina = 100;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$query = "SELECT folio, monto, vencimiento_pago, concepto_pago, tipo_pago, genera_factura, estatus_pago, quien_pago_id, nivel,
                 (SELECT nombre FROM proveedores WHERE id = proveedor_id) AS proveedor, 
                 (SELECT nombre FROM usuarios WHERE id = usuario_solicitante_id) AS usuario,
                 (SELECT nombre FROM unidades_negocio WHERE id = unidad_negocio_id) AS unidad_negocio
          FROM ordenes_compra 
          ORDER BY fecha_creacion DESC 
          LIMIT $registros_por_pagina OFFSET $offset";

$ordenes = $conn->query($query);

while ($orden = $ordenes->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($orden['folio']); ?></td>
        <td><?php echo htmlspecialchars($orden['proveedor']); ?></td>
        <td>$<?php echo number_format($orden['monto'], 2); ?></td>
        <td><?php echo htmlspecialchars($orden['vencimiento_pago']); ?></td>
        <td><?php echo htmlspecialchars($orden['concepto_pago']); ?></td>
        <td><?php echo htmlspecialchars($orden['tipo_pago']); ?></td>
        <td><?php echo htmlspecialchars($orden['genera_factura']); ?></td>
        <td><?php echo htmlspecialchars($orden['usuario']); ?></td>
        <td><?php echo htmlspecialchars($orden['unidad_negocio']); ?></td>
        <td><?php echo htmlspecialchars($orden['estatus_pago']); ?></td>
    </tr>
<?php endwhile; ?>
