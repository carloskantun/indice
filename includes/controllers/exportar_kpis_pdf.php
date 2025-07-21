<?php
session_start();
include '../../conexion.php';

// 👇 Carga DOMPDF sin Composer (ruta manual)
require_once __DIR__ . '/../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;

$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin    = $_GET['fecha_fin'] ?? null;
$unidad_id    = $_GET['unidad_negocio_id'] ?? null;

if (!$fecha_inicio || !$fecha_fin) {
    echo "Fechas inválidas";
    exit;
}

$cond = [];
$cond[] = "fecha_pago BETWEEN '$fecha_inicio' AND '$fecha_fin'";
if (!empty($unidad_id)) {
    $cond[] = "unidad_negocio_id = " . intval($unidad_id);
}
$where = 'WHERE ' . implode(' AND ', $cond);

// Gasto total
$total = $conn->query("SELECT SUM(monto) AS total FROM gastos $where")->fetch_assoc()['total'] ?? 0;

// Por tipo
$tipos = $conn->query("SELECT tipo_gasto, SUM(monto) AS total FROM gastos $where GROUP BY tipo_gasto");

// Por unidad
$unidades = $conn->query("SELECT un.nombre, SUM(g.monto) AS total 
FROM gastos g LEFT JOIN unidades_negocio un ON g.unidad_negocio_id = un.id
$where GROUP BY un.nombre");

// Por estatus
$estatus = $conn->query("SELECT estatus, SUM(monto) AS total FROM gastos $where GROUP BY estatus");

// Por proveedor (top 10)
$proveedores = $conn->query("SELECT p.nombre, SUM(g.monto) AS total 
FROM gastos g 
LEFT JOIN proveedores p ON g.proveedor_id = p.id
$where GROUP BY p.nombre ORDER BY total DESC LIMIT 10");

// Abonos
$abonos = $conn->query("SELECT 
SUM(IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS abonado,
SUM(g.monto - IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS saldo
FROM gastos g $where")->fetch_assoc();

// HTML del reporte
ob_start();
?>

<style>
  body { font-family: sans-serif; font-size: 12px; }
  h1, h2 { text-align: center; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  th, td { border: 1px solid #ccc; padding: 5px; }
  th { background-color: #f5f5f5; }
</style>

<h1>📊 Reporte de KPIs - Gastos</h1>
<p><strong>Periodo:</strong> <?= htmlspecialchars($fecha_inicio) ?> al <?= htmlspecialchars($fecha_fin) ?></p>
<p><strong>Total del periodo:</strong> $<?= number_format($total, 2) ?></p>
<p><strong>Abonado:</strong> $<?= number_format($abonos['abonado'] ?? 0, 2) ?> | 
   <strong>Saldo:</strong> $<?= number_format($abonos['saldo'] ?? 0, 2) ?></p>

<h2>Por tipo de gasto</h2>
<table>
  <thead><tr><th>Tipo</th><th>Total</th></tr></thead>
  <tbody>
    <?php while($row = $tipos->fetch_assoc()): ?>
    <tr>
      <td><?= $row['tipo_gasto'] ?: 'Sin tipo' ?></td>
      <td>$<?= number_format($row['total'], 2) ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<h2>Por unidad de negocio</h2>
<table>
  <thead><tr><th>Unidad</th><th>Total</th></tr></thead>
  <tbody>
    <?php while($row = $unidades->fetch_assoc()): ?>
    <tr>
      <td><?= $row['nombre'] ?: 'Sin unidad' ?></td>
      <td>$<?= number_format($row['total'], 2) ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<h2>Por estatus</h2>
<table>
  <thead><tr><th>Estatus</th><th>Total</th></tr></thead>
  <tbody>
    <?php while($row = $estatus->fetch_assoc()): ?>
    <tr>
      <td><?= $row['estatus'] ?: 'Sin estatus' ?></td>
      <td>$<?= number_format($row['total'], 2) ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<h2>Top 10 proveedores</h2>
<table>
  <thead><tr><th>Proveedor</th><th>Total</th></tr></thead>
  <tbody>
    <?php while($row = $proveedores->fetch_assoc()): ?>
    <tr>
      <td><?= $row['nombre'] ?: 'Sin proveedor' ?></td>
      <td>$<?= number_format($row['total'], 2) ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php
$html = ob_get_clean();

// Generar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Mostrar en navegador
$dompdf->stream("reporte_kpis_gastos.pdf", ["Attachment" => false]);
exit;
