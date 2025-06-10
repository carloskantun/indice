<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
	
	                    <!-- Bot¨®n de Filtrar -->
	                    <!-- Bot¨®n de Filtrar y Limpiar -->
<div class="text-end mt-3">
    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
    <a href="minipanel.php" class="btn btn-outline-secondary ms-2">Limpiar Filtros</a>
</div>
	                </form>
	            </div>
	        </div>
	    </div>
	</div>
	
	    <!-- ”9Ý8 Men¨² de selecci¨®n de columnas -->
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
	    </ul>
	</div> 
	
	
	
	
	<!-- ”9Ý8 Tabla de 0ˆ7rdenes de Compra -->
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
	                <th class="col-quien_pago">Qui¨¦n Pag¨®</th>
	                <th class="col-nivel">Nivel</th>
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
	                    <td class="col-estatus text-center" style="word-wrap: break-word; white-space: normal;">
	                        <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
	                        <form method="POST" class="estatus-form">
	                            <input type="hidden" name="orden_id" value="<?php echo $orden['folio']; ?>">
	                            <select name="estatus_pago" class="form-select estatus-select" data-id="<?php echo $orden['folio']; ?>">
	                                <option value="Por pagar" <?php echo ($orden['estatus_pago'] == 'Por pagar') ? 'selected' : ''; ?>>Por pagar</option>
	                                <option value="Pagado" <?php echo ($orden['estatus_pago'] == 'Pagado') ? 'selected' : ''; ?>>Pagado</option>
	                                <option value="Vencido" <?php echo ($orden['estatus_pago'] == 'Vencido') ? 'selected' : ''; ?>>Vencido</option>
	                                <option value="Pago parcial" <?php echo ($orden['estatus_pago'] == 'Pago parcial') ? 'selected' : ''; ?>>Pago parcial</option>
	                                <option value="Cancelado" <?php echo ($orden['estatus_pago'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
	                                <option value="Nota de cr¨¦dito abierta" <?php echo ($orden['estatus_pago'] == 'Nota de cr¨¦dito abierta') ? 'selected' : ''; ?>>Nota de cr¨¦dito abierta</option>
	                                </select>
	                        </form>
	                    <?php else: ?>
	                    <?php echo htmlspecialchars($orden['estatus_pago']); ?>
	                    <?php endif; ?>
	                </td>
	
	                <!-- •0•0„1‚5 Qui¨¦n Pag¨® -->
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
	        <?php echo $orden['quien_pago_id'] ? htmlspecialchars($usuarios[$orden['quien_pago_id']]) : 'SN'; ?>
	    <?php endif; ?>
	    </td>
	
	    <!-- •0•0„1‚5 Nivel -->
	    <td class="col-nivel">
	    <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
	        <form method="POST" class="nivel-form">
	            <input type="hidden" name="orden_id" value="<?php echo $orden['folio']; ?>">
	            <select name="nivel" class="form-select nivel-select" data-id="<?php echo $orden['folio']; ?>">
	                <option value="Medio" <?php echo ($orden['nivel'] == 'Medio') ? 'selected' : ''; ?>>Medio</option>
	                <option value="Alto" <?php echo ($orden['nivel'] == 'Alto') ? 'selected' : ''; ?>>Alto</option>
	                <option value="Bajo" <?php echo ($orden['nivel'] == 'Bajo') ? 'selected' : ''; ?>>Bajo</option>
	            </select>
	        </form>
	    <?php else: ?>
	        <?php echo htmlspecialchars($orden['nivel']); ?>
	    <?php endif; ?>
	    </td>
	
	                </tr>
	            <?php endwhile; ?>
	        </tbody>
	    </table>
	    <!-- Bot¨®n para cargar m¨¢s ¨®rdenes -->
	        <?php if ($pagina_actual * $registros_por_pagina < $ordenes_totales): ?>
	            <div class="text-center mt-3">
	                <button id="ver-mas" class="btn btn-primary" data-pagina="<?php echo $pagina_actual + 1; ?>">Ver M¨¢s</button>
	            </div>
	        <?php endif; ?>
	</div>
	
	    <!-- ”9Ý8 MODAL: Agregar Usuario -->
	<!-- ”9Ý8 MODAL: Agregar Usuario -->
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
	
	<!-- ”9Ý8 MODAL: Agregar Proveedor -->
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
	
	<!-- ”9Ý8 MODAL: Ingresar Orden de Compra -->
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

<!-- MODAL: Agregar Nota de Cr¨¦dito -->
<div class="modal fade" id="modalAgregarNota" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Agregar Nota de Cr¨¦dito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="contenidoNota">
        <p class="text-center">Cargando...</p>
      </div>
    </div>
  </div>
</div>	
	<!-- ”9Ý8 Script para cargar los formularios en los modales -->
	<script>
	document.addEventListener("DOMContentLoaded", function () {
	    // Funci¨®n para cargar contenido en el modal con fetch
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
	            .catch(error => alert("Error de conexi¨®n con el servidor."));
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
	        .catch(error => alert("Error de conexi¨®n con el servidor."));
	    }
	
	    // Qui¨¦n Pag¨®
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
	                    throw new Error("Error en la petici¨®n");
	                }
	                return response.json();
	            })
	            .then(data => {
	                console.log("Respuesta JSON recibida:", data); // 7¼3 Verifica en la consola
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
	                    .catch(error => console.error("Error al cargar m¨¢s ¨®rdenes:", error));
	            });
	        }
	    });
	    </script>
	<!-- ”9Ý8 Guardar y restaurar la configuraci¨®n de columnas visibles -->
	<script>
	// ”9Ý8 Guardar y restaurar la configuraci¨®n de columnas visibles
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
	    
	    // Restaurar configuraci¨®n al cargar la p¨¢gina
	    restaurarConfiguracion();
	    
	    // Guardar configuraci¨®n al cambiar un checkbox
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
	        console.error("Select2 no est¨¢ cargado correctamente.");
	        return;
	    }
	
	    // Aplicar Select2 a selects m¨²ltiples
	    $(".select2-multiple").select2({
	        placeholder: "Seleccione una o m¨¢s opciones",
	        allowClear: true,
	        width: "100%",
	        closeOnSelect: false,  // Mantener el men¨² abierto en selecciones m¨²ltiples
	        minimumInputLength: 1, // Requiere al menos 1 car¨¢cter para b¨²squeda
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
	
	    // Aplicar Select2 en el selector de estatus (sin m¨²ltiples selecciones)
	    $(".select2-single").select2({
	        placeholder: "Seleccione una opci¨®n",
	        allowClear: true,
	        width: "100%"
	    });
	});
	
	
	</script>
	
	<!-- Bootstrap JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	</body>
	</html>
	