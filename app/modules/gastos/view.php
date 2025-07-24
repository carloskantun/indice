<?php
session_start();
include 'auth.php';
include 'conexion.php';
require_once __DIR__.'/controller.php';
require_once __DIR__.'/../../components/ModalBase.php';

$ajax = isset($_GET['ajax']);

$controller = new GastosController($conn);

$filtros = [
    'proveedor'   => $_GET['proveedor']   ?? '',
    'unidad'      => $_GET['unidad']      ?? '',
    'fecha_inicio'=> $_GET['fecha_inicio']?? '',
    'fecha_fin'   => $_GET['fecha_fin']   ?? '',
    'estatus'     => $_GET['estatus']     ?? '',
    'origen'      => $_GET['origen']      ?? ''
];

$orden = $_GET['orden'] ?? 'fecha_pago';
$dir   = $_GET['dir']   ?? 'DESC';
$gastos = $controller->listar($filtros, $orden, $dir);
$kpis   = $controller->getKpis();

class IncludeModal extends ModalBase {
    private string $id;
    private string $file;
    public function __construct(string $id, string $file){
        $this->id=$id; $this->file=$file;
    }
    public function show(): void {
        echo '<div class="modal fade" id="'.$this->id.'" tabindex="-1"><div class="modal-dialog"><div class="modal-content">';
        include $this->file;
        echo '</div></div></div>';
    }
}

$modalNuevo  = new IncludeModal('modalGasto', __DIR__.'/modal_gasto.php');
$modalEditar = new IncludeModal('modalEditarGasto', __DIR__.'/modal_editar_gasto.php');
?>
<?php if(!$ajax): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<?php endif; ?>
<div class="container py-4" id="gastos-content">
    <div class="mb-3 d-flex justify-content-between">
        <h1 class="h4">Gastos</h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalGasto">Nuevo gasto</button>
    </div>
    <div class="mb-3">
        <strong>Mes:</strong> $<?= number_format($kpis['mes'],2) ?> |
        <strong>Año:</strong> $<?= number_format($kpis['anio'],2) ?>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Proveedor</th>
                <th>Monto</th>
                <th>Fecha pago</th>
                <th>Estatus</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($gastos as $g): ?>
            <tr>
                <td><?= htmlspecialchars($g['folio']) ?></td>
                <td><?= htmlspecialchars($g['proveedor']) ?></td>
                <td>$<?= number_format($g['monto'],2) ?></td>
                <td><?= htmlspecialchars($g['fecha_pago']) ?></td>
                <td><?= htmlspecialchars($g['estatus']) ?></td>
                <td><button class="btn btn-sm btn-outline-warning edit-btn" data-id="<?= $g['id'] ?>" data-bs-toggle="modal" data-bs-target="#modalEditarGasto">Editar</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$modalNuevo->show();
$modalEditar->show();
?>
<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch('app/modules/gastos/modal_editar_gasto.php?id=' + id + '&ajax=1')
            .then(r => r.text())
            .then(html => {
                document.querySelector('#modalEditarGasto .modal-content').innerHTML = html;
            });
    });
});
</script>
<?php if(!$ajax): ?>
</body>
</html>
<?php endif; ?>
