<?php
session_start();
include 'auth.php';
include 'conexion.php';

if (isset($_GET['modal'])) {
?>
<form action="procesar_proveedor.php" method="POST">
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


if (isset($_GET['modal'])) {
?>
<form action="procesar_proveedor.php" method="POST">
    <div class="mb-3">
        <label>No. Cuenta</label>
        <input type="text" name="numero_cuenta" class="form-control">
    </div>
    <div class="mb-3">
        <label>Banco</label>
        <input type="text" name="banco" class="form-control">
    </div>
    <div class="mb-3">
        <label>DirecciÃ³n</label>
        <textarea name="direccion" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label>RFC (Opcional)</label>
        <input type="text" name="rfc" class="form-control">
    </div>
    <div class="mb-3">
        <label>DescripciÃ³n del Servicio</label>
        <textarea name="descripcion_servicio" class="form-control"></textarea>
    </div>
    <button type="submit" class="btn btn-warning w-100">Guardar</button>
</form>
<?php
    exit;
}

// Si no es un modal, cargar la vista completa
// ”9Ý8 Si la petici¨®n viene del modal (`proveedores.php?modal=1`), solo devuelve el formulario
if (isset($_GET['modal'])) {
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
            <label>Tel¨¦fono</label>
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
    exit;
}

// Si no es un modal, cargar la vista completa
include 'header.php';
?>
<div class="container mt-5">
    <h2 class="mb-4">Lista de Proveedores</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Telefono</th>
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
                        <a href="eliminar_proveedor.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este proveedor?')">Eliminar</a>
                        <a href="eliminar_proveedor.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Â¿Seguro que deseas eliminar este proveedor?')">Eliminar</a>
                        <a href="eliminar_proveedor.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('0†7Seguro que deseas eliminar este proveedor?')">Eliminar</a>

                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>
