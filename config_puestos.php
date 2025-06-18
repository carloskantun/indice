<?php
// config_puestos.php

return [
    'superadministrador' => [
        'modulos' => ['compras', 'mantenimiento', 'servicio_cliente', 'usuarios', 'configuracion', 'reportes', 'kpis'],
        'acceso_total' => true
    ],
    'admin' => [
        'modulos' => ['compras', 'mantenimiento', 'servicio_cliente', 'usuarios', 'configuracion', 'kpis'],
        'acceso_total' => true
    ],
    'ceo' => [
        'modulos' => ['reportes', 'kpis'],
        'acceso_total' => true
    ],
    'webmaster' => [
        'modulos' => ['usuarios', 'configuracion'],
        'acceso_total' => true
    ],
    'operador' => [
        'modulos' => ['transfers'],
        'acceso_total' => false
    ],
    'supervisor operador' => [
        'modulos' => ['transfers'],
        'acceso_total' => false
    ],
    'servicio al cliente' => [
        // Permite también ingresar órdenes en el módulo de mantenimiento
        'modulos' => ['servicio_cliente', 'mantenimiento'],
        'acceso_total' => false
    ],
    'camarista' => [
        'modulos' => ['mantenimiento_reporte'],
        'acceso_total' => false
    ],
    'mantenimiento' => [
        'modulos' => ['mantenimiento_listado'],
        'acceso_total' => false
    ],
    'director' => [
        'modulos' => ['kpis', 'mantenimiento'],
        'acceso_total' => false
    ],
    'gerente' => [
        'modulos' => ['kpis', 'compras', 'mantenimiento', 'servicio_cliente'],
        'acceso_total' => false
    ],
    'jefa de ama de llaves' => [
        'modulos' => ['mantenimiento'],
        'acceso_total' => false
    ],
];
