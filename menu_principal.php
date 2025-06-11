<?php
session_start();
include 'auth.php';
include 'router_roles.php';
include 'verificar_acceso.php'; // << Reemplaza config y funciones auxiliares

// Redirección automática según el puesto (si aplica)
redireccionar_por_puesto(obtener_puesto());

// Permite evaluar varios puestos separados por coma
function tienePuesto($puesto) {
    $lista = array_map('trim', explode(',', strtolower($_SESSION['puesto'] ?? '')));
    return in_array(strtolower($puesto), $lista);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú Principal - PocketTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modulo-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            background-color: #f9f9f9;
        }
        .modulo-box:hover {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            background-color: #e9ecef;
        }
        .modulo-box a {
            text-decoration: none;
            color: #000;
            font-size: 1.2rem;
        }
        .modulo-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: block;
        }
        @media (max-width: 767px) {
            .modulo-box {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
        <h4 class="mb-4 text-center">Selecciona un módulo</h4>

        <div class="row justify-content-center g-4">

            <?php if (puede_ver_modulo('compras')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="minipanel.php">
                            <span class="modulo-icon">📦</span>
                            Órdenes de Compra
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (tienePuesto('mantenimiento')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="minipanel_mantenimiento.php">
                            <span class="modulo-icon">🛠️</span>
                            Mantenimiento
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (tienePuesto('servicio al cliente')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="minipanel_servicio_cliente.php">
                            <span class="modulo-icon">📞</span>
                            Servicio al Cliente
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (puede_ver_modulo('usuarios')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="usuarios.php">
                            <span class="modulo-icon">👤</span>
                            Gestión de Usuarios
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (puede_ver_modulo('kpis')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="kpis_mantenimiento.php">
                            <span class="modulo-icon">📊</span>
                            KPIs
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (puede_ver_modulo('configuracion')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="panel_config.php">
                            <span class="modulo-icon">⚙️</span>
                            Configuración
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>
