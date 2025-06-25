<?php
// router_roles.php

function redireccionar_por_puesto($puesto) {
    $mapa = [
        'camarista' => 'reporte_camarista.php',
        'mantenimiento' => 'minipanel_mantenimiento.php',
        'director'=> 'minipanel_mantenimiento.php',
        'servicio al cliente'=> 'minipanel_servicio_cliente.php',
        'gerente'=> 'minipanel_servicio_cliente.php',
        'ceo'=> 'minipanel_mantenimiento.php',
        'operador'=> 'registrar_transfer.php',
        'supervisor operador'=> 'minipanel_transfers.php',
        'lavanderia'=> 'minipanel_lavanderia.php',
        // Agrega más puestos personalizados aquí si lo deseas:
        // 'webmaster' => 'panel_webmaster.php',
    ];

    $clave = strtolower(trim($puesto));
    if (isset($mapa[$clave])) {
        header("Location: " . $mapa[$clave]);
        exit;
    }
}
