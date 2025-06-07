<?php
	session_start();
	include 'auth.php'; // Protección de sesión
	include 'conexion.php'; // Ahora usa el archivo de conexión
	header('Content-Type: text/html; charset=utf-8');
	
	
	// 📌 KPIs
	$ordenes_totales = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra")->fetch_assoc()['total'];
	$ordenes_pagadas = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE estatus_pago = 'Pagado'")->fetch_assoc()['total'];
	$ordenes_por_liquidar = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE estatus_pago = 'Por pagar'")->fetch_assoc()['total'];
	$ordenes_vencidas = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE estatus_pago = 'Vencido'")->fetch_assoc()['total'];
	
	// 📌 Paginación
	$registros_por_pagina = 500;
	$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
	$offset = ($pagina_actual - 1) * $registros_por_pagina;
	
	// 📌 Construcción de la consulta con filtros dinámicos
	$query = "SELECT 
    oc.folio, oc.monto, oc.vencimiento_pago, oc.concepto_pago, oc.tipo_pago, 
    oc.genera_factura, oc.estatus_pago, oc.quien_pago_id, oc.nivel,
    p.nombre AS proveedor, 
    u.nombre AS usuario,
    un.nombre AS unidad_negocio,
    c.id AS compra_id, c.monto_total,
    nc.monto AS monto_nc
    FROM ordenes_compra oc
    LEFT JOIN proveedores p ON oc.proveedor_id = p.id
    LEFT JOIN usuarios u ON oc.usuario_solicitante_id = u.id
    LEFT JOIN unidades_negocio un ON oc.unidad_negocio_id = un.id
    LEFT JOIN compras c ON c.orden_id = oc.folio
    LEFT JOIN notas_credito nc ON nc.compra_id = c.id
        WHERE 1=1";
	// 📌 Verificar y actualizar automáticamente órdenes vencidas
	$conn->query("UPDATE ordenes_compra SET estatus_pago = 'Vencido' WHERE vencimiento_pago < CURDATE() AND estatus_pago != 'Pagado' AND estatus_pago != 'Cancelado'");
	
	// Aplicar filtros dinámicos
	if (!empty($_GET['proveedor']) && is_array($_GET['proveedor'])) {
	    $proveedores_ids = array_map('intval', $_GET['proveedor']); // Asegurar que son enteros
	    $proveedores_ids_str = implode(',', $proveedores_ids); // Convertir array en string separado por comas
	    $query .= " AND proveedor_id IN ($proveedores_ids_str)";
	}
	
	
	if (!empty($_GET['estatus'])) {
	    $estatus = $conn->real_escape_string($_GET['estatus']);
	    $query .= " AND estatus_pago = '$estatus'";
	}
	if (!empty($_GET['usuario'])) {
	    $usuario_id = (int) $_GET['usuario'];
	    $query .= " AND usuario_solicitante_id = $usuario_id";
	}
	if (!empty($_GET['unidad_negocio'])) {
	    $unidad_negocio_id = (int) $_GET['unidad_negocio'];
	    $query .= " AND unidad_negocio_id = $unidad_negocio_id";
	}
	if (!empty($_GET['fecha_inicio'])) {
	    $fecha_inicio = $conn->real_escape_string($_GET['fecha_inicio']);
	    $query .= " AND vencimiento_pago >= '$fecha_inicio'";
	}
	if (!empty($_GET['fecha_fin'])) {
	    $fecha_fin = $conn->real_escape_string($_GET['fecha_fin']);
	    $query .= " AND vencimiento_pago <= '$fecha_fin'";
	}
	
	$query .= " LIMIT $registros_por_pagina OFFSET $offset";
	$ordenes = $conn->query($query);
	
	// 📌 Obtener el total de registros para la paginación
	$total_ordenes = $conn->query("SELECT COUNT(*) AS total FROM ordenes_compra WHERE 1=1")->fetch_assoc()['total'];
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
	        .btn-custom {
	            min-width: 150px;
	            font-size: 0.9rem;
	        }
	
	        /* Scroll para tabla */
	        .table-responsive { overflow-x: auto; }
	        th, td { white-space: nowrap; }
	
	        /* Personalización de etiquetas del dropdown */
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
	    @media (max-width: 576px) {
	        .btn-custom {
	            font-size: 0.85rem;
	            padding: 0.5rem 0.8rem;
	        }
	    }
	        
	        .table-responsive { overflow-x: auto; }  /* 🔥 Arreglo para móviles */
	
	    </style>
	    <style>
            .modal .form-control,
            .modal .form-select,
            .modal textarea,
            .modal button {
                display: block;
                width: 100%;
                margin-bottom: 1rem;
            }
        </style>
	    <!-- Bootstrap 5 -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- jQuery -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	
	<!-- Select2 CSS y JS -->
	<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
	
	
	
	</head>
	<body class="bg-light">
	    <!-- Barra de navegación -->
	    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
	        <div class="container">
	            <a class="navbar-brand" href="#">Control de Gastos</a>
	            <div>
	                <span class="me-3">Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
	                <a href="admin_panel.php" class="btn btn-secondary btn-sm">Panel de Administracion</a>
	                <a href="panel_config.php" class="btn btn-secundary btn-sm">Configuración</a>
	                <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesion</a>
	            </div>
	        </div>
	        
	    </nav>
	    <div class="container mt-5">
	    <!-- Botones principales -->
