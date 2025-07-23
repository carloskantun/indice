<?php
include '../../conexion.php';
session_start();

$id = $_GET['id'] ?? null;
$data = [
    'folio' => '',
    'monto' => '',
    'concepto' => '',
    'fecha_nota' => date('Y-m-d'),
    'unidad_negocio_id' => '',
    'usuario_responsable_id' => ''
];

if ($id) {
    $sql = "SELECT * FROM notas_credito WHERE id = " . intval($id);
    $res = $conn->query($sql);
    if ($res && $res->num_rows) {
        $data = $res->fetch_assoc();
    }
}

// Generar folio si es nuevo
if (!$id) {
    $resF = $conn->query("SELECT MAX(id) + 1 AS next_id FROM notas_credito");
    $next_id = $resF->fetch_assoc()['next_id'] ?? 1;
    $data['folio'] = 'NC-' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
}
?>

<div class="modal-header">
    <h5 class="modal-title"><?= $id ? '✏️ Editar' : '➕ Nueva' ?> Nota de Crédito</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="formNotaCredito" method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Folio</label>
            <input type="text" name="folio" class="form-control" value="<?= htmlspecialchars($data['folio']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" step="0.01" name="monto" class="form-control" value="<?= htmlspecialchars($data['monto']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha_nota" class="form-control" value="<?= htmlspecialchars($data['fecha_nota']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Unidad de negocio</label>
            <select name="unidad_negocio_id" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php
                $unidades = $conn->query("SELECT id, nombre FROM unidades_negocio ORDER BY nombre");
                while ($un = $unidades->fetch_assoc()):
                ?>
                    <option value="<?= $un['id'] ?>" <?= $data['unidad_negocio_id'] == $un['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($un['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Usuario responsable</label>
            <select name="usuario_responsable_id" class="form-select select2" required>
                <option value="">Seleccionar</option>
                <?php
                $usuarios = $conn->query("SELECT id, nombre FROM usuarios ORDER BY nombre");
                while ($u = $usuarios->fetch_assoc()):
                ?>
                    <option value="<?= $u['id'] ?>" <?= $data['usuario_responsable_id'] == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Concepto</label>
            <textarea name="concepto" class="form-control"><?= htmlspecialchars($data['concepto']) ?></textarea>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    </div>
</form>

<script>
$(function(){
    $('.select2').select2({
        width: '100%',
        dropdownParent: $('#modalNota') // 👈 Soluciona el bug visual
    });

    $('#formNotaCredito').on('submit', function(e){
        e.preventDefault();
        const datos = new FormData(this);
        fetch('includes/controllers/guardar_nota_credito.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.text())
        .then(res => {
            if (res.trim() === 'ok') {
                alert('✅ Nota guardada correctamente');
                bootstrap.Modal.getInstance(document.getElementById('modalNota')).hide();
                location.reload();
            } else {
                alert('❌ Error: ' + res);
            }
        })
        .catch(() => alert('❌ Error en la conexión'));
    });
});
</script>

