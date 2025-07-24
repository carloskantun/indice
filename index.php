<?php
$module = $_GET['module'] ?? null;
if ($module) {
    $file = __DIR__ . '/app/modules/' . basename($module) . '/view.php';
    if (is_file($file)) {
        include $file;
        exit;
    } else {
        http_response_code(404);
        echo "Modulo no encontrado";
        exit;
    }
}
include __DIR__ . '/login_form.php';