<div class="row g-2 mb-4">
    <?php if ($_SESSION['user_role'] === 'superadmin'): ?>
        <div class="col-12 col-md-auto">
            <button class="btn btn-primary btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">Agregar Usuario</button>
        </div>
        <div class="col-12 col-md-auto">
            <button class="btn btn-secondary btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalAgregarProveedor">Agregar Proveedor</button>
        </div>
    <?php endif; ?>
    <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
        <div class="col-12 col-md-auto">
            <button class="btn btn-success btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalIngresarOrden">Ingresar Orden de Compra</button>
        </div>
        <div class="col-12 col-md-auto">
            <button class="btn btn-outline-success btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalAgregarCompra">Agregar Compra</button>
        </div>
        <div class="col-12 col-md-auto">
            <button class="btn btn-warning btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalAgregarNota">Agregar Nota de Crédito</button>
        </div>
    <?php endif; ?>
    <div class="col-12 col-md-auto">
        <button class="btn btn-info btn-custom w-100" data-bs-toggle="modal" data-bs-target="#modalKPIs">Resumen de KPIs</button>
    </div>
    <div class="col-12 col-md-auto">
        <a href="kpis.php" class="btn btn-primary btn-custom w-100">Ver Detalles de KPIs</a>
    </div>
