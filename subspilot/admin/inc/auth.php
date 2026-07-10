<?php
/* ======================================================
   Admin Auth — completely separate login/session system
   from the regular user authentication (auth/*).
   Uses its own session keys (admin_id, admin_name, ...)
   so an admin session and a user session never collide.
====================================================== */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdminLogin()
{
    if (empty($_SESSION['admin_id']) || empty($_SESSION['is_admin'])) {
        header("Location: login.php");
        exit;
    }
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['is_admin']);
}
