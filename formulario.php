<?php
include 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pedido Lavandería</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
<h3 class="mb-3">Registrar Pedido de Lavandería</h3>
<?php if(isset($_GET['msg'])): ?><div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
<form method="POST" action="procesar_lavanderia.php">
  <input type="hidden" name="origen" value="formulario.php">
  <div class="mb-2"><label class="form-label">Fecha</label><input type="date" name="fecha" class="form-control" required></div>
  <div class="mb-2"><label class="form-label">Cliente</label><input type="text" name="cliente" class="form-control" required></div>
  <div class="mb-2"><label class="form-label">Servicio</label><input type="text" name="servicio" class="form-control" required></div>
  <div class="mb-2"><label class="form-label">Prenda</label><input type="text" name="prenda" class="form-control" required></div>
  <div class="mb-2"><label class="form-label">Cantidad</label><input type="number" name="cantidad" class="form-control" value="1" required></div>
  <div class="mb-2"><label class="form-label">Monto</label><input type="number" step="0.01" name="monto" class="form-control" required></div>
  <div class="mb-2"><label class="form-label">Unidad de Negocio</label>
    <select name="unidad_negocio_id" class="form-select" required>
      <?php $u=$conn->query("SELECT id,nombre FROM unidades_negocio ORDER BY nombre"); while($r=$u->fetch_assoc()){echo "<option value='{$r['id']}'>{$r['nombre']}</option>";} ?>
    </select>
  </div>
  <button class="btn btn-primary" type="submit">Enviar</button>
</form>
</div>
</body>
</html>
