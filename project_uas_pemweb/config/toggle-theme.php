<?php
require_once "session.php";
require_once "database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$mode = (isset($_POST['dark_mode']) && $_POST['dark_mode'] === '1') ? 1 : 0;

$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("UPDATE users SET dark_mode = ? WHERE id = ?");
$stmt->execute([$mode, $_SESSION['user_id']]);

$_SESSION['dark_mode'] = $mode;

echo json_encode(['ok' => true, 'dark_mode' => $mode]);
