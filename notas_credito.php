<?php
session_start();
include 'auth.php';
include 'conexion.php';

$unidad = $_GET['unidad'] ?? '';
$estatus = $_GET['estatus'] ?? '';

$condiciones = [];
if ($unidad !== '') {
    $condiciones[] = 'nc.unidad_negocio_id = ' . intval($unidad);
}
if ($estatus !== '') {
    $condiciones[] = "nc.estatus = '" . $conn->real_escape_string($estatus) . "'";
}
$usuario = $_GET['usuario'] ?? '';
if ($usuario !== '') {
    $condiciones[] = 'nc.usuario_responsable_id = ' . intval($usuario);
}

if ($_SESSION['user_role'] !== 'superadmin' && $_SESSION['user_role'] !== 'admin') {
    $condiciones[] = 'nc.usuario_responsable_id = ' . intval($_SESSION['user_id']);
}
$where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

$sql = "SELECT 
            nc.id,
            nc.folio,
            nc.monto,
            nc.fecha_nota,
            nc.estatus,
            nc.concepto,
            u.nombre AS responsable,
            un.nombre AS unidad
        FROM notas_credito nc
        LEFT JOIN usuarios u ON nc.usuario_responsable_id = u.id
        LEFT JOIN unidades_negocio un ON nc.unidad_negocio_id = un.id
        $where
        ORDER BY nc.fecha_nota DESC";

$res = $conn->query($sql);
$notas = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Notas de Crédito</title>

  <!-- Primero los scripts base -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
</head>

<body class="bg-light">
<div class="container mt-4">
    <h3>Notas de Crédito</h3>
    <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
<button class="btn btn-success mb-3" id="btnNuevaNota">+ Nueva Nota de Crédito</button>
<?php endif; ?>

    <?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_role'] === 'admin'): ?>
<form method="GET" class="row g-2 mb-4" id="form-filtros">
    <div class="col-md">
        <select name="unidad" class="form-select">
            <option value="">Unidad de negocio</option>
            <?php
            $query = $conn->query("SELECT id, nombre FROM unidades_negocio ORDER BY nombre");
            while ($row = $query->fetch_assoc()):
            ?>
                <option value="<?= $row['id'] ?>" <?= $unidad == $row['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md">
        <select name="usuario" class="form-select select2">
            <option value="">Responsable</option>
            <?php
            $usrs = $conn->query("SELECT id, nombre FROM usuarios ORDER BY nombre");
            while ($u = $usrs->fetch_assoc()):
            ?>
                <option value="<?= $u['id'] ?>" <?= $_GET['usuario'] == $u['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md">
        <select name="estatus" class="form-select">
            <option value="">Estatus</option>
            <?php foreach (['Por pagar', 'Pago parcial', 'Pagado', 'Vencido'] as $e): ?>
                <option value="<?= $e ?>" <?= $estatus == $e ? 'selected' : '' ?>><?= $e ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md">
        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        <a href="notas_credito.php" class="btn btn-outline-secondary w-100">Limpiar</a>
    </div>
</form>
<?php endif; ?>


    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Folio</th>
                    <th>Unidad</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Responsable</th>
                    <th>Estatus</th>
                    <th>Concepto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notas as $n): ?>
                <tr>
                    <td><?= htmlspecialchars($n['folio']) ?></td>
                    <td><?= htmlspecialchars($n['unidad']) ?></td>
                    <td>$<?= number_format($n['monto'], 2) ?></td>
                    <td><?= htmlspecialchars($n['fecha_nota']) ?></td>
                    <td><?= htmlspecialchars($n['responsable']) ?></td>
                    <td><?= htmlspecialchars($n['estatus']) ?></td>
                    <td><?= htmlspecialchars($n['concepto']) ?></td>
                    <td>
                       <button class="btn btn-sm btn-outline-info ver-abonos-nota" data-id="<?= $n['id'] ?>">👁 Ver abonos</button>
<?php if ($_SESSION['user_role'] === 'superadmin' || $_SESSION['user_id'] == $n['usuario_responsable_id']): ?>
  <button class="btn btn-sm btn-outline-primary abonar-nota-btn" data-id="<?= $n['id'] ?>">💰 Abonar</button>
<?php endif; ?>
<button class="btn btn-sm btn-outline-warning editar-nota-btn" data-id="<?= $n['id'] ?>">Editar</button>

                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($notas)): ?>
                    <tr><td colspan="8" class="text-center text-muted">No hay notas registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="modalNota" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="contenidoNota">Cargando...</div>
  </div>
</div>

<div class="modal fade" id="modalVerAbonos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="contenidoVerAbonos">Cargando...</div>
  </div>
</div>
<div class="modal fade" id="modalAbonoNota" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" id="contenidoAbonoNota">Cargando...</div>
  </div>
</div>

  <!-- Luego tu script -->
  <script src="includes/assets/js/notas_credito.js"></script>
</body>
</html>
