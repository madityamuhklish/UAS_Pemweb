<?php

/**
 * Don't show raw PHP warnings/errors/notices in the browser (this is what breaks
 * the page layout with green "Warning: ..." text). Log them to a file instead so
 * they can still be debugged, but the user never sees broken HTML.
 */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../php_errors.log');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks that the user is logged in. If a PDO connection is passed, it also
 * verifies the account still exists in the database (e.g. it was deleted,
 * or the DB was reset while an old session cookie was still around) and, if
 * not, cleanly logs the session out instead of letting every page below crash
 * with "Trying to access array offset on value of type bool".
 */
function checkLogin($conn = null)
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    if ($conn) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            session_unset();
            session_destroy();
            header("Location: ../auth/login.php?expired=1");
            exit;
        }
    }
}