<?php
session_start();
include 'auth.php';
include 'conexion.php';
require_once __DIR__.'/controller.php';
require_once __DIR__.'/../../components/ModalBase.php';
require_once __DIR__.'/../../components/FiltrosBase.php';

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

// Opciones para filtros
$optsProv = [];
$res = $conn->query("SELECT id,nombre FROM proveedores ORDER BY nombre");
while($r=$res->fetch_assoc()) $optsProv[$r['id']] = $r['nombre'];

$optsUnidades = [];
$res = $conn->query("SELECT id,nombre FROM unidades_negocio ORDER BY nombre");
while($r=$res->fetch_assoc()) $optsUnidades[$r['id']] = $r['nombre'];

$filtrosForm = [
    [
        'type' => 'select', 'name'=>'proveedor', 'label'=>'Proveedor',
        'options'=>$optsProv, 'value'=>$filtros['proveedor'],
        'class'=>'form-select select2', 'placeholder'=>'Todos', 'col'=>'col-md-2'
    ],
    [
        'type' => 'select', 'name'=>'unidad', 'label'=>'Unidad',
        'options'=>$optsUnidades, 'value'=>$filtros['unidad'],
        'class'=>'form-select select2', 'placeholder'=>'Todas', 'col'=>'col-md-2'
    ],
    [ 'type'=>'date', 'name'=>'fecha_inicio', 'label'=>'Inicio', 'value'=>$filtros['fecha_inicio'], 'col'=>'col-md-2' ],
    [ 'type'=>'date', 'name'=>'fecha_fin', 'label'=>'Fin', 'value'=>$filtros['fecha_fin'], 'col'=>'col-md-2' ],
    [
        'type'=>'select', 'name'=>'estatus', 'label'=>'Estatus',
        'options'=>['Pagado'=>'Pagado','Pago parcial'=>'Pago parcial','Vencido'=>'Vencido','Por pagar'=>'Por pagar'],
        'value'=>$filtros['estatus'], 'class'=>'form-select', 'placeholder'=>'Todos', 'col'=>'col-md-2'
    ],
    [
        'type'=>'select', 'name'=>'origen', 'label'=>'Tipo',
        'options'=>['Directo'=>'Directo','Orden'=>'Orden'],
        'value'=>$filtros['origen'], 'class'=>'form-select', 'placeholder'=>'Todos', 'col'=>'col-md-2'
    ]
];

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
$modalOrden  = new IncludeModal('modalOrden', __DIR__.'/../../modal_orden.php');
$modalEditar = new IncludeModal('modalEditarGasto', __DIR__.'/modal_editar_gasto.php');
$modalAbono  = new IncludeModal('modalAbono', __DIR__.'/modal_abono.php');
$modalComp   = new IncludeModal('modalComprobantes', __DIR__.'/../../modal_comprobantes.php');
$modalKpis   = new IncludeModal('modalKpisGastos', __DIR__.'/../../includes/modals/modal_kpis_gastos.php');
?>
<?php if(!$ajax): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<?php endif; ?>
<div class="container py-4" id="gastos-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 m-0">Gastos</h1>
        <div class="btn-group">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalGasto">Nuevo Gasto</button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalOrden">Nueva Orden de Compra</button>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalKpisGastos">Ver Análisis de KPIs</button>
            <a class="btn btn-outline-secondary" id="linkCsv" target="_blank">CSV</a>
            <a class="btn btn-outline-danger" id="linkPdf" target="_blank">PDF</a>
            <a class="btn btn-outline-dark" href="kpis.php" target="_blank">KPIs Chart.js</a>
        </div>
    </div>

    <form id="formFiltros" class="row g-3 mb-4" method="GET">
        <input type="hidden" name="module" value="gastos">
        <?php echo FiltrosBase::render($filtrosForm); ?>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6 class="mb-1">Gasto del Mes</h6>
                    <h4>$<?= number_format($kpis['mes'],2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h6 class="mb-1">Gasto del Año</h6>
                    <h4>$<?= number_format($kpis['anio'],2) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-striped table-sm align-middle">
        <thead>
            <tr>
                <th><input type="checkbox" id="seleccionar-todos"></th>
                <th>Folio</th>
                <th>Proveedor</th>
                <th>Monto</th>
                <th>Abonado</th>
                <th>Saldo</th>
                <th>Fecha pago</th>
                <th>Unidad</th>
                <th>Tipo</th>
                <th>Uso</th>
                <th>Forma</th>
                <th>Cuenta</th>
                <th>Concepto</th>
                <th>Estatus</th>
                <th>Origen</th>
                <th>Recibo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($gastos as $g): ?>
            <tr>
                <td><input type="checkbox" class="seleccionar-gasto" value="<?= $g['id'] ?>"></td>
                <td><?= htmlspecialchars($g['folio']) ?></td>
                <td><?= htmlspecialchars($g['proveedor']) ?></td>
                <td class="col-monto">$<?= number_format($g['monto'],2) ?></td>
                <td class="col-abonado">$<?= number_format($g['abonado_total'] ?? 0,2) ?></td>
                <td class="col-saldo">$<?= number_format($g['saldo'] ?? 0,2) ?></td>
                <td><?= htmlspecialchars($g['fecha_pago']) ?></td>
                <td><?= htmlspecialchars($g['unidad']) ?></td>
                <td><?= htmlspecialchars($g['tipo_gasto']) ?></td>
                <td><?= htmlspecialchars($g['tipo_compra']) ?></td>
                <td><?= htmlspecialchars($g['medio_pago']) ?></td>
                <td><input type="text" class="form-control form-control-sm campo-update" data-id="<?= $g['id'] ?>" data-campo="cuenta_bancaria" value="<?= htmlspecialchars($g['cuenta_bancaria']) ?>"></td>
                <td><input type="text" class="form-control form-control-sm campo-update" data-id="<?= $g['id'] ?>" data-campo="concepto" value="<?= htmlspecialchars($g['concepto']) ?>"></td>
                <td>
                    <select class="form-select form-select-sm campo-update" data-id="<?= $g['id'] ?>" data-campo="estatus">
                        <?php foreach(['Pagado','Pago parcial','Vencido','Por pagar'] as $op): ?>
                            <option value="<?= $op ?>" <?php if($op==$g['estatus']) echo 'selected'; ?>><?= $op ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><?= htmlspecialchars($g['origen']) ?></td>
                <td>
                    <?php if(!empty($g['archivo_comprobante'])): ?>
                        <button class="btn btn-sm btn-outline-secondary comprobantes-btn" data-id="<?= $g['id'] ?>">Ver</button>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-success abono-btn" data-id="<?= $g['id'] ?>">Pagar</button>
                    <button class="btn btn-sm btn-warning edit-btn" data-id="<?= $g['id'] ?>">Editar</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $g['id'] ?>">Eliminar</button>
                    <a class="btn btn-sm btn-outline-dark" href="app/modules/gastos/generar_pdf_gasto.php?folio=<?= urlencode($g['folio']) ?>" target="_blank">PDF</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php
$modalNuevo->show();
$modalOrden->show();
$modalEditar->show();
$modalAbono->show();
$modalComp->show();
$modalKpis->show();
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    $('.select2').select2({width:'100%'});
});
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

