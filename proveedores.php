<?php
session_start();
include 'auth.php';
include 'conexion.php';

if (isset($_GET['modal'])) {
    // 🔹 SOLO el formulario cuando se llama como modal
    ?>
    <form action="procesar_proveedor.php" method="POST">
        <div class="mb-3">
            <label>Nombre del Proveedor</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Persona Responsable</label>
            <input type="text" name="persona_responsable" class="form-control">
        </div>
        <div class="mb-3">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email (Opcional)</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
            <label>CLABE Interbancaria</label>
            <input type="text" name="clabe_interbancaria" class="form-control">
        </div>
        <div class="mb-3">
            <label>No. Cuenta</label>
            <input type="text" name="numero_cuenta" class="form-control">
        </div>
        <div class="mb-3">
            <label>Banco</label>
            <input type="text" name="banco" class="form-control">
        </div>
        <div class="mb-3">
            <label>Dirección</label>
            <textarea name="direccion" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>RFC (Opcional)</label>
            <input type="text" name="rfc" class="form-control">
        </div>
        <div class="mb-3">
            <label>Descripción del Servicio</label>
            <textarea name="descripcion_servicio" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-warning w-100">Guardar</button>
    </form>
    <?php
    exit;
}

// 🔸 Si NO es modal: mostrar la vista completa
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proveedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Lista de Proveedores</h2>
    <a href="menu_principal.php" class="btn btn-outline-primary btn-sm mb-3">Volver al menú</a>
    <a href="#" class="btn btn-warning btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregarProveedor">Agregar Proveedor</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Banco</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $proveedores = $conn->query("SELECT id, nombre, telefono, email, banco FROM proveedores");
            while ($row = $proveedores->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['banco']); ?></td>
                <td>
                    <a href="editar_proveedor.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                    <a href="eliminar_proveedor.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Deseas eliminar este proveedor?')">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalAgregarProveedor" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="contenidoProveedor">Cargando...</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalAgregarProveedor");
    modal.addEventListener("show.bs.modal", function () {
        fetch("proveedores.php?modal=1")
            .then(res => res.text())
            .then(html => {
                document.getElementById("contenidoProveedor").innerHTML = html;
            })
            .catch(() => {
                document.getElementById("contenidoProveedor").innerHTML = "<p class='text-danger'>Error al cargar el formulario.</p>";
            });
    });

    modal.addEventListener("hidden.bs.modal", function () {
        document.getElementById("contenidoProveedor").innerHTML = "Cargando...";
    });
});
</script>
</body>
</html>