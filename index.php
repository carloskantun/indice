<?php
session_start();
$module = $_GET['module'] ?? null;
$ajax   = isset($_GET['ajax']);

if ($module) {
    $file = __DIR__ . '/app/modules/' . basename($module) . '/view.php';
    if (is_file($file)) {
        include $file;
    } else {
        http_response_code(404);
        echo 'Modulo no encontrado';
    }
    exit;
}

if (!isset($_SESSION['user_id'])) {
    include __DIR__ . '/login_form.php';
} else {
    include __DIR__ . '/menu.php';
}
