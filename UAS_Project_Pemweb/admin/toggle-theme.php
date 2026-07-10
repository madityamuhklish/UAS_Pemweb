<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (empty($_SESSION['admin_id']) || empty($_SESSION['is_admin'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$theme = (isset($_POST['theme']) && $_POST['theme'] === 'light') ? 'light' : 'dark';
$_SESSION['admin_theme'] = $theme;

echo json_encode(['ok' => true, 'theme' => $theme]);
