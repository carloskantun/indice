<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Denegado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 text-center">
        <h3 class="text-danger">Acceso denegado</h3>
        <p>No cuentas con permisos para ver esta sección.</p>
        <a href="menu_principal.php" class="btn btn-primary">Regresar al menú</a>
    </div>
</body>
</html>
