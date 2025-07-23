<?php
include '../../conexion.php';

$nota_id = $_GET['id'] ?? null;
if (!$nota_id) {
    echo '<div class="p-3 text-danger">Nota no especificada.</div>';
    exit;
}

$nota_id = intval($nota_id);

$sql = "SELECT 
            a.monto,
            a.comentario,
            a.archivo_comprobante,
            a.fecha,
            u.nombre AS usuario
        FROM abonos_notas_credito a
        LEFT JOIN usuarios u ON a.usuario_id = u.id
        WHERE a.nota_credito_id = $nota_id
        ORDER BY a.fecha ASC";

$res = $conn->query($sql);
?>

<div class="modal-header">
    <h5 class="modal-title">🧾 Abonos de Nota de Crédito</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<?php if ($res && $res->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Comentario</th>
                    <th>Usuario</th>
                    <th>Comprobante</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                    <td>$<?= number_format($row['monto'], 2) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['comentario'])) ?></td>
                    <td><?= htmlspecialchars($row['usuario']) ?></td>
                    <td>
                        <?php if ($row['archivo_comprobante']): ?>
                            <a href="<?= htmlspecialchars($row['archivo_comprobante']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Ver</a>
                        <?php else: ?>
                            <span class="text-muted">Sin archivo</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">Esta nota aún no tiene abonos registrados.</div>
<?php endif; ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
</div>
