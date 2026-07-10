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
    <title><?= isset($pageTitle) ? $pageTitle . " | SubsPilot" : "SubsPilot"; ?></title>

    <!-- Bootstrap -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=20260710a">
    <link rel="stylesheet" href="../assets/css/style.css?v=20260710a">
    <link rel="stylesheet" href="../assets/css/animations.css?v=20260710a">
    <link rel="stylesheet" href="../assets/css/extra.css?v=20260710a">
</head>

<body class="<?= !empty($_SESSION['dark_mode']) ? 'dark-mode' : '' ?>">

<div class="page-loader"><div class="spinner"></div></div>

<!-- Themed silhouette background -->
<div class="silhouette-bg">
    <div class="blob blob1"></div>
    <div class="blob blob2"></div>
    <div class="blob blob3"></div>
    <span class="s1"><i class="fa-solid fa-wallet"></i></span>
    <span class="s2"><i class="fa-solid fa-credit-card"></i></span>
    <span class="s3"><i class="fa-solid fa-coins"></i></span>
    <span class="s4"><i class="fa-solid fa-calendar-days"></i></span>
    <span class="s5"><i class="fa-solid fa-chart-line"></i></span>
    <span class="s6"><i class="fa-solid fa-bell"></i></span>
</div>

<div class="wrapper">
