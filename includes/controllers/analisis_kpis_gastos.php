<?php
session_start();
include '../../conexion.php';
header('Content-Type: application/json');

// Parámetros recibidos
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin    = $_POST['fecha_fin'] ?? null;
$unidad_id    = $_POST['unidad_negocio_id'] ?? null;

if (!$fecha_inicio || !$fecha_fin) {
    echo json_encode(['error' => 'Fechas inválidas']);
    exit;
}

// Condición SQL
$cond = [];
$cond[] = "fecha_pago BETWEEN '$fecha_inicio' AND '$fecha_fin'";
if (!empty($unidad_id)) {
    $cond[] = "unidad_negocio_id = " . intval($unidad_id);
}
$where = 'WHERE ' . implode(' AND ', $cond);

// 1. Gasto total
$total = $conn->query("SELECT SUM(monto) AS total FROM gastos $where")->fetch_assoc()['total'] ?? 0;

// 2. Por tipo
$tipos = [];
$res = $conn->query("SELECT tipo_gasto, SUM(monto) AS total FROM gastos $where GROUP BY tipo_gasto");
while ($row = $res->fetch_assoc()) {
    $tipo = trim($row['tipo_gasto']) ?: 'Sin tipo';
    $tipos[] = ['tipo' => $tipo, 'total' => (float)$row['total']];
}

// 3. Por unidad
$unidades = [];
$res = $conn->query("SELECT un.nombre AS unidad, SUM(g.monto) AS total 
    FROM gastos g 
    LEFT JOIN unidades_negocio un ON g.unidad_negocio_id = un.id
    $where GROUP BY un.nombre");
while ($row = $res->fetch_assoc()) {
    $unidad = trim($row['unidad']) ?: 'Sin unidad';
    $unidades[] = ['unidad' => $unidad, 'total' => (float)$row['total']];
}

// 4. Por estatus
$estatus = [];
$res = $conn->query("SELECT estatus, SUM(monto) AS total FROM gastos $where GROUP BY estatus");
while ($row = $res->fetch_assoc()) {
    $est = trim($row['estatus']) ?: 'Sin estatus';
    $estatus[] = ['estatus' => $est, 'total' => (float)$row['total']];
}

// 5. Por proveedor
$proveedores = [];
$res = $conn->query("SELECT p.nombre AS proveedor, SUM(g.monto) AS total 
    FROM gastos g 
    LEFT JOIN proveedores p ON g.proveedor_id = p.id
    $where GROUP BY p.nombre");
while ($row = $res->fetch_assoc()) {
    $prov = trim($row['proveedor']) ?: 'Sin proveedor';
    $proveedores[] = ['proveedor' => $prov, 'total' => (float)$row['total']];
}

// 6. Abonos vs Saldo
$abonos = $conn->query("SELECT 
    SUM(IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS abonado,
    SUM(g.monto - IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS saldo
    FROM gastos g $where")->fetch_assoc();

// Respuesta
$response = [
    'gasto_total' => (float)$total,
    'por_tipo'    => $tipos,
    'por_unidad'  => $unidades,
    'por_estatus' => $estatus,
    'por_proveedor' => $proveedores,
    'abonos' => [
        'abonado' => (float)($abonos['abonado'] ?? 0),
        'saldo'   => (float)($abonos['saldo'] ?? 0)
    ]
];

// Depuración (puedes quitar en producción)
file_put_contents('log_kpis_debug.txt', print_r($response, true));

echo json_encode($response);
