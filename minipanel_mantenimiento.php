<?php
//session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'auth.php'; // Proteccin de sesin
include 'conexion.php'; // Ahora usa el archivo de conexin
header('Content-Type: text/html; charset=utf-8');


// 98 KPIs
$ordenes_totales = $conn->query("SELECT COUNT(*) AS total FROM ordenes_mantenimiento")->fetch_assoc()['total'];
$ordenes_pagadas = $conn->query("SELECT COUNT(*) AS total FROM ordenes_mantenimiento WHERE estatus = 'Pagado'")->fetch_assoc()['total'];
$ordenes_por_liquidar = $conn->query("SELECT COUNT(*) AS total FROM ordenes_mantenimiento WHERE estatus = 'Por pagar'")->fetch_assoc()['total'];
$ordenes_vencidas = $conn->query("SELECT COUNT(*) AS total FROM ordenes_mantenimiento WHERE estatus = 'Vencido'")->fetch_assoc()['total'];

// 98 Paginacin
$registros_por_pagina = 1500;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// 98 Construccin de la consulta con filtros dinmicos
$query = "SELECT folio, fecha_reporte, descripcion_reporte, foto, estatus, nivel, quien_realizo_id, fecha_completado, detalle_completado, foto_completado, costo_final, ponderacion,
                 (SELECT nombre FROM alojamientos WHERE id = alojamiento_id) AS alojamiento, 
                 (SELECT nombre FROM usuarios WHERE id = usuario_solicitante_id) AS usuario,
                 (SELECT nombre FROM unidades_negocio WHERE id = unidad_negocio_id) AS unidad_negocio
          FROM ordenes_mantenimiento WHERE 1=1";


