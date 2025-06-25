<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificaAccesoModulo(string $modulo): bool {
    $rol    = strtolower(trim($_SESSION['user_role'] ?? $_SESSION['rol'] ?? ''));
    $puesto = strtolower(trim($_SESSION['puesto'] ?? ''));

    // Roles con acceso total
    $roles_full = ['superadmin', 'administrador', 'gerente', 'ceo', 'webmaster'];

    if (in_array($rol, $roles_full)) {
        return true;
    }

    switch ($modulo) {
        case 'mantenimiento':
            return str_contains($puesto, 'mantenimiento') ||
                   str_contains($puesto, 'servicio al cliente') ||
                   str_contains($puesto, 'camarista') ||
                   str_contains($puesto, 'ama de llaves') ||
                   str_contains($puesto, 'director') ||
                   str_contains($puesto, 'gerente') ||
                   str_contains($puesto, 'webmaster');

        case 'servicio_cliente':
            return str_contains($puesto, 'servicio al cliente') ||
                   str_contains($puesto, 'gerente') ||
                   str_contains($puesto, 'webmaster');

        case 'camarista':
            return str_contains($puesto, 'camarista') ||
                   str_contains($puesto, 'ama de llaves');

        case 'kpis':
            return in_array($rol, ['superadmin', 'gerente', 'ceo', 'webmaster', 'administrador']);

        case 'usuarios':
        case 'configuracion':
            return in_array($rol, ['superadmin', 'administrador', 'webmaster']);

        case 'ordenes_compra':
            return in_array($rol, ['superadmin', 'administrador', 'gerente']) ||
                   str_contains($puesto, 'compras');

        case 'transfers':
            return str_contains($puesto, 'operador') ||
                   str_contains($puesto, 'supervisor operador') ||
                   in_array($rol, ['admin', 'ceo', 'webmaster', 'superadmin']);

        case 'lavanderia':
            return in_array($rol, ['superadmin','administrador','gerente','admin']) ||
                   str_contains($puesto, 'lavanderia');

        default:
            return false;
    }
}
?>
