<?php
require_once "inc/auth.php";
require_once "../config/database.php";

checkAdminLogin();

$db = new Database();
$conn = $db->connect();

$type = $_GET['type'] ?? '';
$allowed = ['users', 'subscriptions', 'activity'];
if (!in_array($type, $allowed)) {
    die("Tipe export tidak valid.");
}

$filename = "subspilot_{$type}_" . date('Y-m-d_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads it correctly

if ($type === 'users') {
    $rows = $conn->query("SELECT id, fullname, email, role, status, created_at FROM users ORDER BY id")->fetchAll();
    fputcsv($out, ['ID', 'Nama Lengkap', 'Email', 'Role', 'Status', 'Terdaftar Sejak']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'], $r['fullname'], $r['email'], $r['role'], $r['status'], $r['created_at']]);
    }

} elseif ($type === 'subscriptions') {
    $rows = $conn->query("
        SELECT s.id, u.fullname, u.email, s.service_name, c.category_name, s.amount,
               s.billing_cycle, s.start_date, s.next_payment, s.status, s.auto_renew
        FROM subscriptions s
        JOIN users u ON u.id = s.user_id
        LEFT JOIN categories c ON c.id = s.category_id
        ORDER BY s.id
    ")->fetchAll();
    fputcsv($out, ['ID', 'Pengguna', 'Email', 'Layanan', 'Kategori', 'Jumlah', 'Siklus', 'Tanggal Mulai', 'Pembayaran Berikutnya', 'Status', 'Auto Renew']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'], $r['fullname'], $r['email'], $r['service_name'], $r['category_name'] ?? '-',
            $r['amount'], $r['billing_cycle'], $r['start_date'], $r['next_payment'], $r['status'],
            $r['auto_renew'] ? 'Ya' : 'Tidak'
        ]);
    }

} elseif ($type === 'activity') {
    $search = trim($_GET['q'] ?? '');
    $userFilter = $_GET['user_id'] ?? '';
    $dateFrom = $_GET['from'] ?? '';
    $dateTo = $_GET['to'] ?? '';

    $sql = "
        SELECT a.activity, a.created_at, u.fullname, u.email
        FROM activity_logs a
        LEFT JOIN users u ON u.id = a.user_id
        WHERE 1=1
    ";
    $params = [];
    if ($search !== '') {
        $sql .= " AND (a.activity LIKE ? OR u.fullname LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($userFilter !== '') {
        $sql .= " AND u.id = ?";
        $params[] = $userFilter;
    }
    if ($dateFrom !== '') {
        $sql .= " AND DATE(a.created_at) >= ?";
        $params[] = $dateFrom;
    }
    if ($dateTo !== '') {
        $sql .= " AND DATE(a.created_at) <= ?";
        $params[] = $dateTo;
    }
    $sql .= " ORDER BY a.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    fputcsv($out, ['Pengguna', 'Email', 'Aktivitas', 'Waktu']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['fullname'] ?? 'Pengguna dihapus', $r['email'] ?? '-', $r['activity'], $r['created_at']]);
    }
}

fclose($out);
exit;