// 98 Verificar y actualizar automticamente rdenes vencidas
$conn->query("UPDATE ordenes_mantenimiento 
              SET estatus = 'Vencido' 
              WHERE fecha_reporte < CURDATE() 
              AND estatus NOT IN ('Pagado', 'Cancelado', 'Terminado')");

// Aplicar filtros dinmicos
if (!empty($_GET['alojamiento']) && is_array($_GET['alojamiento'])) {
    $alojamientoes_ids = array_map('intval', $_GET['alojamiento']); // Asegurar que son enteros
    $alojamientoes_ids_str = implode(',', $alojamientoes_ids); // Convertir array en string separado por comas
    $query .= " AND alojamiento_id IN ($alojamientoes_ids_str)";
}


if (!empty($_GET['estatus'])) {
    $estatus = trim($conn->real_escape_string($_GET['estatus']));
    $query .= " AND COALESCE(NULLIF(TRIM(estatus), ''), 'Pendiente') = '$estatus'";
}
if (!empty($_GET['usuario'])) {
    $usuarios = is_array($_GET['usuario']) ? $_GET['usuario'] : [$_GET['usuario']];
    $usuario_ids = array_map('intval', $usuarios);
    $query .= " AND usuario_solicitante_id IN (" . implode(',', $usuario_ids) . ")";
}
if (!empty($_GET['unidad_negocio'])) {
    $unidad_negocio = is_array($_GET['unidad_negocio']) ? $_GET['unidad_negocio'] : [$_GET['unidad_negocio']];
    $unidad_ids = array_map('intval', $unidad_negocio);
    $query .= " AND unidad_negocio_id IN (" . implode(',', $unidad_ids) . ")";
}
if (!empty($_GET['fecha_inicio'])) {
    $fecha_inicio = $conn->real_escape_string($_GET['fecha_inicio']);
    $query .= " AND fecha_reporte >= '$fecha_inicio'";
}
if (!empty($_GET['fecha_fin'])) {
    $fecha_fin = $conn->real_escape_string($_GET['fecha_fin']);
    $query .= " AND fecha_reporte <= '$fecha_fin'";
}
$mapa_orden_sql = [
  'descripcion' => 'descripcion_reporte',
  'folio' => 'folio',
  'alojamiento' => 'alojamiento',
  'foto' => 'foto',
  'fecha' => 'fecha_reporte',
  'usuario' => 'usuario',
  'unidad_negocio' => 'unidad_negocio',
  'estatus' => 'estatus',
  'quien_pago' => 'quien_realizo_id',
  'nivel' => 'nivel',
  'completar' => 'estatus',
  'fecha_completado' => 'fecha_completado',
  'detalle_completado' => 'detalle_completado',
  'foto_completado' => 'foto_completado',
  'costo_final' => 'costo_final',
  'ver_pdf' => 'folio' // o cualquier campo para que no falle
];

$orden_key = $_GET['orden'] ?? 'id';
$columna_orden = $mapa_orden_sql[$orden_key] ?? 'id';
$direccion = strtoupper($_GET['dir'] ?? 'ASC');
$direccion = ($direccion === 'DESC') ? 'DESC' : 'ASC';

$query .= " ORDER BY $columna_orden $direccion LIMIT $registros_por_pagina OFFSET $offset";

$ordenes = $conn->query($query);

// 98 Obtener el total de registros para la paginacin
$total_ordenes = $conn->query("SELECT COUNT(*) AS total FROM ordenes_mantenimiento WHERE 1=1")->fetch_assoc()['total'];
$total_paginas = ceil($total_ordenes / $registros_por_pagina);
function corregirCodificacion($cadena) {
    return mb_convert_encoding($cadena, 'UTF-8', 'ISO-8859-1');
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minipanel - Control de Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 100%;
        }

        .btn-custom {
            min-width: 150px;
            font-size: 0.9rem;
        }

        /* Scroll para tabla */
        .table-responsive { overflow-x: auto; }
        th, td { white-space: nowrap; }

        /* Personalizacin de etiquetas del dropdown */
        .dropdown-item label {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Espaciado entre secciones */
        .section-spacing {
            margin-top: 20px;
            margin-bottom: 20px;
        }
         .btn-custom {
        font-size: 0.9rem;
        padding: 0.6rem 1rem;
        text-align: center;
    }
    @media (min-width: 992px) {
    .container-fluid {
        padding-left: 30px;
        padding-right: 30px;
    }

    .table {
        font-size: 0.85rem;
    }
}

    @media (max-width: 576px) {
        .btn-custom {
            font-size: 0.85rem;
            padding: 0.5rem 0.8rem;
        }
    }
        
        .table-responsive { overflow-x: auto; }  /* 97 Arreglo para mviles */

    th a {
    text-decoration: none;
    color: inherit;
        }
    th a:hover {
    text-decoration: underline;
    }
    </style>
    <!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS y JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>


</head>
<body class="bg-light">

<?php $es_superadmin = $_SESSION['user_role'] === 'superadmin'; ?>
<?php $es_admin = $_SESSION['user_role'] === 'admin'; ?>
<?php $es_usuario = $_SESSION['user_role'] === 'user'; ?>

    <!-- Barra de navegacin -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">Control de Gastos</a>
            <div>
                <span class="me-3">Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="admin_panel.php" class="btn btn-secondary btn-sm">Panel de Administracion</a>
                <a href="panel_config.php" class="btn btn-secundary btn-sm">Configuracin</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesion</a>
            </div>
        </div>
        
    </nav>
    <div class="container-fluid mt-5">

    <!-- Botones principales -->
<div class="row g-2 mb-4">
    <?php if ($_SESSION['user_role'] === 'superadmin'): ?>
        <div class="col-12 col-md-auto">
            <button class="btn btn-primary btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">Agregar Usuario</button>
        </div>
        <div class="col-12 col-md-auto">
            <button class="btn btn-secondary btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalAgregarAlojamiento">Agregar Alojamiento</button>
        </div>
    <?php endif; ?>
        <div class="col-12 col-md-auto">
            <button class="btn btn-success btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalIngresarOrden">Ingresar Reporte de Mantenimiento</button>
        </div>
    <div class="col-12 col-md-auto">
        <button class="btn btn-info btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalKPIs">Resumen de KPIs</button>
    </div>
    <div class="col-12 col-md-auto">
        <a href="kpis_mantenimiento.php" class="btn btn-primary btn-custom w-100">Ver Detalles de KPIs</a>
        </div>
</div>

        <h4 class="mb-3">Reportes de Mantenimiento</h4>
    

<!-- Filtros en Acorden -->
        <div class="accordion mb-4" id="accordionFiltros">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingFiltros">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltros" aria-expanded="true" aria-controls="collapseFiltros">
                Filtros
            </button>
        </h2>
        <div id="collapseFiltros" class="accordion-collapse collapse" aria-labelledby="headingFiltros" data-bs-parent="#accordionFiltros">
            <div class="accordion-body">
                <form method="GET">
                    <div class="row g-3">
                        <!-- Alojamiento -->
                        <div class="col-12 col-md-4">
                        <label for="alojamiento" class="form-label">Alojamientos</label>
                       <select class="form-select select2-multiple" id="alojamiento" name="alojamiento[]" multiple="multiple">
                       <option value="">Seleccione alojamientos</option> <!-- 97 IMPORTANTE: Placeholder similar a estatus -->
                        <?php
                       $alojamientos = $conn->query("SELECT id, nombre FROM alojamientos");
                       while ($alojamiento = $alojamientos->fetch_assoc()):
                       $selected = (isset($_GET['alojamiento']) && is_array($_GET['alojamiento']) && in_array($alojamiento['id'], $_GET['alojamiento'])) ? 'selected' : '';
                        ?>
                        <option value="<?php echo htmlspecialchars($alojamiento['id']); ?>" <?php echo $selected; ?>>
                       <?php echo htmlspecialchars($alojamiento['nombre']); ?>
                      </option>
                       <?php endwhile; ?>
                       </select>
                    </div>

                        <!-- Estatus -->
                        <div class="col-12 col-md-4">
                            <label for="estatus" class="form-label">Estatus</label>
                            <select class="form-select select2-single" id="estatus" name="estatus">
                            <option value="">Todos</option>
                            <option value="Pendiente" <?php echo ($_GET['estatus'] ?? '') === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="En proceso" <?php echo ($_GET['estatus'] ?? '') === 'En proceso' ? 'selected' : ''; ?>>En proceso</option>
                            <option value="Terminado" <?php echo ($_GET['estatus'] ?? '') === 'Terminado' ? 'selected' : ''; ?>>Terminado</option>
                            <option value="Cancelado" <?php echo ($_GET['estatus'] ?? '') === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>


                            </select>
                        </div>

                        <!-- Usuario -->
                        <div class="col-12 col-md-4">
                            <label for="usuario" class="form-label">Usuario Solicitante</label>
                            <select class="form-select select2-multiple" id="usuario" name="usuario[]" multiple="multiple">
                                <option value="">Todos</option>
                                <?php
                                $usuarios = $conn->query("SELECT id, nombre FROM usuarios");
                                while ($usuario = $usuarios->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $usuario['id']; ?>" 
                                        <?php echo (isset($_GET['usuario']) && is_array($_GET['usuario']) && in_array($usuario['id'], $_GET['usuario'])) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($usuario['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <!-- Unidad de Negocio -->
                        <div class="col-12 col-md-6">
                            <label for="unidad_negocio" class="form-label">Unidad de Negocio</label>
                            <select class="form-select select2-multiple" id="unidad_negocio" name="unidad_negocio[]" multiple="multiple">
                                <option value="">Todos</option>
                                <?php
                                $unidades = $conn->query("SELECT id, nombre FROM unidades_negocio");
                                while ($unidad = $unidades->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $unidad['id']; ?>" 
                                        <?php echo (isset($_GET['unidad_negocio']) && is_array($_GET['unidad_negocio']) && in_array($unidad['id'], $_GET['unidad_negocio'])) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($unidad['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Rango de Fechas -->
                        <div class="col-6 col-md-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                value="<?php echo isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : ''; ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                value="<?php echo isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : ''; ?>">
                        </div>
                    </div>

                    <!-- Botn de Filtrar -->
                    <div class="text-end mt-2">
                    <a href="minipanel_mantenimiento.php" class="btn btn-outline-secondary">Limpiar Filtros</a></div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- 98 Men de seleccin de columnas -->
    <!-- Menú de selección de columnas -->
<div class="dropdown mb-3">
  <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
    Columnas
  </button>
  <ul class="dropdown-menu">
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="folio"> Folio</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="alojamiento"> Alojamiento</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="descripcion"> Descripción</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="foto"> Foto</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="fecha"> Fecha</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="usuario"> Usuario</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="unidad_negocio"> Unidad de Negocio</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="estatus"> Estatus</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="quien_pago"> Quién Realizó</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="nivel"> Nivel</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="completar"> Completar</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="fecha_completado"> Fecha Ejecución</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="detalle_completado"> Detalle</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="foto_completado"> Foto Final</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="costo_final"> Costo Final</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="ponderacion"> Ponderación</label></li>
    <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="ver_pdf"> Ver PDF</label></li>
  </ul>
  <button id="btnFiltroPendientes" class="btn btn-outline-primary btn-custom" type="button">Pendientes</button>
<button id="btnFiltroTerminados" class="btn btn-outline-success btn-custom" type="button">Terminados</button>

</div>

<!-- 98 Tabla de 07rdenes de Compra -->
<!-- 98 Tabla de Órdenes de Mantenimiento -->
<div class="table-responsive">
    <table class="table table-striped table-sm">
<thead>
  <tr id="columnas-reordenables">
<?php
$columnas_ordenables = [
  'descripcion' => 'Descripción',
  'folio' => 'Folio',
  'alojamiento' => 'Alojamiento',
  'foto' => 'Foto',
  'fecha' => 'Fecha',
  'usuario' => 'Usuario',
  'unidad_negocio' => 'Unidad de Negocio',
  'estatus' => 'Estatus',
  'quien_pago' => 'Quién Realizó',
  'nivel' => 'Nivel',
  'completar' => 'Completar',
  'fecha_completado' => 'Fecha Ejecución',
  'detalle_completado' => 'Detalle',
  'foto_completado' => 'Foto Final',
  'costo_final' => 'Costo Final',
  'ponderacion' => 'Ponderación',
  'ver_pdf' => 'PDF'
];

$orden_actual = $_GET['orden'] ?? '';
$dir_actual = $_GET['dir'] ?? 'ASC';

foreach ($columnas_ordenables as $col => $label):
    $params = $_GET;
    $params['orden'] = $col;
    $params['dir'] = ($orden_actual === $col && $dir_actual === 'ASC') ? 'DESC' : 'ASC';
    $url = '?' . http_build_query($params);
    $icon = ($orden_actual === $col) ? ($dir_actual === 'DESC' ? '↓' : '↑') : '';
?>
    <th class="col-<?php echo $col; ?>">
        <a href="<?php echo htmlspecialchars($url); ?>" style="text-decoration:none; color:inherit;">
            <?php echo $label . ' ' . $icon; ?>
        </a>
    </th>
<?php endforeach; ?>
</tr>
</thead>
        <tbody id="tabla-ordenes">
            <?php if ($ordenes->num_rows === 0): ?>
    <tr>
        <td colspan="16" class="text-center text-danger py-4">
            No se encontraron resultados con los filtros aplicados.
        </td>
    </tr>
<?php endif; ?>
            <?php while ($orden = $ordenes->fetch_assoc()): ?>
                <tr>
                    <td class="col-folio"><?php echo htmlspecialchars($orden['folio']); ?></td>
                    <td class="col-alojamiento"><?php echo htmlspecialchars($orden['alojamiento']); ?></td>
                    <td class="col-descripcion">
    <?php 
    $desc = wordwrap($orden['descripcion_reporte'], 80, "\n", true); 
    $lineas = explode("\n", $desc);
    $lineas = array_slice($lineas, 0, 5);
    echo nl2br(htmlspecialchars(implode("\n", $lineas)));
    ?>
</td>
                    <td class="col-foto">
                        <?php if (!empty($orden['foto'])): ?>
                            <img src="<?php echo htmlspecialchars($orden['foto']); ?>" style="max-width: 100px;">
                        <?php else: ?>
                            <span class="text-muted">Sin imagen</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-fecha"><?php echo htmlspecialchars($orden['fecha_reporte']); ?></td>
                    <td class="col-usuario"><?php echo htmlspecialchars($orden['usuario']); ?></td>
                    <td class="col-unidad_negocio"><?php echo htmlspecialchars($orden['unidad_negocio']); ?></td>

                    <!-- Estatus -->
                    <td class="col-estatus text-center">
                        <?php
                        $estatus_actual = $orden['estatus'] ?? 'Pendiente';
                        if ($estatus_actual === '') $estatus_actual = 'Pendiente';
                        ?>
                        <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
                            <form method="POST" class="estatus-form">
                                <input type="hidden" name="orden_id" value="<?php echo $orden['folio']; ?>">
                                <select name="estatus" class="form-select estatus-select" data-id="<?php echo $orden['folio']; ?>">
                                    <option value="Pendiente" <?php echo ($estatus_actual == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="En proceso" <?php echo ($estatus_actual == 'En proceso') ? 'selected' : ''; ?>>En proceso</option>
                                    <option value="Terminado" <?php echo ($estatus_actual == 'Terminado') ? 'selected' : ''; ?>>Terminado</option>
                                    <option value="Cancelado" <?php echo ($estatus_actual == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </form>
                        <?php else: ?>
                            <?php echo htmlspecialchars($estatus_actual); ?>
                        <?php endif; ?>
                    </td>

                    <!-- Quién Realizó -->
                    <td class="col-quien_pago">
                        <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
                            <form method="POST" class="quien-realizo-form">
                                <input type="hidden" name="orden_id" value="<?php echo $orden['folio']; ?>">
                                <select name="quien_realizo_id" class="form-select quien-realizo-select" data-id="<?php echo $orden['folio']; ?>">
                                    <option value="">SN</option>
                                    <?php
                                    $usuarios = $conn->query("SELECT id, nombre FROM usuarios");
                                    while ($usuario = $usuarios->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $usuario['id']; ?>" <?php echo ($orden['quien_realizo_id'] == $usuario['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($usuario['nombre']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </form>
                        <?php else: ?>
                            <?php echo $orden['quien_realizo_id'] ? htmlspecialchars($orden['quien_realizo_id']) : 'SN'; ?>
                        <?php endif; ?>
                    </td>

                    <!-- Nivel -->
                    <td class="col-nivel">
                        <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
                            <form method="POST" class="nivel-form">
                                <input type="hidden" name="orden_id" value="<?php echo $orden['folio']; ?>">
                                <select name="nivel" class="form-select nivel-select" data-id="<?php echo $orden['folio']; ?>">
                                    <option value="Alto" <?php echo ($orden['nivel'] == 'Alto') ? 'selected' : ''; ?>>Alto</option>
                                    <option value="Medio" <?php echo ($orden['nivel'] == 'Medio') ? 'selected' : ''; ?>>Medio</option>
                                    <option value="Bajo" <?php echo ($orden['nivel'] == 'Bajo') ? 'selected' : ''; ?>>Bajo</option>
                                </select>
                            </form>
                        <?php else: ?>
                            <?php echo htmlspecialchars($orden['nivel']); ?>
                        <?php endif; ?>
                    </td>
                    <!-- Completar -->
                    <?php if ($es_superadmin || $es_admin): ?>
                  <td class="col-completar">
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalCompletarOrden" data-folio="<?php echo $orden['folio']; ?>">
                        Completar
                    </button>
                    </td>
                    <?php else: ?>
                        <td class="col-completar text-muted">N/A</td>
                    <?php endif; ?>
                    <!-- Fecha de Ejecución -->
<td class="col-fecha_completado">
    <?php echo isset($orden['fecha_completado']) && $orden['fecha_completado'] !== '' 
        ? htmlspecialchars($orden['fecha_completado']) 
        : '<span class="text-muted">—</span>'; ?>
</td>

<!-- Detalle -->
<td class="col-detalle_completado">
    <?php 
    if (!empty($orden['detalle_completado'])) {
        $detalle = wordwrap($orden['detalle_completado'], 80, "\n", true);
        $lineas = explode("\n", $detalle);
        $lineas = array_slice($lineas, 0, 5);
        echo nl2br(htmlspecialchars(implode("\n", $lineas)));
    } else {
        echo '<span class="text-muted">—</span>';
    }
    ?>
</td>

<!-- Foto Final -->
<td class="col-foto_completado">
    <?php if (isset($orden['foto_completado']) && !empty($orden['foto_completado'])): ?>
        <img src="<?php echo htmlspecialchars($orden['foto_completado']); ?>" style="max-width: 100px;">
    <?php else: ?>
        <span class="text-muted">Sin foto</span>
    <?php endif; ?>
</td>
<!-- Ponderación -->
<td class="col-ponderacion">
    <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
        <form method="POST" class="ponderacion-form">
            <input type="hidden" name="orden_id" value="<?php echo $orden['folio']; ?>">
            <select name="ponderacion" class="form-select ponderacion-select" data-id="<?php echo $orden['folio']; ?>">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo ($orden['ponderacion'] == $i) ? 'selected' : ''; ?>>
                        <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>
    <?php else: ?>
        <?php echo htmlspecialchars($orden['ponderacion'] ?? 1); ?>
    <?php endif; ?>
</td>
<td class="col-costo_final">
    <?php 
        echo isset($orden['costo_final']) && $orden['costo_final'] !== '' 
            ? '$' . number_format($orden['costo_final'], 2) 
            : '<span class="text-muted">—</span>'; 
    ?>
</td>

<td class="col-ver_pdf">
  <a href="generar_pdf_mantenimiento.php?folio=<?php echo $orden['folio']; ?>" target="_blank" class="btn btn-sm btn-outline-dark">Ver PDF</a>
</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <!-- Botón para cargar más órdenes -->
    <?php if ($pagina_actual * $registros_por_pagina < $ordenes_totales): ?>
        <div class="text-center mt-3">
            <button id="ver-mas" class="btn btn-primary" data-pagina="<?php echo $pagina_actual + 1; ?>">Ver Más</button>
        </div>
    <?php endif; ?>
</div>


    <!-- 98 MODAL: Agregar Usuario -->
<!-- 98 MODAL: Agregar Usuario -->
<div class="modal fade" id="modalAgregarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Agregar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoUsuario">
                <p class="text-center">Cargando...</p>
            </div>
        </div>
    </div>
</div>

<!-- 98 MODAL: Agregar Alojamiento -->
<div class="modal fade" id="modalAgregarAlojamiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Agregar Alojamiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoAlojamiento">
                <p class="text-center">Cargando...</p>
            </div>
        </div>
    </div>
</div>

<!-- 98 MODAL: Ingresar Reporte de Mantenimiento -->
<div class="modal fade" id="modalIngresarOrden" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Ingresar Reporte de Mantenimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoOrden">
                <p class="text-center">Cargando...</p>
            </div>
        </div>
    </div>
</div>
<div class="row g-2 mb-4">
    <div class="col-12 col-md-auto">
    <button id="btnExportarCSV" class="btn btn-dark btn-custom w-100">Exportar Resultados</button>
</div>

<div class="col-12 col-md-auto">
  <a id="btnExportarPDF" class="btn btn-danger btn-custom w-100" href="#">Exportar PDF</a>
</div>

    <div class="col-12 col-md-auto">
        <button class="btn btn-info btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalKPIs">Resumen de KPIs</button>
    </div>
    <div class="col-12 col-md-auto">
        <a href="kpis.php" class="btn btn-primary btn-custom w-100">Ver Detalles de KPIs</a>
    </div>
</div>

<!-- Modal de Resumen -->
<div class="modal fade" id="modalKPIs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Resumen de KPIs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="kpi-summary-content" class="text-center">
                    <p>Cargando resumen...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 98 Script para cargar los formularios en los modales -->

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Seleccionar todos los checkboxes de columnas
    document.querySelectorAll(".col-toggle").forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            let columnClass = ".col-" + this.dataset.col;
            let isChecked = this.checked;

            // Mostrar u ocultar la columna
            document.querySelectorAll(columnClass).forEach(function (col) {
                col.style.display = isChecked ? "" : "none";
            });
        });
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Detectar cambios en el select de estatus
    document.querySelectorAll(".estatus-select").forEach(select => {
        select.addEventListener("change", function () {
            let ordenId = this.dataset.id;
            let nuevoEstatus = this.value;

            fetch("actualizar_estatus_mantenimiento.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `orden_id=${encodeURIComponent(ordenId)}&estatus=${encodeURIComponent(nuevoEstatus)}`
            })
            .then(response => response.text())
            .then(data => {
                if (data === "ok") {
                    alert("Estatus actualizado correctamente.");
                } else {
                    alert("Error al actualizar el estatus.");
                }
            })
            .catch(error => alert("Error de conexin con el servidor."));
        });
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    function actualizarCampo(url, ordenId, campo, valor) {
        fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `orden_id=${encodeURIComponent(ordenId)}&${campo}=${encodeURIComponent(valor)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data === "ok") {
                alert(`${campo.replace("_", " ")} actualizado correctamente.`);
            } else {
                alert(`Error al actualizar ${campo.replace("_", " ")}.`);
            }
        })
        .catch(error => alert("Error de conexin con el servidor."));
    }

    // Quin Pag
    document.querySelectorAll(".quien-realizo-select").forEach(select => {
        select.addEventListener("change", function () {
            actualizarCampo("actualizar_quien_realizo_mantenimiento.php", this.dataset.id, "quien_realizo_id", this.value);
        });
    });

    // Nivel
    document.querySelectorAll(".nivel-select").forEach(select => {
        select.addEventListener("change", function () {
            actualizarCampo("actualizar_nivel_mantenimiento.php", this.dataset.id, "nivel", this.value);
        });
    });
});
// Ponderación
document.querySelectorAll(".ponderacion-select").forEach(select => {
    select.addEventListener("change", function () {
        const ordenId = this.dataset.id;
        const valor = this.value;

        fetch("actualizar_ponderacion.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `orden_id=${encodeURIComponent(ordenId)}&ponderacion=${encodeURIComponent(valor)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data === "ok") {
                alert("Ponderación actualizada correctamente.");
            } else {
                alert("Error al actualizar la ponderación.");
            }
        })
        .catch(error => alert("Error de conexión con el servidor."));
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const modalKPIs = document.getElementById("modalKPIs");
    
    modalKPIs.addEventListener("show.bs.modal", function () {
        fetch("kpis_summary.php")
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error en la peticin");
                }
                return response.json();
            })
            .then(data => {
                console.log("Respuesta JSON recibida:", data); // 73 Verifica en la consola
                document.getElementById("kpi-summary-content").innerHTML = `
                    <p><strong>Reportes de Mantenimiento Vencidas (Anual):</strong> $${data.foto_vencidas_anual}</p>
                    <p><strong>Reportes de Mantenimiento Vencidas (Mes):</strong> $${data.foto_vencidas_mes}</p>
                    <p><strong>Total de Reportes de Mantenimiento (Mes):</strong> $${data.foto_total_mes}</p>
                    <p><strong>% Ordenes Liquidadas (Mes):</strong> ${data.porcentaje_liquidadas_mes}%</p>
                `;
            })
            .catch(error => {
                console.error("Error cargando KPIs:", error);
                document.getElementById("kpi-summary-content").innerHTML = "<p class='text-danger'>Error al cargar los datos.</p>";
            });
    });
});

</script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        let botonVerMas = document.getElementById("ver-mas");

        if (botonVerMas) {
            botonVerMas.addEventListener("click", function () {
                let pagina = this.getAttribute("data-pagina");

                fetch("cargar_mantenimiento.php?pagina=" + pagina)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById("tabla-ordenes").insertAdjacentHTML('beforeend', data);
                        let nuevaPagina = parseInt(pagina) + 1;
                        botonVerMas.setAttribute("data-pagina", nuevaPagina);

                        if (data.trim() === "") {
                            botonVerMas.style.display = "none";
                        }
                    })
                    .catch(error => console.error("Error al cargar ms rdenes:", error));
            });
        }
    });
    </script>
