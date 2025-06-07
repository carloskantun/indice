<?php
session_start();
include 'auth.php';
include 'conexion.php';

// Solo permitir a admin o superadmin
if (!in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    die("Acceso no autorizado.");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID no proporcionado.");
}

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    die("Usuario no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3>Editar Usuario</h3>
    <form action="procesar_edicion_usuario.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">

        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Tel¨¦fono</label>
            <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($usuario['telefono']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Puesto</label>
            <input type="text" name="puesto" class="form-control" value="<?php echo htmlspecialchars($usuario['puesto']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Rol</label>
            <select name="rol" class="form-control" required>
                <option value="user" <?php if ($usuario['rol'] == 'user') echo 'selected'; ?>>Usuario</option>
                <option value="admin" <?php if ($usuario['rol'] == 'admin') echo 'selected'; ?>>Administrador</option>
                <option value="superadmin" <?php if ($usuario['rol'] == 'superadmin') echo 'selected'; ?>>Superadministrador</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="listar_usuarios.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

</body>
</html>