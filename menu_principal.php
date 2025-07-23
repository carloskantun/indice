<?php
session_start();
include 'auth.php';
include 'router_roles.php';
include 'verificar_acceso.php';

redireccionar_por_puesto(obtener_puesto());

$rol    = strtolower(trim($_SESSION['user_role'] ?? $_SESSION['rol'] ?? ''));
$puesto = strtolower(trim($_SESSION['puesto'] ?? ''));

function verModulo($modulo) {
    global $rol, $puesto;
    $altos = ['superadmin', 'administrador', 'gerente', 'ceo', 'webmaster'];

    if (in_array($rol, $altos)) {
        return true;
    }

    switch ($modulo) {
        case 'mantenimiento':
            return str_contains($puesto, 'mantenimiento') || str_contains($puesto, 'servicio al cliente');
        case 'servicio_cliente':
            return str_contains($puesto, 'servicio al cliente');
        case 'transfers':
            return in_array($rol, ['superadmin', 'webmaster', 'admin', 'ceo']) ||
                   str_contains($puesto, 'operador') ||
                   str_contains($puesto, 'supervisor operador');
        case 'lavanderia':
            return in_array($rol, ['superadmin', 'administrador', 'gerente', 'admin']) ||
                   str_contains($puesto, 'lavanderia');
            return str_contains($puesto, 'camarista') || str_contains($puesto, 'ama de llaves');
        default:
            return false;
    }
}

// Evaluar si tiene acceso a algo
$modulos_disponibles = ['ordenes_compra', 'mantenimiento', 'servicio_cliente', 'usuarios', 'kpis', 'configuracion', 'camarista', 'transfers','lavanderia'];
$puede_ver_algo = false;
foreach ($modulos_disponibles as $m) {
    if (verModulo($m)) {
        $puede_ver_algo = true;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú Principal - Indice</title>
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
            <?php if (verModulo('gastos')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="gastos.php">
                            <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/gastos.PNG" width="50px"></span>
                            Gastos
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (verModulo('mantenimiento')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="minipanel_mantenimiento.php">
                            <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/mantenimiento.PNG" width="50px">️</span>
                            Mantenimiento
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (verModulo('servicio_cliente')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="minipanel_servicio_cliente.php">
                            <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/tareas.PNG" width="50px"></span>
                            Tareas Y Procesos
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (verModulo('transfers')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="minipanel_transfers.php">
                            <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/transfers.PNG" width="50px"></span>
                            Transfers
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (verModulo('lavanderia')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="minipanel_lavanderia.php">
                            <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/lavanderia.PNG" width="50px"></span>
                            Lavandería
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (verModulo('usuarios')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="usuarios.php">
                            <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/usuarios.PNG" width="50px"></span>
                            Gestión de Usuarios
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (verModulo('configuracion')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="panel_config.php">
                            <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/configuracion.PNG" width="50px">️</span>
                            Configuración
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (verModulo('camarista')): ?>
                <div class="col-12 col-md-4">
                    <div class="modulo-box">
                        <a href="reporte_camarista.php">
                            <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/camaristas.PNG" width="50px"></span>
                            Reporte Camarista
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Nuevos accesos generales sin restricciones -->
            <?php if (verModulo('usuarios')): ?>
            <!-- Recursos Humanos -->
            <div class="col-12 col-md-4">
                <div class="modulo-box">
                    <a href="https://docs.google.com/spreadsheets/d/19_Bi2m5STQXcNn7YIC2WA87OPplbuUOrfRlwZST0UqE/edit?gid=2094784319" target="_blank">
                        <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/Recursos.jpeg" width="50px"></span>
                        Recursos Humanos
                    </a>
                </div>
            </div>

            <!-- Notas de Crédito -->
            <div class="col-12 col-md-4">
                <div class="modulo-box">
                    <a href="https://docs.google.com/spreadsheets/d/1L9Pz7Vpc25emKZa-VP30Uxts2mrXkCt-m1sEqCyB7xY/edit?gid=0" target="_blank">
                        <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/notas_credito.png" width="50px"></span>
                        Notas de Crédito
                    </a>
                </div>
            </div>

            <!-- Control de Minutas -->
            <div class="col-12 col-md-4">
                <div class="modulo-box">
                    <a href="https://drive.google.com/drive/folders/1DZv9UlTs6pAjpy5PfStnBvoJIdpjFbdv" target="_blank">
                        <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/minutas.png" width="50px"></span>
                        Control de Minutas
                    </a>
                </div>
            </div>

            <!-- Control de Vehículos -->
            <div class="col-12 col-md-4">
                <div class="modulo-box">
                    <a href="https://docs.google.com/spreadsheets/d/1vPr0WZilpdN_qcRTCknrTyHhjM45rKOZ_EwDWRdPJTM/edit?gid=1497749633#gid=1497749633" target="_blank">
                        <span class="modulo-icon"><img src="https://elcorazondelcaribe.com/indice/uploads/imgs/control_vehiculo.jpeg" width="50px"></span>
                        Control de Vehículos
                    </a>
                </div>
            </div>
                        <?php endif; ?>
            <?php if (!$puede_ver_algo): ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        Acceso restringido. Consulta con el administrador.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>