<!-- 98 Guardar y restaurar la configuracin de columnas visibles -->
<script>
// 98 Guardar y restaurar la configuracin de columnas visibles
document.addEventListener("DOMContentLoaded", function () {
    const STORAGE_KEY = "column_visibility";
    
    function guardarConfiguracion() {
        const configuracion = {};
        document.querySelectorAll(".col-toggle").forEach(checkbox => {
            configuracion[checkbox.dataset.col] = checkbox.checked;
        });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(configuracion));
    }
    
    function restaurarConfiguracion() {
        const configuracionGuardada = localStorage.getItem(STORAGE_KEY);
        if (configuracionGuardada) {
            const configuracion = JSON.parse(configuracionGuardada);
            document.querySelectorAll(".col-toggle").forEach(checkbox => {
                if (configuracion.hasOwnProperty(checkbox.dataset.col)) {
                    checkbox.checked = configuracion[checkbox.dataset.col];
                    let columnClass = ".col-" + checkbox.dataset.col;
                    document.querySelectorAll(columnClass).forEach(col => {
                        col.style.display = configuracion[checkbox.dataset.col] ? "" : "none";
                    });
                }
            });
        }
    }
    
    // Restaurar configuracin al cargar la pgina
    restaurarConfiguracion();
    
    // Guardar configuracin al cambiar un checkbox
    document.querySelectorAll(".col-toggle").forEach(checkbox => {
        checkbox.addEventListener("change", function () {
            let columnClass = ".col-" + this.dataset.col;
            document.querySelectorAll(columnClass).forEach(col => {
                col.style.display = this.checked ? "" : "none";
            });
            guardarConfiguracion();
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    if (typeof $ === "undefined" || typeof $.fn.select2 === "undefined") {
        console.error("Select2 no est cargado correctamente.");
        return;
    }

    // Aplicar Select2 a selects mltiples
    $(".select2-multiple").select2({
        placeholder: "Seleccione una o ms opciones",
        allowClear: true,
        width: "100%",
        closeOnSelect: false,  // Mantener el men abierto en selecciones mltiples
        minimumInputLength: 1, // Requiere al menos 1 carcter para bsqueda
        matcher: function (params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            if (data.text.toLowerCase().includes(params.term.toLowerCase())) {
                return data;
            }
            return null;
        }
    });

    // Aplicar Select2 en el selector de estatus (sin mltiples selecciones)
    $(".select2-single").select2({
        placeholder: "Seleccione una opcin",
        allowClear: true,
        width: "100%"
    });
});


</script>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const modalCompletar = document.getElementById("modalCompletarOrden");

    modalCompletar.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        const folio = button.getAttribute("data-folio");

        fetch("completar_orden.php?folio=" + folio)
            .then(res => res.text())
            .then(html => {
                document.getElementById("contenidoCompletarOrden").innerHTML = html;
            });
    });

    modalCompletar.addEventListener("hidden.bs.modal", function () {
        document.getElementById("contenidoCompletarOrden").innerHTML = "<p class='text-center'>Cargando...</p>";
    });
});

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  var modales = ['modalIngresarOrden', 'modalAgregarUsuario', 'modalAgregarAlojamiento'];
  modales.forEach(function(id) {
    let modal = document.getElementById(id);
    if (modal) {
      modal.addEventListener("show.bs.modal", function () {
        console.log("Abriendo: " + id);
      });
    }
  });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  function cargarContenidoModal(modalId, url, contenedorId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.addEventListener("show.bs.modal", function () {
        fetch(url)
          .then(response => {
            if (!response.ok) throw new Error("Error al cargar modal");
            return response.text();
          })
          .then(data => document.getElementById(contenedorId).innerHTML = data)
          .catch(() => document.getElementById(contenedorId).innerHTML = "<p class='text-danger'>Error al cargar el formulario.</p>");
      });

      modal.addEventListener("hidden.bs.modal", function () {
        document.getElementById(contenedorId).innerHTML = "<p class='text-center'>Cargando...</p>";
      });
    }
  }

  cargarContenidoModal("modalAgregarUsuario", "usuarios.php?modal=1", "contenidoUsuario");
  cargarContenidoModal("modalAgregarAlojamiento", "alojamientos.php?modal=1", "contenidoAlojamiento");
  cargarContenidoModal("modalIngresarOrden", "ordenes_mantenimiento.php?modal=1", "contenidoOrden");
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const modalCompletar = document.getElementById("modalCompletarOrden");

    modalCompletar.addEventListener("shown.bs.modal", function () {
        const form = document.getElementById("formCompletarOrden");

        if (form) {
            form.addEventListener("submit", function (e) {
                e.preventDefault();
                const formData = new FormData(form);

                fetch("guardar_completado.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.text())
                .then(res => {
                    if (res.trim() === "ok") {
                        alert("Orden completada exitosamente.");
                        bootstrap.Modal.getInstance(modalCompletar).hide();
                        location.reload();
                    } else {
                        alert("Error: " + res);
                    }
                })
                .catch(() => {
                    alert("Ocurrió un error al enviar los datos.");
                });
            });
        } else {
            console.warn("No se encontró el formulario de completar.");
        }
    });
});
</script>

