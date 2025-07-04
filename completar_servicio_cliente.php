<?php
session_start();
include 'conexion.php';

$folio = $_GET['folio'] ?? null;

if (!$folio) {
    echo "<p class='text-danger'>Folio no especificado.</p>";
    exit;
}

// Obtener toda la info de la orden para precargar el formulario
$query = $conn->prepare("SELECT * FROM ordenes_servicio_cliente WHERE folio = ?");
$query->bind_param("s", $folio);
$query->execute();
$result = $query->get_result();
$orden = $result->fetch_assoc();

if (!$orden) {
    echo "<p class='text-danger'>Orden no encontrada.</p>";
    exit;
}
?>
<form id="formCompletarOrden" enctype="multipart/form-data">
    <input type="hidden" name="folio" value="<?php echo htmlspecialchars($folio); ?>">

    <div class="mb-3">
        <label for="fecha_completado" class="form-label">Fecha de Ejecucin</label>
        <input type="date" class="form-control" name="fecha_completado" value="<?php echo htmlspecialchars($orden['fecha_completado'] ?? ''); ?>" required>
    </div>

    <div class="mb-3">
        <label for="detalle_completado" class="form-label">Detalle de la Tarea</label>
        <textarea class="form-control" name="detalle_completado" rows="3" required><?php echo htmlspecialchars($orden['detalle_completado'] ?? ''); ?></textarea>
    </div>

    <div class="mb-3">
        <label for="foto_completado" class="form-label">Foto Final (opcional)</label>
        <input type="file" class="form-control" name="foto_completado" accept="image/*">
        <?php if (!empty($orden['foto_completado'])): ?>
            <p class="mt-2">Foto actual:</p>
            <img src="<?php echo $orden['foto_completado']; ?>" style="max-width: 100px;">
        <?php endif; ?>
    </div>
    
<div class="mb-3">
    <label for="costo_final" class="form-label">Costo Final (opcional)</label>
    <input type="number" class="form-control" name="costo_final" step="0.01" value="<?php echo htmlspecialchars($orden['costo_final'] ?? ''); ?>">
</div>

    <div class="text-end">
        <button type="submit" class="btn btn-success">Guardar y Completar</button>
    </div>
</form>

<script>
document.getElementById("formCompletarOrden").addEventListener("submit", function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch("guardar_completado_servicio_cliente.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        if (res.trim() === "ok") {
            alert("Orden completada exitosamente.");
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCompletarOrden'));
            modal.hide();
            location.reload();
        } else {
            alert("Error: " + res);
        }
    })
    .catch(() => {
        alert("Ocurri un error al enviar los datos.");
    });
});
</script>
