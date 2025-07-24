<?php
session_start();
include 'auth.php';
include 'conexion.php';
require_once __DIR__.'/controller.php';

if ($_SESSION['user_role'] !== 'superadmin') {
    exit('No autorizado');
}

$controller = new GastosController($conn);
echo $controller->actualizar($_POST);
