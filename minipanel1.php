<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'auth.php'; // Protección de sesión

$servername = "localhost";
$username = "corazon_caribe";
$password = "Kantun.01*";
$database = "corazon_orderdecompras";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
// KPIs
$ordenes_totales = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra")->fetch_assoc()['total'];
$ordenes_liquidadas = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE estatus_pago = 'Pagado'")->fetch_assoc()['total'];
$ordenes_por_liquidar = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE estatus_pago = 'Por pagar'")->fetch_assoc()['total'];
$notas_credito = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE tipo_pago = 'Nota de Crédito'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minipanel - Control de Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">Control de Gastos</a>
            <div>
                <span class="me-3">Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <!-- Navegación por rol -->
        <div class="mb-4">
            <?php if ($_SESSION['user_role'] === 'superadmin'): ?>
                <a href="usuarios.php" class="btn btn-primary">Usuarios</a>
                <a href="proveedores.php" class="btn btn-secondary">Agregar Proveedor</a>
            <?php endif; ?>
            <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
                <a href="ordenes_compra.php" class="btn btn-secondary">Ingresar Orden de Compra</a>
        <!-- KPIs -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Órdenes de Compras Vencidas</h5>
                        <p class="card-text fs-4"><?php echo $ordenes_totales; ?></p>
                    </div>
                </div>
                        <h5 class="card-title"> Órdenes de Compras Vencidas del Mes</h5>
                        <p class="card-text fs-4"><?php echo $ordenes_liquidadas; ?></p>
                        <h5 class="card-title">Total del Órdenes de Compras Vencidas </h5>
                        <p class="card-text fs-4"><?php echo $ordenes_por_liquidar; ?></p>
                        <h5 class="card-title">% de Órdenes de mes liquidadas</h5>
                        <p class="card-text fs-4"><?php echo $notas_credito; ?></p>
        <!-- Tabla de Órdenes de Compra -->
        <h4 class="mb-3">Filtros</h4>
        <table class="table table-striped">
        <!-- Filtros Avanzados -->
<form method="GET" class="mb-4">
    <div class="row g-3">
        <!-- Filtro por Proveedor -->
        <div class="col-md-3">
            <label for="proveedor" class="form-label">Proveedor</label>
            <select class="form-control" id="proveedor" name="proveedor">
                <option value="">Todos</option>
                <?php
                $proveedores = $conn->query("SELECT id, nombre FROM proveedores");
                while ($proveedor = $proveedores->fetch_assoc()):
                ?>
                    <option value="<?php echo $proveedor['id']; ?>" 
                        <?php echo isset($_GET['proveedor']) && $_GET['proveedor'] == $proveedor['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($proveedor['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        <!-- Filtro por Estatus -->
            <label for="estatus" class="form-label">Estatus de Pago</label>
            <select class="form-control" id="estatus" name="estatus">
                <option value="Por pagar" <?php echo isset($_GET['estatus']) && $_GET['estatus'] == 'Por pagar' ? 'selected' : ''; ?>>Por pagar</option>
                <option value="Pagado" <?php echo isset($_GET['estatus']) && $_GET['estatus'] == 'Pagado' ? 'selected' : ''; ?>>Pagado</option>
                <option value="Vencido" <?php echo isset($_GET['estatus']) && $_GET['estatus'] == 'Vencido' ? 'selected' : ''; ?>>Vencido</option>
                <option value="Pago parcial" <?php echo isset($_GET['estatus']) && $_GET['estatus'] == 'Pago parcial' ? 'selected' : ''; ?>>Pago parcial</option>
                <option value="Cancelado" <?php echo isset($_GET['estatus']) && $_GET['estatus'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
        <!-- Filtro por Rango de Fechas -->
            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                value="<?php echo isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : ''; ?>">
            <label for="fecha_fin" class="form-label">Fecha Fin</label>
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                value="<?php echo isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : ''; ?>">
        <!-- Filtro por Usuario -->
            <label for="usuario" class="form-label">Usuario</label>
            <select class="form-control" id="usuario" name="usuario">
                $usuarios = $conn->query("SELECT id, nombre FROM usuarios");
                while ($usuario = $usuarios->fetch_assoc()):
                    <option value="<?php echo $usuario['id']; ?>" 
                        <?php echo isset($_GET['usuario']) && $_GET['usuario'] == $usuario['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($usuario['nombre']); ?>
        <!-- Botón de Filtrar -->
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
    </div>
</form>
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Proveedor</th>
                    <th>Usuario</th>
                    <th>Monto</th>
                    <th>Vencimiento</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                $ordenes = $conn->query("SELECT folio, monto, vencimiento_pago, estatus_pago, 
                                                (SELECT nombre FROM proveedores WHERE id = proveedor_id) AS proveedor, 
                                                (SELECT nombre FROM usuarios WHERE id = usuario_solicitante_id) AS usuario 
                                         FROM ordenes_compra LIMIT 10");
                while ($orden = $ordenes->fetch_assoc()):
                    <tr>
                        <td><?php echo htmlspecialchars($orden['folio']); ?></td>
                        <td><?php echo htmlspecialchars($orden['proveedor']); ?></td>
                        <td><?php echo htmlspecialchars($orden['usuario']); ?></td>
                        <td>$<?php echo number_format($orden['monto'], 2); ?></td>
                        <td><?php echo htmlspecialchars($orden['vencimiento_pago']); ?></td>
                        <td><?php echo htmlspecialchars($orden['estatus_pago']); ?></td>
                    </tr>
            </tbody>
        </table>
</body>
</html>
