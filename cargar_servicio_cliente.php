<?php
session_start();
include 'auth.php';
include 'conexion.php';

$registros_por_pagina = 100;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$query = "SELECT folio, descripcion_reporte, fecha_reporte, estatus, nivel, quien_realizo_id, costo_final,
                 (SELECT nombre FROM alojamientos WHERE id = alojamiento_id) AS alojamiento,
                 (SELECT nombre FROM usuarios WHERE id = usuario_solicitante_id) AS usuario,
                 (SELECT nombre FROM unidades_negocio WHERE id = unidad_negocio_id) AS unidad_negocio
          FROM ordenes_servicio_cliente
          ORDER BY fecha_reporte DESC
          LIMIT $registros_por_pagina OFFSET $offset";

$ordenes = $conn->query($query);

while ($orden = $ordenes->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($orden['folio']); ?></td>
        <td><?php echo htmlspecialchars($orden['alojamiento']); ?></td>
        <td><?php echo htmlspecialchars($orden['descripcion_reporte']); ?></td>
        <td><?php echo htmlspecialchars($orden['fecha_reporte']); ?></td>
        <td><?php echo htmlspecialchars($orden['usuario']); ?></td>
        <td><?php echo htmlspecialchars($orden['unidad_negocio']); ?></td>
        <td><?php echo htmlspecialchars($orden['estatus']); ?></td>
        <td><?php echo htmlspecialchars($orden['quien_realizo_id']); ?></td>
        <td><?php echo htmlspecialchars($orden['nivel']); ?></td>
        <td>
            <?php echo isset($orden['costo_final']) ? '$' . number_format($orden['costo_final'], 2) : ''; ?>
        </td>
    </tr>
<?php endwhile; ?>
