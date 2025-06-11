<?php
session_start();
include 'auth.php';
include 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administracion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background-color: #343a40;
            padding: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
        }
        .sidebar a:hover { background-color: #495057; }
        .content { margin-left: 260px; padding: 20px; }
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .content { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- 📌 Barra lateral de navegación -->
<div class="sidebar">
    <h4 class="text-white">Admin Panel</h4>
    <a href="admin_panel.php">Inicio</a>
    <a href="minipanel.php">🏠 MiniPanel</a>
    <a href="ordenes.php">📋 Órdenes de Compra</a>
    <a href="proveedores.php">🏢 Proveedores</a>
    <a href="listar_usuarios.php">👥 Usuarios</a>
    <a href="notas_credito.php">💳 Notas de Crédito</a>
    <a href="logout.php" class="text-danger">🚪 Cerrar Sesión</a>
</div>

<!-- 📌 Contenido -->
<div class="content">
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</h2>
    <p>Selecciona una opción del menú para comenzar.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