<div class="modal fade" id="modalCompletarOrden" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Completar Orden</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoCompletarOrden">
                <p class="text-center">Cargando...</p>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const tabla = document.querySelector("table");
    const columnas = document.getElementById("columnas-reordenables");

    if (columnas) {
        Sortable.create(columnas, {
            animation: 150,
            onEnd: function () {
                // Guarda orden de columnas en localStorage
                let orden = [];
                columnas.querySelectorAll("th").forEach(th => orden.push(th.className));
                localStorage.setItem("orden_columnas", JSON.stringify(orden));

                // Aplica el mismo orden al tbody
                let filas = tabla.querySelectorAll("tbody tr");
                filas.forEach(tr => {
                    let celdas = Array.from(tr.children);
                    let nuevoOrden = [];
                    orden.forEach(className => {
                        let celda = celdas.find(td => td.classList.contains(className));
                        if (celda) nuevoOrden.push(celda);
                    });
                    nuevoOrden.forEach(td => tr.appendChild(td));
                });
            }
        });

        // Restaurar orden desde localStorage
        let ordenGuardado = JSON.parse(localStorage.getItem("orden_columnas"));
        if (ordenGuardado && ordenGuardado.length > 0) {
            let ths = Array.from(columnas.children);
            let nuevoOrden = [];
            ordenGuardado.forEach(className => {
                let th = ths.find(el => el.classList.contains(className));
                if (th) nuevoOrden.push(th);
            });
            nuevoOrden.forEach(th => columnas.appendChild(th));

            let filas = tabla.querySelectorAll("tbody tr");
            filas.forEach(tr => {
                let celdas = Array.from(tr.children);
                let nuevoOrden = [];
                ordenGuardado.forEach(className => {
                    let celda = celdas.find(td => td.classList.contains(className));
                    if (celda) nuevoOrden.push(celda);
                });
                nuevoOrden.forEach(td => tr.appendChild(td));
            });
        }
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const btnExportarPDF = document.getElementById("btnExportarPDF");
    btnExportarPDF.addEventListener("click", function () {
        // Detecta columnas visibles y ordenadas
        let columnasVisibles = [];
        document.querySelectorAll("thead tr th").forEach(th => {
            const clase = th.className.trim();
            if (clase && th.offsetParent !== null) {
                columnasVisibles.push(clase.replace("col-", ""));
            }
        });

        // Construye URL con filtros + columnas
        const filtros = new URLSearchParams(window.location.search);
filtros.set("columnas", columnasVisibles.join(","));

// También incluir orden y dirección si existen
const thOrden = document.querySelector("thead th a[href*='orden=']");
if (thOrden) {
    const urlOrden = new URL(thOrden.href);
    const orden = urlOrden.searchParams.get("orden");
    const dir = urlOrden.searchParams.get("dir");
    if (orden) filtros.set("orden", orden);
    if (dir) filtros.set("dir", dir);
}

        window.open("exportar_mantenimiento_pdf.php?" + filtros.toString(), "_blank");
    });
});
document.addEventListener("DOMContentLoaded", function () {
    const btnExportarCSV = document.getElementById("btnExportarCSV");
    btnExportarCSV.addEventListener("click", function () {
        let columnasVisibles = [];
        document.querySelectorAll("thead tr th").forEach(th => {
            const clase = th.className.trim();
            if (clase && th.offsetParent !== null) {
                columnasVisibles.push(clase.replace("col-", ""));
            }
        });

        const filtros = new URLSearchParams(window.location.search);
        filtros.set("columnas", columnasVisibles.join(","));

        // Detectar orden y dirección
        const thOrden = document.querySelector("thead th a[href*='orden=']");
        if (thOrden) {
            const urlOrden = new URL(thOrden.href);
            const orden = urlOrden.searchParams.get("orden");
            const dir = urlOrden.searchParams.get("dir");
            if (orden) filtros.set("orden", orden);
            if (dir) filtros.set("dir", dir);
        }

        window.open("exportar_mantenimiento.php?" + filtros.toString(), "_blank");
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    function aplicarFiltroRapido(estatus) {
        const params = new URLSearchParams(window.location.search);

        // Mantener filtros actuales
        const usuario = document.querySelector("#usuario");
        const unidad = document.querySelector("#unidad_negocio");
        const alojamiento = document.querySelector("#alojamiento");

        // Obtener valores seleccionados
        if (usuario) {
            const seleccionados = Array.from(usuario.selectedOptions).map(opt => opt.value).filter(v => v);
            seleccionados.forEach(val => params.append("usuario[]", val));
        }

        if (unidad) {
            const seleccionados = Array.from(unidad.selectedOptions).map(opt => opt.value).filter(v => v);
            seleccionados.forEach(val => params.append("unidad_negocio[]", val));
        }

        if (alojamiento) {
            const seleccionados = Array.from(alojamiento.selectedOptions).map(opt => opt.value).filter(v => v);
            seleccionados.forEach(val => params.append("alojamiento[]", val));
        }

        params.set("estatus", estatus);
        window.location.href = "minipanel_mantenimiento.php?" + params.toString();
    }

    document.getElementById("btnFiltroPendientes").addEventListener("click", function () {
        aplicarFiltroRapido("Pendiente");
    });

    document.getElementById("btnFiltroTerminados").addEventListener("click", function () {
        aplicarFiltroRapido("Terminado");
    });
});
</script>
</body>
</html>
