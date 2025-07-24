<?php
session_start();
include 'auth.php';
include 'conexion.php';
require_once __DIR__.'/controller.php';

$controller = new GastosController($conn);
$result = $controller->guardar($_POST, $_FILES);

echo $result;
