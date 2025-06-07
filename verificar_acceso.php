<?php
// verificar_acceso.php

function obtener_puesto() {
    return strtolower(trim($_SESSION['puesto'] ?? ''));
}

function tiene_acceso($modulo) {
    $puesto = obtener_puesto();
    $permisos = include __DIR__ . '/config_puestos.php';

    return $permisos[$puesto]['acceso_total'] ?? false ||
           in_array($modulo, $permisos[$puesto]['modulos'] ?? []);
}

function puede_ver_modulo($modulo) {
    return tiene_acceso($modulo); // alias semántico para vistas
}
