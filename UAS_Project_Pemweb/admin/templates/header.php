<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . " | SubsPilot Admin" : "SubsPilot Admin"; ?></title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/animations.css?v=20260710a">
    <link rel="stylesheet" href="../assets/css/admin.css?v=20260710a">
</head>

<body class="admin-body <?= (($_SESSION['admin_theme'] ?? 'dark') === 'light') ? 'admin-light' : '' ?>">

<div class="admin-silhouette">
    <div class="glow1"></div>
    <div class="glow2"></div>
    <span class="a1"><i class="fa-solid fa-shield-halved"></i></span>
    <span class="a2"><i class="fa-solid fa-gears"></i></span>
    <span class="a3"><i class="fa-solid fa-users-gear"></i></span>
    <span class="a4"><i class="fa-solid fa-lock"></i></span>
    <span class="a5"><i class="fa-solid fa-chart-line"></i></span>
</div>

<div class="admin-wrapper">
