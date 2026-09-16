<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/database.php';
}
$pageTitle = $pageTitle ?? 'SIREKA - Sistem Informasi Rekrutmen & Kandidat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Meta SEO -->
    <meta name="description" content="SIREKA - Platform Sistem Informasi Rekrutmen dan Pengelolaan Kandidat Terintegrasi dengan Smart Matching Score, Tracking Status, dan Talent Pool.">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Design System CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    
    <!-- Chart.js (Loaded for dashboard and reports) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Top Scroll Reading Progress Indicator -->
    <div id="scrollProgressBar" class="scroll-progress-bar" aria-hidden="true"></div>
