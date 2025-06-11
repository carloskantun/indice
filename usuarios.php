<?php
session_start();
include 'auth.php';
include 'conexion.php';

// Solo superadmin o admin pueden entrar a este módulo
if (!in_array($_SESSION['user_role'], ['superadmin', 'admin'])) {
    die("Acceso no autorizado.");
}

// 📌 Si la petición viene del modal (usuarios.php?modal=1), solo devuelve el formulario
if (isset($_GET['modal'])) {
?>
<form action="procesar_usuario.php" method="POST">
    <!-- Nombre -->
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre Completo</label>
        <input type="text" class="form-control" id="nombre" name="nombre" required>
    </div>

    <!-- Teléfono -->
    <div class="mb-3">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" class="form-control" id="telefono" name="telefono" required>
    </div>
    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label">Correo Electrónico</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <!-- Puesto -->
    <div class="mb-3">
        <label for="puesto" class="form-label">Puesto</label>
        <input type="text" class="form-control" id="puesto" name="puesto" required>
    </div>

    <!-- Contraseña -->
    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>

    <!-- Rol -->
    <div class="mb-3">
        <label for="rol" class="form-label">Rol</label>
        <select class="form-control" id="rol" name="rol" required>
            <option value="user">Usuario</option>
            <option value="admin">Administrador</option>
            <option value="superadmin">Superadministrador</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary w-100">Registrar Usuario</button>
</form>
<?php
    exit;
}

// Página completa
include 'header.php';
?>
<div class="container mt-5">
    <h2 class="mb-4">Lista de Usuarios</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Puesto</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $usuarios = $conn->query("SELECT id, nombre, telefono, email, puesto, rol FROM usuarios");
            while ($usuario = $usuarios->fetch_assoc()):
                $puede_editar = false;
                $puede_eliminar = false;

                if ($_SESSION['user_role'] === 'superadmin') {
                    $puede_editar = $puede_eliminar = true;
                } elseif ($_SESSION['user_role'] === 'admin' && $usuario['rol'] !== 'superadmin') {
                    $puede_editar = $puede_eliminar = true;
                }
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['puesto']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                    <td>
                        <?php if ($puede_editar): ?>
                            <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                        <?php endif; ?>
                        <?php if ($puede_eliminar): ?>
                            <a href="eliminar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">Eliminar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>