</div>
	
	        <h4 class="mb-3">Ordenes de Compra</h4>

	
	<!-- Filtros en Acordeón -->
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
	                        <!-- Proveedor -->
	                        <div class="col-12 col-md-4">
	                        <label for="proveedor" class="form-label">Proveedor</label>
	                       <select class="form-select select2-multiple" id="proveedor" name="proveedor[]" multiple="multiple">
	                       <option value="">Seleccione proveedores</option> <!-- 🔥 IMPORTANTE: Placeholder similar a estatus -->
	                        <?php
	                       $proveedores = $conn->query("SELECT id, nombre FROM proveedores");
	                       while ($proveedor = $proveedores->fetch_assoc()):
	                       $selected = (isset($_GET['proveedor']) && is_array($_GET['proveedor']) && in_array($proveedor['id'], $_GET['proveedor'])) ? 'selected' : '';
	                        ?>
	                        <option value="<?php echo htmlspecialchars($proveedor['id']); ?>" <?php echo $selected; ?>>
	                       <?php echo htmlspecialchars($proveedor['nombre']); ?>
	                      </option>
	                       <?php endwhile; ?>
	                       </select>
	                    </div>
	
	                        <!-- Estatus -->
	                        <div class="col-12 col-md-4">
	                            <label for="estatus" class="form-label">Estatus de Pago</label>
	                            <select class="form-select select2-single" id="estatus" name="estatus">
	                                <option value="">Todos</option>
	                                <option value="Por pagar" <?php echo (isset($_GET['estatus']) && $_GET['estatus'] == 'Por pagar') ? 'selected' : ''; ?>>Por pagar</option>
	                                <option value="Pagado" <?php echo (isset($_GET['estatus']) && $_GET['estatus'] == 'Pagado') ? 'selected' : ''; ?>>Pagado</option>
	                                <option value="Vencido" <?php echo (isset($_GET['estatus']) && $_GET['estatus'] == 'Vencido') ? 'selected' : ''; ?>>Vencido</option>
	                                <option value="Pago parcial" <?php echo (isset($_GET['estatus']) && $_GET['estatus'] == 'Pago parcial') ? 'selected' : ''; ?>>Pago parcial</option>
	                                <option value="Cancelado" <?php echo (isset($_GET['estatus']) && $_GET['estatus'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
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
	
	                    <!-- Botón de Filtrar -->
	                    <!-- Botón de Filtrar y Limpiar -->
<div class="text-end mt-3">
    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
    <a href="minipanel.php" class="btn btn-outline-secondary ms-2">Limpiar Filtros</a>
</div>
	                </form>
	            </div>
	        </div>
	    </div>
	</div>
	
	    <!-- 📌 Menú de selección de columnas -->
	    <div class="dropdown mb-3">
	    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
	        Columnas
	    </button>
	    <ul class="dropdown-menu">
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="folio"> Folio</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="proveedor"> Proveedor</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="monto"> Monto</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="vencimiento"> Vencimiento</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="concepto"> Concepto</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="tipo"> Tipo</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="factura"> Factura</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="usuario"> Usuario</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="unidad_negocio"> Unidad de Negocio</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="estatus"> Estatus</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="quien_pago"> Quien Pago</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="nivel"> Nivel</label></li>
	        <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="compra"> Compra</label></li>
            <li><label class="dropdown-item"><input type="checkbox" checked class="col-toggle" data-col="nota"> Nota de Crédito</label></li>
	    </ul>
	</div> 
	
	<div class="row g-2 mb-3">
    <div class="col-12 col-md-auto">
        <a href="minipanel.php?estatus=Por pagar" class="btn btn-outline-warning btn-custom w-100">Por pagar</a>
    </div>
    <div class="col-12 col-md-auto">
        <a href="minipanel.php?estatus=Pagado" class="btn btn-outline-success btn-custom w-100">Pagadas</a>
    </div>
    <div class="col-12 col-md-auto">
        <a href="minipanel.php?estatus=Vencido" class="btn btn-outline-danger btn-custom w-100">Vencidas</a>
    </div>
</div>

	
	
	<!-- 📌 Tabla de Órdenes de Compra -->
	    <div class="table-responsive">
	            <table class="table table-striped table-sm">
	        <thead>
	            <tr>
	                <th class="col-folio">Folio</th>
	                <th class="col-proveedor">Proveedor</th>
	                <th class="col-monto">Monto</th>
	                <th class="col-vencimiento">Vencimiento</th>
	                <th class="col-concepto">Concepto</th>
	                <th class="col-tipo">Tipo</th>
	                <th class="col-factura">Factura</th>
	                <th class="col-usuario">Usuario</th>
	                <th class="col-unidad_negocio">Unidad de Negocio</th>
	                <th class="col-estatus text-center" style="min-width: 180px;">Estatus</th>
	                <th class="col-quien_pago">Quién Pagó</th>
	                <th class="col-nivel">Nivel</th>
	                <th class="col-compra">Compra</th>
                    <th class="col-nota">Nota de Crédito</th>
                    <th class="col-oc">OC</th>
                    <th class="col-compra">Gasto</th>
                    <th class="col-nc">NC</th>

	            </tr>
	        </thead>
<tbody id="tabla-ordenes">
<?php while ($orden = $ordenes->fetch_assoc()): ?>
<tr>
    <td class="col-folio"><?php echo htmlspecialchars($orden['folio']); ?></td>
    <td class="col-proveedor"><?php echo htmlspecialchars($orden['proveedor']); ?></td>
    <td class="col-monto">$<?php echo number_format($orden['monto'], 2); ?></td>
    <td class="col-vencimiento"><?php echo htmlspecialchars($orden['vencimiento_pago']); ?></td>
    <td class="col-concepto"><?php echo htmlspecialchars($orden['concepto_pago']); ?></td>
    <td class="col-tipo"><?php echo htmlspecialchars($orden['tipo_pago']); ?></td>
    <td class="col-factura"><?php echo htmlspecialchars($orden['genera_factura']); ?></td>
    <td class="col-usuario"><?php echo htmlspecialchars($orden['usuario']); ?></td>
    <td class="col-unidad_negocio"><?php echo htmlspecialchars($orden['unidad_negocio']); ?></td>

    <td class="col-estatus text-center">
        <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
            <form method="POST" class="estatus-form">
                <input type="hidden" name="orden_id" value="<?php echo $orden['folio']; ?>">
                <select name="estatus_pago" class="form-select estatus-select" data-id="<?php echo $orden['folio']; ?>">
                    <option value="Por pagar" <?php if ($orden['estatus_pago'] == 'Por pagar') echo 'selected'; ?>>Por pagar</option>
                    <option value="Pagado" <?php if ($orden['estatus_pago'] == 'Pagado') echo 'selected'; ?>>Pagado</option>
                    <option value="Vencido" <?php if ($orden['estatus_pago'] == 'Vencido') echo 'selected'; ?>>Vencido</option>
                    <option value="Pago parcial" <?php if ($orden['estatus_pago'] == 'Pago parcial') echo 'selected'; ?>>Pago parcial</option>
                    <option value="Cancelado" <?php if ($orden['estatus_pago'] == 'Cancelado') echo 'selected'; ?>>Cancelado</option>
                    <option value="Nota de credito abierta" <?php if ($orden['estatus_pago'] == 'Nota de credito abierta') echo 'selected'; ?>>Nota de credito abierta</option>
                </select>
            </form>
        <?php else: ?>
            <?php echo htmlspecialchars($orden['estatus_pago']); ?>
        <?php endif; ?>
    </td>

    <td class="col-quien_pago">
        <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
            <form method="POST" class="quien-pago-form">
                <input type="hidden" name="orden_id" value="<?php echo $orden['folio']; ?>">
                <select name="quien_pago_id" class="form-select quien-pago-select" data-id="<?php echo $orden['folio']; ?>">
                    <option value="">SN</option>
                    <?php
                    $usuarios = $conn->query("SELECT id, nombre FROM usuarios");
                    while ($usuario = $usuarios->fetch_assoc()):
                    ?>
                        <option value="<?php echo $usuario['id']; ?>" <?php echo ($orden['quien_pago_id'] == $usuario['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($usuario['nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        <?php else: ?>
            <?php echo $orden['quien_pago_id'] ? htmlspecialchars($orden['quien_pago_id']) : 'SN'; ?>
        <?php endif; ?>
    </td>

    <td class="col-nivel">
        <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
            <form method="POST" class="nivel-form">
                <input type="hidden" name="orden_id" value="<?php echo $orden['folio']; ?>">
                <select name="nivel" class="form-select nivel-select" data-id="<?php echo $orden['folio']; ?>">
                    <option value="Alto" <?php if ($orden['nivel'] == 'Alto') echo 'selected'; ?>>Alto</option>
                    <option value="Medio" <?php if ($orden['nivel'] == 'Medio') echo 'selected'; ?>>Medio</option>
                    <option value="Bajo" <?php if ($orden['nivel'] == 'Bajo') echo 'selected'; ?>>Bajo</option>
                </select>
            </form>
        <?php else: ?>
            <?php echo htmlspecialchars($orden['nivel']); ?>
        <?php endif; ?>
    </td>

    <!-- Columna: Compra asociada -->
<td class="col-compra">
    <?php
    echo isset($orden['compra_id'])
        ? "Compra #" . $orden['compra_id'] . "<br><strong>$" . number_format($orden['monto_total'], 2) . "</strong>"
        : '<span class="text-muted">—</span>';
    ?>
</td>

    <!-- Columna: Nota de crédito asociada -->
    <td class="col-nota">
    <?php
    echo isset($orden['monto_nc']) && $orden['monto_nc'] !== null
        ? "<strong>$" . number_format($orden['monto_nc'], 2) . "</strong>"
        : '<span class="text-muted">—</span>';
    ?>
</td>

<!-- OC -->
<td class="col-oc"><?php echo htmlspecialchars($orden['folio']); ?></td>

<!-- Gasto (Compra) -->
<td class="col-compra">
    <?php
    echo isset($orden['compra_id'])
        ? "Compra #" . $orden['compra_id'] . "<br><strong>$" . number_format($orden['monto_total'], 2) . "</strong>"
        : '<span class="text-muted">�</span>';
    ?>
</td>

<!-- NC (Nota de Cr�dito) -->
<td class="col-nc">
    <?php
    echo isset($orden['monto_nc']) && $orden['monto_nc'] !== null
        ? "<strong>$" . number_format($orden['monto_nc'], 2) . "</strong>"
        : '<span class="text-muted">�</span>';
    ?>
</td>


</tr>
<?php endwhile; ?>
</tbody>	    </table>
	    <!-- Botón para cargar más órdenes -->
	        <?php if ($pagina_actual * $registros_por_pagina < $ordenes_totales): ?>
	            <div class="text-center mt-3">
	                <button id="ver-mas" class="btn btn-primary" data-pagina="<?php echo $pagina_actual + 1; ?>">Ver Más</button>
	            </div>
	        <?php endif; ?>
	</div>
	
	    <!-- 📌 MODAL: Agregar Usuario -->
	<!-- 📌 MODAL: Agregar Usuario -->
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
	
	<!-- 📌 MODAL: Agregar Proveedor -->
	<div class="modal fade" id="modalAgregarProveedor" tabindex="-1" aria-hidden="true">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <div class="modal-header bg-warning text-dark">
	                <h5 class="modal-title">Agregar Proveedor</h5>
	                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
	            </div>
	            <div class="modal-body" id="contenidoProveedor">
	                <p class="text-center">Cargando...</p>
	            </div>
	        </div>
	    </div>
	</div>
	
	<!-- 📌 MODAL: Ingresar Orden de Compra -->
	<div class="modal fade" id="modalIngresarOrden" tabindex="-1" aria-hidden="true">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <div class="modal-header bg-success text-white">
	                <h5 class="modal-title">Ingresar Orden de Compra</h5>
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
<!-- MODAL: Agregar Compra -->
<div class="modal fade" id="modalAgregarCompra" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Agregar Compra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="contenidoCompra">
        <p class="text-center">Cargando...</p>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Agregar Nota de Crédito -->
<div class="modal fade" id="modalAgregarNota" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Agregar Nota de Crédito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="contenidoNota">
        <p class="text-center">Cargando...</p>
      </div>
    </div>
  </div>
</div>	
	<!-- 📌 Script para cargar los formularios en los modales -->
	<script>
	document.addEventListener("DOMContentLoaded", function () {
	    // Función para cargar contenido en el modal con fetch
	    function cargarContenidoModal(modalId, url, contenidoId) {
	        let modal = document.getElementById(modalId);
	        modal.addEventListener("show.bs.modal", function () {
	            fetch(url)
	                .then(response => {
	                    if (!response.ok) {
	                        throw new Error("Error al cargar el formulario.");
	                    }
	                    return response.text();
	                })
	                .then(data => document.getElementById(contenidoId).innerHTML = data)
	                .catch(error => document.getElementById(contenidoId).innerHTML = "<p class='text-danger'>No se pudo cargar el formulario.</p>");
	        });
	
	        modal.addEventListener("hidden.bs.modal", function () {
	            document.getElementById(contenidoId).innerHTML = "<p class='text-center'>Cargando...</p>";
	        });
	    }
	
	    // Cargar contenido de los modales al abrirlos
	    cargarContenidoModal("modalAgregarUsuario", "usuarios.php?modal=1", "contenidoUsuario");
	    cargarContenidoModal("modalAgregarProveedor", "proveedores.php?modal=1", "contenidoProveedor");
	    cargarContenidoModal("modalIngresarOrden", "ordenes_compra.php?modal=1", "contenidoOrden");
	    cargarContenidoModal("modalAgregarCompra", "compras.php?modal=1", "contenidoCompra");
cargarContenidoModal("modalAgregarNota", "notas_credito.php?modal=1", "contenidoNota");
	});
	</script>
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
	
	            fetch("actualizar_estatus.php", {
	                method: "POST",
	                headers: { "Content-Type": "application/x-www-form-urlencoded" },
	                body: `orden_id=${encodeURIComponent(ordenId)}&estatus_pago=${encodeURIComponent(nuevoEstatus)}`
	            })
	            .then(response => response.text())
	            .then(data => {
	                if (data === "ok") {
	                    alert("Estatus actualizado correctamente.");
	                } else {
	                    alert("Error al actualizar el estatus.");
	                }
	            })
	            .catch(error => alert("Error de conexión con el servidor."));
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
	        .catch(error => alert("Error de conexión con el servidor."));
	    }
	
	    // Quién Pagó
	    document.querySelectorAll(".quien-pago-select").forEach(select => {
	        select.addEventListener("change", function () {
	            actualizarCampo("actualizar_quien_pago.php", this.dataset.id, "quien_pago_id", this.value);
	        });
	    });
	
	    // Nivel
	    document.querySelectorAll(".nivel-select").forEach(select => {
	        select.addEventListener("change", function () {
	            actualizarCampo("actualizar_nivel.php", this.dataset.id, "nivel", this.value);
	        });
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
	                    throw new Error("Error en la petición");
	                }
	                return response.json();
	            })
	            .then(data => {
	                console.log("Respuesta JSON recibida:", data); // ✅ Verifica en la consola
	                document.getElementById("kpi-summary-content").innerHTML = `
	                    <p><strong>Ordenes de Compra Vencidas (Anual):</strong> $${data.monto_vencidas_anual}</p>
	                    <p><strong>Ordenes de Compra Vencidas (Mes):</strong> $${data.monto_vencidas_mes}</p>
	                    <p><strong>Total de Ordenes de Compra (Mes):</strong> $${data.monto_total_mes}</p>
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
let params = new URLSearchParams(window.location.search);
params.set("pagina", pagina);

fetch("cargar_ordenes.php?" + params.toString())	                    .then(response => response.text())
	                    .then(data => {
	                        document.getElementById("tabla-ordenes").insertAdjacentHTML('beforeend', data);
	                        let nuevaPagina = parseInt(pagina) + 1;
	                        botonVerMas.setAttribute("data-pagina", nuevaPagina);
	
	                        if (data.trim() === "") {
	                            botonVerMas.style.display = "none";
	                        }
	                    })
	                    .catch(error => console.error("Error al cargar más órdenes:", error));
	            });
	        }
	    });
	    </script>
	<!-- 📌 Guardar y restaurar la configuración de columnas visibles -->
	<script>
	// 📌 Guardar y restaurar la configuración de columnas visibles
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
	    
	    // Restaurar configuración al cargar la página
	    restaurarConfiguracion();
	    
	    // Guardar configuración al cambiar un checkbox
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
	        console.error("Select2 no está cargado correctamente.");
	        return;
	    }
	
	    // Aplicar Select2 a selects múltiples
	    $(".select2-multiple").select2({
	        placeholder: "Seleccione una o más opciones",
	        allowClear: true,
	        width: "100%",
	        closeOnSelect: false,  // Mantener el menú abierto en selecciones múltiples
	        minimumInputLength: 1, // Requiere al menos 1 carácter para búsqueda
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
	
	    // Aplicar Select2 en el selector de estatus (sin múltiples selecciones)
	    $(".select2-single").select2({
	        placeholder: "Seleccione una opcion",
	        allowClear: true,
	        width: "100%"
	    });
	});
	
	
	</script>
	
	<!-- Bootstrap JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	</body>
	</html>
	