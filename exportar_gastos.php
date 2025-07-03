<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=gastos.csv');
include 'conexion.php';

$cond = [];
if (!empty($_GET['proveedor'])) {
    $id = intval($_GET['proveedor']);
    $cond[] = "g.proveedor_id=$id";
}
if (!empty($_GET['unidad'])) {
    $id = intval($_GET['unidad']);
    $cond[] = "g.unidad_negocio_id=$id";
}
if (!empty($_GET['fecha_inicio'])) {
    $f = $conn->real_escape_string($_GET['fecha_inicio']);
    $cond[] = "g.fecha_pago >= '$f'";
}
if (!empty($_GET['fecha_fin'])) {
    $f = $conn->real_escape_string($_GET['fecha_fin']);
    $cond[] = "g.fecha_pago <= '$f'";
}
if (!empty($_GET['estatus'])) {
    $e = $conn->real_escape_string($_GET['estatus']);
    $cond[] = "g.estatus='$e'";
}
if (!empty($_GET['origen'])) {
    $o = $conn->real_escape_string($_GET['origen']);
    $cond[] = "g.origen='$o'";
}
$where = $cond ? 'WHERE '.implode(' AND ',$cond) : '';

$sql = "SELECT g.folio, p.nombre AS proveedor, g.monto, g.fecha_pago, un.nombre AS unidad, g.tipo_gasto, g.medio_pago, g.cuenta_bancaria, g.concepto, g.estatus FROM gastos g LEFT JOIN proveedores p ON g.proveedor_id=p.id LEFT JOIN unidades_negocio un ON g.unidad_negocio_id=un.id $where ORDER BY g.fecha_pago DESC";
$res = $conn->query($sql);
$out = fopen('php://output','w');
fputcsv($out,['Folio','Proveedor','Monto','Fecha','Unidad','Tipo','Medio','Cuenta','Concepto','Estatus']);
while($row=$res->fetch_assoc()){
    fputcsv($out,[$row['folio'],$row['proveedor'],$row['monto'],$row['fecha_pago'],$row['unidad'],$row['tipo_gasto'],$row['medio_pago'],$row['cuenta_bancaria'],$row['concepto'],$row['estatus']]);
}
fclose($out);
exit;
?>
