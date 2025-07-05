<?php
include 'conexion.php';

$id = intval($_GET['id'] ?? 0);
$res = $conn->query("SELECT archivo_comprobante FROM abonos_gastos WHERE gasto_id = $id AND archivo_comprobante IS NOT NULL ORDER BY id ASC");
$comprobantes = [];
while ($row = $res->fetch_assoc()) {
    $comprobantes[] = $row['archivo_comprobante'];
}
if (empty($comprobantes)) {
    echo '<div class="p-4">No hay comprobantes disponibles.</div>';
    exit;
}
?>
<div class="modal-header bg-secondary text-white">
    <h5 class="modal-title">Comprobantes</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body p-0">
    <div id="carouselComprobantes" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($comprobantes as $i => $ruta): 
                $is_pdf = preg_match('/\.pdf$/i', $ruta);
            ?>
            <div class="carousel-item <?php if($i === 0) echo 'active'; ?>">
                <?php if ($is_pdf): ?>
                    <iframe src="<?php echo htmlspecialchars($ruta); ?>" class="w-100" style="height:500px;border:none;"></iframe>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($ruta); ?>" class="d-block w-100" style="max-height:500px;object-fit:contain;">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselComprobantes" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselComprobantes" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>
