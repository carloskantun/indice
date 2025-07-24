<?php
session_start();
include 'auth.php';
include 'conexion.php';
require_once __DIR__.'/controller.php';

$controller = new GastosController($conn);
$result = $controller->guardar($_POST, $_FILES);

if(isset($_GET['ajax']) || isset($_POST['ajax'])){
    echo $result;
}else{
    if($result === 'ok'){
        header('Location: index.php?module=gastos');
    }else{
        echo $result;
    }
}