document.querySelectorAll('.abono-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch('app/modules/gastos/modal_abono.php?id=' + id + '&ajax=1')
            .then(r => r.text())
            .then(html => {
                document.querySelector('#modalAbono .modal-content').innerHTML = html;
            });
    });
});

document.querySelectorAll('.comprobantes-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch('modal_comprobantes.php?id=' + id + '&ajax=1')
            .then(r => r.text())
            .then(html => {
                document.querySelector('#modalComprobantes .modal-content').innerHTML = html;
            });
    });
});

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        if(!confirm('¿Eliminar gasto?')) return;
        const id = btn.dataset.id;
        fetch('app/modules/gastos/eliminar_gasto.php?id='+id+'&ajax=1')
            .then(r=>r.text())
            .then(resp => { if(resp.trim()==='ok') window.refreshModule(); else alert(resp); });
    });
});

document.querySelectorAll('.campo-update').forEach(el => {
    el.addEventListener('change', () => {
        const id = el.dataset.id;
        const campo = el.dataset.campo;
        const valor = el.value;
        fetch('app/modules/gastos/actualizar_campo_gasto.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:new URLSearchParams({id, campo, valor})
        }).then(r=>r.text()).then(t=>{ if(t.trim()!=='ok') alert('Error al actualizar'); });
    });
});

window.refreshModule = function(){
    const params = new URLSearchParams(new FormData(document.getElementById('formFiltros'))).toString();
    fetch('index.php?module=gastos&ajax=1&'+params)
        .then(r=>r.text())
        .then(html=>{ document.getElementById('gastos-content').innerHTML = html; });
}

// Export links
(function(){
    const params = new URLSearchParams(new FormData(document.getElementById('formFiltros'))).toString();
    document.getElementById('linkCsv').href = 'app/modules/gastos/exportar_gastos.php?'+params;
    document.getElementById('linkPdf').href = 'app/modules/gastos/exportar_gastos_pdf.php?'+params;
})();
</script>
<?php if(!$ajax): ?>
</body>
</html>
<?php endif; ?>
