<?php
session_start();
include 'auth.php';
include 'conexion.php';
require_once 'app/components/TableOptions.php';
require_once 'app/components/PanelResumen.php';
require_once 'app/providers/ComprasDataProvider.php';

$options = new TableOptions([
    'page'      => $_GET['pagina'] ?? 1,
    'per_page'  => 500,
    'order_by'  => $_GET['orden'] ?? 'folio',
    'direction' => $_GET['dir'] ?? 'ASC'
]);

$provider = new ComprasDataProvider($conn);
$options->setTotal($provider->getTotalCount());
$kpis  = $provider->getKpis();
$data  = $provider->getTableData($options);
$panel = new PanelResumen('Órdenes de Compra', $kpis, $data, $options);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Órdenes de Compra</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
<?php $panel->render(); ?>
</div>
</body>
</html>
