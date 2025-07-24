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
$cond = ["fecha_pago BETWEEN ? AND ?"];
$params = [$fecha_inicio, $fecha_fin];
$types  = 'ss';
if (!empty($unidad_id)) {
    $cond[]  = "unidad_negocio_id = ?";
    $params[] = (int)$unidad_id;
    $types   .= 'i';
}
$where = 'WHERE ' . implode(' AND ', $cond);

function fetch_value(mysqli $conn, string $sql, string $types, array $params) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        log_error('Error al preparar consulta en analisis_kpis_gastos: ' . $conn->error);
        return 0;
    }
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        log_error('Error al ejecutar consulta en analisis_kpis_gastos: ' . $stmt->error);
        $stmt->close();
        return 0;
    }
    $res = $stmt->get_result();
    $val = $res ? ($res->fetch_assoc()['total'] ?? 0) : 0;
    $stmt->close();
    return $val;
}

function fetch_all(mysqli $conn, string $sql, string $types, array $params) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        log_error('Error al preparar consulta en analisis_kpis_gastos: ' . $conn->error);
        return false;
    }
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        log_error('Error al ejecutar consulta en analisis_kpis_gastos: ' . $stmt->error);
        $stmt->close();
        return false;
    }
    $res = $stmt->get_result();
    $stmt->close();
    return $res;
}

// 1. Gasto total
$total = fetch_value($conn, "SELECT SUM(monto) AS total FROM gastos $where", $types, $params);

// 2. Por tipo
$tipos = [];
$res = fetch_all($conn, "SELECT tipo_gasto, SUM(monto) AS total FROM gastos $where GROUP BY tipo_gasto", $types, $params);
while ($row = $res->fetch_assoc()) {
    $tipo = trim($row['tipo_gasto']) ?: 'Sin tipo';
    $tipos[] = ['tipo' => $tipo, 'total' => (float)$row['total']];
}

// 3. Por unidad
$unidades = [];
$res = fetch_all(
    $conn,
    "SELECT un.nombre AS unidad, SUM(g.monto) AS total FROM gastos g LEFT JOIN unidades_negocio un ON g.unidad_negocio_id = un.id $where GROUP BY un.nombre",
    $types,
    $params
);
while ($row = $res->fetch_assoc()) {
    $unidad = trim($row['unidad']) ?: 'Sin unidad';
    $unidades[] = ['unidad' => $unidad, 'total' => (float)$row['total']];
}

// 4. Por estatus
$estatus = [];
$res = fetch_all($conn, "SELECT estatus, SUM(monto) AS total FROM gastos $where GROUP BY estatus", $types, $params);
while ($row = $res->fetch_assoc()) {
    $est = trim($row['estatus']) ?: 'Sin estatus';
    $estatus[] = ['estatus' => $est, 'total' => (float)$row['total']];
}

// 5. Por proveedor
$proveedores = [];
$res = fetch_all(
    $conn,
    "SELECT p.nombre AS proveedor, SUM(g.monto) AS total FROM gastos g LEFT JOIN proveedores p ON g.proveedor_id = p.id $where GROUP BY p.nombre",
    $types,
    $params
);
while ($row = $res->fetch_assoc()) {
    $prov = trim($row['proveedor']) ?: 'Sin proveedor';
    $proveedores[] = ['proveedor' => $prov, 'total' => (float)$row['total']];
}

// 6. Abonos vs Saldo
$stmt = $conn->prepare("SELECT
    SUM(IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS abonado,
    SUM(g.monto - IFNULL((SELECT SUM(a.monto) FROM abonos_gastos a WHERE a.gasto_id = g.id), 0)) AS saldo
    FROM gastos g $where");
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $abonos = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    log_error('Error al preparar consulta en analisis_kpis_gastos: ' . $conn->error);
    $abonos = ['abonado'=>0,'saldo'=>0];
}

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
