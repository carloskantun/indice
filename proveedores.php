<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'auth.php';
include 'conexion.php'; // Ahora usamos conexion.php

        <div class="mb-3">
            <label>No. Cuenta</label>
            <input type="text" name="numero_cuenta" class="form-control">
        </div>
        <div class="mb-3">
            <label>Banco</label>
            <input type="text" name="banco" class="form-control">
        </div>
        <div class="mb-3">
            <label>Direcci¨®n</label>
            <textarea name="direccion" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>RFC (Opcional)</label>
            <input type="text" name="rfc" class="form-control">
        </div>
        <div class="mb-3">
            <label>Descripci¨®n del Servicio</label>
            <textarea name="descripcion_servicio" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-warning w-100">Guardar</button>
    </form>
<?php
    exit; // Evita que se cargue toda la p¨¢gina si se usa en un modal
}

// ”9Ý8 Si no es un modal, cargar la vista completa
include 'header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Lista de Proveedores</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tel¨¦fono</th>
                <th>Email</th>
                <th>Banco</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $proveedores = $conn->query("SELECT id, nombre, telefono, email, banco FROM proveedores");
            while ($proveedor = $proveedores->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($proveedor['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['email']); ?></td>
                    <td><?php echo htmlspecialchars($proveedor['banco']); ?></td>
                    <td>
                        <a href="editar_proveedor.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="eliminar_proveedor.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('0†7Seguro que deseas eliminar este proveedor?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
