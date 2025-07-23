<?php
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
session_start();
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=Debe iniciar sesión");
    exit;
}

// Verificar rol del usuario (opcional por archivo)
function verificar_rol($rol_requerido) {
    if ($_SESSION['user_role'] !== $rol_requerido) {
        die("Acceso no autorizado para este rol.");
    }
}
?>
