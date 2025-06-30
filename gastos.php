<?php
session_start();
include 'auth.php';
include 'conexion.php';

$proveedor = $_GET['proveedor'] ?? '';
$unidad = $_GET['unidad'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$cond = [];
if ($proveedor !== '') {
    $cond[] = 'g.proveedor_id=' . intval($proveedor);
}
if ($unidad !== '') {
    $cond[] = 'g.unidad_negocio_id=' . intval($unidad);
}
if ($fecha_inicio !== '') {
    $cond[] = "g.fecha_pago >= '".$conn->real_escape_string($fecha_inicio)."'";
}
if ($fecha_fin !== '') {
    $cond[] = "g.fecha_pago <= '".$conn->real_escape_string($fecha_fin)."'";
}
$where = $cond ? 'WHERE '.implode(' AND ',$cond) : '';

$sql = "SELECT g.folio, p.nombre AS proveedor, g.monto, g.fecha_pago, un.nombre AS unidad, g.tipo_gasto, g.medio_pago, g.cuenta_bancaria, g.estatus FROM gastos g LEFT JOIN proveedores p ON g.proveedor_id=p.id LEFT JOIN unidades_negocio un ON g.unidad_negocio_id=un.id $where ORDER BY g.fecha_pago DESC";
$res = $conn->query($sql);
$gastos = $res->fetch_all(MYSQLI_ASSOC);

$kpi_mes = $conn->query("SELECT SUM(monto) AS total FROM gastos WHERE MONTH(fecha_pago)=MONTH(CURDATE()) AND YEAR(fecha_pago)=YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;
$kpi_anio = $conn->query("SELECT SUM(monto) AS total FROM gastos WHERE YEAR(fecha_pago)=YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gastos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-light bg-white shadow-sm">
    <div class="container">
        <span class="navbar-brand">Módulo de Gastos</span>
        <a href="menu_principal.php" class="btn btn-outline-primary btn-sm">Menú</a>
    </div>
</nav>
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col">
            <div class="card">
                <div class="card-body text-center">
                    <strong>Gastos del Mes</strong><br>$<?php echo number_format($kpi_mes,2); ?>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body text-center">
                    <strong>Gastos del Año</strong><br>$<?php echo number_format($kpi_anio,2); ?>
                </div>
            </div>
        </div>
        <div class="col text-end align-self-center">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalGasto">Agregar Gasto</button>
        </div>
    </div>
    <form class="row g-2 mb-4">
        <div class="col-md-3">
            <select name="proveedor" class="form-select select2" data-placeholder="Proveedor">
                <option value="">Proveedor</option>
                <?php $pro=$conn->query("SELECT id,nombre FROM proveedores ORDER BY nombre");
                while($p=$pro->fetch_assoc()): ?>
                <option value="<?php echo $p['id']; ?>" <?php if($proveedor==$p['id']) echo 'selected';?>><?php echo htmlspecialchars($p['nombre']);?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="unidad" class="form-select select2" data-placeholder="Unidad">
                <option value="">Unidad</option>
                <?php $un=$conn->query("SELECT id,nombre FROM unidades_negocio ORDER BY nombre");
                while($u=$un->fetch_assoc()): ?>
                <option value="<?php echo $u['id']; ?>" <?php if($unidad==$u['id']) echo 'selected';?>><?php echo htmlspecialchars($u['nombre']);?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($fecha_inicio);?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($fecha_fin);?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>
    <div class="mb-3">
        <a href="exportar_gastos_pdf.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="btn btn-outline-danger btn-sm">PDF</a>
        <a href="exportar_gastos.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="btn btn-outline-success btn-sm">CSV</a>
    </div>
    <div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Proveedor</th>
                <th>Monto</th>
                <th>Fecha de pago</th>
                <th>Unidad</th>
                <th>Tipo</th>
                <th>Medio de pago</th>
                <th>Cuenta</th>
                <th>Estatus</th>
                <th>PDF</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($gastos as $g): ?>
            <tr>
                <td><?php echo htmlspecialchars($g['folio']); ?></td>
                <td><?php echo htmlspecialchars($g['proveedor']); ?></td>
                <td>$<?php echo number_format($g['monto'],2); ?></td>
                <td><?php echo htmlspecialchars($g['fecha_pago']); ?></td>
                <td><?php echo htmlspecialchars($g['unidad']); ?></td>
                <td><?php echo htmlspecialchars($g['tipo_gasto']); ?></td>
                <td><?php echo htmlspecialchars($g['medio_pago']); ?></td>
                <td><?php echo htmlspecialchars($g['cuenta_bancaria']); ?></td>
                <td><?php echo htmlspecialchars($g['estatus']); ?></td>
                <td><a class="btn btn-sm btn-outline-dark" target="_blank" href="generar_pdf_gasto.php?folio=<?php echo $g['folio']; ?>">PDF</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<div class="modal fade" id="modalGasto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="contenidoGasto"></div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
    $('.select2').select2({width:'100%'});
    $('#modalGasto').on('show.bs.modal', function(){
        $('#contenidoGasto').load('modal_gasto.php?modal=1');
    });
});
</script>
</body>
</html>
