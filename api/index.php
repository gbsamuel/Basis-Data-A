<?php
/**
 * SIREKA - Vercel Serverless Entrypoint Router
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Clean path
$path = ltrim($uri, '/');
if (empty($path)) {
    $path = 'index.php';
}

// Security: Prevent path traversal
$path = str_replace(['..', '\\'], '', $path);
$targetFile = __DIR__ . '/../' . $path;

if (is_dir($targetFile)) {
    $targetFile = rtrim($targetFile, '/') . '/index.php';
}

if (!file_exists($targetFile)) {
    if (file_exists($targetFile . '.php')) {
        $targetFile = $targetFile . '.php';
    } else {
        http_response_code(404);
        echo "<h2 style='font-family:sans-serif;text-align:center;margin-top:50px;'>404 - Halaman Tidak Ditemukan</h2>";
        exit;
    }
}

// Set working directory so relative includes in PHP files work seamlessly
chdir(dirname($targetFile));
require $targetFile;
