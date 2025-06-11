<?php
include 'auth.php';
session_start();

$puesto = strtolower(trim($_SESSION['puesto'] ?? ''));

$rol    = strtolower(trim($_SESSION['user_role'] ?? $_SESSION['rol'] ?? ''));
if (!in_array($rol, ['camarista', 'ama de llaves'])) {
    header('Location: minipanel_mantenimiento.php');
    exit;
}

if ($puesto !== 'camarista') {
    header('Location: minipanel_mantenimiento.php');
    exit;
}


include 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresar Reporte de Mantenimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h3 class="mb-4">Ingresar Reporte de Mantenimiento</h3>

        <form action="procesar_mantenimiento.php" method="POST" enctype="multipart/form-data">
            <!-- Alojamiento -->
            <div class="mb-3">
                <label for="alojamiento_id" class="form-label">Alojamiento / Departamento</label>
                <select name="alojamiento_id" class="form-control" required>
                    <option value="">Seleccionar</option>
                    <?php
                    $alojamientos = $conn->query("SELECT id, nombre FROM alojamientos");
                    while ($alojamiento = $alojamientos->fetch_assoc()):
                    ?>
                        <option value="<?php echo $alojamiento['id']; ?>"><?php echo htmlspecialchars($alojamiento['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Fecha del reporte -->
            <div class="mb-3">
                <label for="fecha_reporte" class="form-label">Fecha del reporte</label>
                <input type="date" name="fecha_reporte" class="form-control" required>
            </div>

            <!-- Descripción -->
            <div class="mb-3">
                <label for="descripcion_reporte" class="form-label">Descripción del mantenimiento</label>
                <textarea name="descripcion_reporte" class="form-control" required></textarea>
            </div>

            <!-- Estatus -->
            <div class="mb-3">
                <label for="estatus" class="form-label">Estatus</label>
                <select name="estatus" class="form-control" required>
                    <option value="Pendiente" selected>Pendiente</option>
                    <option value="En proceso">En proceso</option>
                    <option value="Terminado">Terminado</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>

            <!-- Subir foto -->
            <div class="mb-3">
                <label for="foto" class="form-label">Foto del mantenimiento</label>
                <input type="file" name="foto" class="form-control">
            </div>

            <!-- Usuario solicitante -->
            <div class="mb-3">
                <label for="usuario_solicitante_id" class="form-label">Usuario solicitante</label>
                <select name="usuario_solicitante_id" class="form-control" required>
                    <option value="">Seleccionar</option>
                    <?php
                    $usuarios = $conn->query("SELECT id, nombre FROM usuarios");
                    while ($usuario = $usuarios->fetch_assoc()):
                    ?>
                        <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Unidad de negocio -->
            <div class="mb-3">
                <label for="unidad_negocio_id" class="form-label">Unidad de Negocio</label>
                <select name="unidad_negocio_id" class="form-control" required>
                    <option value="">Seleccionar</option>
                    <?php
                    $unidades = $conn->query("SELECT id, nombre FROM unidades_negocio");
                    while ($unidad = $unidades->fetch_assoc()):
                    ?>
                        <option value="<?php echo $unidad['id']; ?>"><?php echo htmlspecialchars($unidad['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Notas -->
            <div class="mb-3">
                <label for="notas" class="form-label">Notas adicionales</label>
                <textarea name="notas" class="form-control"></textarea>
            </div>

            <!-- Botón -->
            <button type="submit" class="btn btn-success w-100">Guardar Reporte</button>
        </form>
    </div>
</body>
</html>
