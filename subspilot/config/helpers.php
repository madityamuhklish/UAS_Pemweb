<?php

/**
 * Safely reads a POST field without throwing "Undefined array key" warnings
 * when a field is missing (e.g. an incomplete form submission). Use this
 * instead of raw $_POST['x'] in process.php files.
 */
function input($key, $default = '') {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/**
 * Same as input() but for $_GET.
 */
function inputGet($key, $default = '') {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

function logActivity($conn, $userId, $activity) {
    $stmt = $conn->prepare("INSERT INTO activity_logs(user_id, activity) VALUES(?, ?)");
    $stmt->execute([$userId, $activity]);
}

function flash($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function renderFlash() {
    if (!empty($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] === 'error' ? 'danger' : $_SESSION['flash_type'];
        $icon = $type === 'success' ? 'circle-check' : ($type === 'danger' ? 'circle-exclamation' : 'circle-info');
        echo '<div class="toast-notif toast-notif-' . htmlspecialchars($type) . '" role="status">
                <div class="toast-notif-icon"><i class="fa-solid fa-' . $icon . '"></i></div>
                <div class="toast-notif-msg">' . htmlspecialchars($_SESSION['flash_message']) . '</div>
                <button type="button" class="toast-notif-close" aria-label="Tutup">&times;</button>
              </div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

function statusBadgeClass($status) {
    switch ($status) {
        case 'Active': return 'status-active';
        case 'Cancelled': return 'status-cancelled';
        case 'Paused': return 'status-paused';
        default: return 'bg-secondary';
    }
}

function priorityBadgeClass($priority) {
    switch ($priority) {
        case 'High': return 'priority-high';
        case 'Medium': return 'priority-medium';
        case 'Low': return 'priority-low';
        default: return 'bg-secondary';
    }
}
