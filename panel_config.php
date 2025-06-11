<?php
session_start();
include 'auth.php'; // Protección de sesión
include 'conexion.php'; // Ahora usa el archivo de conexión
header('Content-Type: text/html; charset=utf-8');

// Obtener ID del usuario autenticado
$usuario_id = $_SESSION['user_id'];
$rol = $_SESSION['user_role'];

// Si es admin o superadmin, puede ver todo
$filtro = ($rol === 'superadmin' || $rol === 'admin') ? "1=1" : "usuario_solicitante_id = $usuario_id";

// Contar órdenes propias o del negocio
$total_ordenes = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE $filtro")->fetch_assoc()['total'];
$total_proveedores = $conn->query("SELECT COUNT(*) AS total FROM proveedores")->fetch_assoc()['total'];
$total_usuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Configuración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <a href="minipanel.php" class="btn btn-outline-primary">⬅️ Volver</a>
        <h2 class="text-center mb-4">⚙️ Panel de Configuración</h2>

        <div class="row">
            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5>📑 Órdenes de Compra</h5>
                        <p><?php echo $total_ordenes; ?> registradas</p>
                        <a href="ordenes.php" class="btn btn-primary">Ver Órdenes</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5>🏢 Proveedores</h5>
                        <p><?php echo $total_proveedores; ?> registrados</p>
                        <a href="proveedores.php" class="btn btn-warning">Ver Proveedores</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5>👥 Usuarios</h5>
                        <p><?php echo $total_usuarios; ?> en tu empresa</p>
                        <a href="usuarios.php" class="btn btn-dark">Ver Usuarios</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="notas_credito.php" class="btn btn-info">💳 Gestionar Notas de Crédito</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
