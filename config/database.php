<?php
/**
 * SIREKA - Sistem Informasi Rekrutmen & Kandidat
 * Database Connection & Core Application Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials (TiDB Cloud Connection)
define('DB_HOST', getenv('DB_HOST') ?: 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com');
define('DB_NAME', getenv('DB_NAME') ?: 'sireka_db');
define('DB_USER', getenv('DB_USER') ?: '3QykrhkWC6SQyZr.root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '<PASSWORD>');
define('DB_PORT', getenv('DB_PORT') ?: '4000');

// Base URL helper
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';

$projectRoot = '';
if (!empty($_SERVER['VERCEL'])) {
    // Hosted on Vercel
    $projectRoot = '';
} elseif (php_sapi_name() === 'cli-server') {
    // When running with php -S localhost:8000
    $projectRoot = '';
} else {
    // When running under Apache / Laragon (e.g. http://localhost/sireka)
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $parts = array_values(array_filter(explode('/', $scriptDir)));
    if (!empty($parts)) {
        if (in_array('sireka', $parts)) {
            $projectRoot = '/sireka';
        } elseif (in_array('Project Basis Data', $parts)) {
            $projectRoot = '/Project Basis Data';
        } else {
            $appDirs = ['auth', 'user', 'admin', 'config', 'assets', 'includes', 'uploads'];
            if (!in_array($parts[0], $appDirs)) {
                $projectRoot = '/' . $parts[0];
            }
        }
    }
}
define('BASE_URL', rtrim($protocol . $hostName . $projectRoot, '/'));

/**
 * Get PDO Database Connection
 */
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            if (DB_HOST !== 'localhost' && DB_HOST !== '127.0.0.1') {
                $options[PDO::MYSQL_ATTR_SSL_CA] = true;
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("<div style='font-family:sans-serif;padding:30px;background:#fff5f5;border:1px solid #feb2b2;color:#9b2c2c;border-radius:8px;max-width:700px;margin:50px auto;'>"
                . "<h2 style='margin-top:0;'>Koneksi Database Gagal</h2>"
                . "<p>Pastikan MySQL di Laragon sudah dinyalakan (Running) dan database <code>" . htmlspecialchars(DB_NAME) . "</code> telah diimport dari file <code>database.sql</code>.</p>"
                . "<p><strong>Detail Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>"
                . "</div>");
        }
    }
    return $pdo;
}

/**
 * Authentication Helpers
 */
function isLoggedIn() {
    return isset($_SESSION['user_nik']) && !empty($_SESSION['user_nik']);
}

function currentUser() {
    if (!isLoggedIn()) return null;
    return $_SESSION['user'] ?? null;
}

function hasRole($roles) {
    if (!isLoggedIn()) return false;
    $currentRole = $_SESSION['user_role'] ?? '';
    if (is_array($roles)) {
        return in_array($currentRole, $roles);
    }
    return $currentRole === $roles;
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.');
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    if (!hasRole($roles)) {
        setFlash('danger', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        if (hasRole('admin') || hasRole('interviewer')) {
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/user/dashboard.php');
        }
        exit;
    }
}

/**
 * Flash Notification Helpers
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash() {
    $flash = getFlash();
    if ($flash) {
        $type = htmlspecialchars($flash['type']);
        $msg = htmlspecialchars($flash['message']);
        $icon = 'bi-info-circle';
        if ($type === 'success') $icon = 'bi-check-circle-fill';
        elseif ($type === 'danger') $icon = 'bi-exclamation-triangle-fill';
        elseif ($type === 'warning') $icon = 'bi-exclamation-circle-fill';

        echo "<div class='alert alert-{$type} alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm' role='alert'>
                <i class='bi {$icon} me-2 fs-5'></i>
                <div>{$msg}</div>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

/**
 * Match Score Calculation System
 * Formula: Match Score = (Jumlah skill kandidat yang cocok / Jumlah skill yang dibutuhkan posisi) * 100%
 */
function calculateMatchScore($pdo, $nik, $id_job) {
    // 1. Ambil skill yang dibutuhkan oleh lowongan kerja
    $stmt = $pdo->prepare("SELECT id_skill FROM job_skill WHERE id_job = ? AND is_required = 1");
    $stmt->execute([$id_job]);
    $jobSkills = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $totalRequired = count($jobSkills);
    if ($totalRequired === 0) {
        return 100.00; // Posisi umum tanpa spesifikasi skill khusus
    }

    // 2. Ambil skill yang dimiliki kandidat
    $stmtUser = $pdo->prepare("SELECT id_skill FROM user_skill WHERE nik = ?");
    $stmtUser->execute([$nik]);
    $userSkills = $stmtUser->fetchAll(PDO::FETCH_COLUMN);

    // 3. Hitung persentase kecocokan (Intersection)
    $matched = array_intersect($jobSkills, $userSkills);
    $matchedCount = count($matched);

    $score = ($matchedCount / $totalRequired) * 100;
    return round($score, 2);
}

/**
 * Get detailed skill match breakdown for UI chips
 */
function getJobSkillsDetail($pdo, $nik, $id_job) {
    // Ambil semua skill pada lowongan
    $stmt = $pdo->prepare("
        SELECT s.id_skill, s.nama_skill, s.category, js.is_required
        FROM job_skill js
        JOIN skill s ON js.id_skill = s.id_skill
        WHERE js.id_job = ?
        ORDER BY s.nama_skill ASC
    ");
    $stmt->execute([$id_job]);
    $jobSkills = $stmt->fetchAll();

    // Ambil list skill id kandidat
    $userSkillIds = [];
    if (!empty($nik)) {
        $stmtUser = $pdo->prepare("SELECT id_skill FROM user_skill WHERE nik = ?");
        $stmtUser->execute([$nik]);
        $userSkillIds = $stmtUser->fetchAll(PDO::FETCH_COLUMN);
    }

    $matchedCount = 0;
    $totalRequired = 0;

    foreach ($jobSkills as &$item) {
        $isMatched = in_array($item['id_skill'], $userSkillIds);
        $item['matched'] = $isMatched;
        if ($item['is_required']) {
            $totalRequired++;
            if ($isMatched) $matchedCount++;
        }
    }

    $score = $totalRequired > 0 ? round(($matchedCount / $totalRequired) * 100, 2) : 100.00;

    return [
        'skills' => $jobSkills,
        'matched_count' => $matchedCount,
        'total_required' => $totalRequired,
        'score' => $score
    ];
}

/**
 * Record recruitment stage progression in database history
 */
function recordStageHistory($pdo, $id_application, $stage, $status, $notes, $changed_by = null) {
    $stmt = $pdo->prepare("
        INSERT INTO candidate_stage_history (id_application, stage, status, notes, changed_by, changed_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    return $stmt->execute([$id_application, $stage, $status, $notes, $changed_by]);
}

/**
 * Format date in Indonesian
 */
function formatTanggalIndo($datetime) {
    if (empty($datetime)) return '-';
    $timestamp = strtotime($datetime);
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tgl = date('d', $timestamp);
    $bln = $bulan[(int)date('m', $timestamp)];
    $thn = date('Y', $timestamp);
    return "{$tgl} {$bln} {$thn}";
}

/**
 * Format currency to IDR
 */
function formatRupiah($nominal) {
    if ($nominal === null || $nominal === '') return 'Negosiasi';
    return 'Rp ' . number_format($nominal, 0, ',', '.');
}

/**
 * Get system setting
 */
function getSystemSetting($pdo, $key, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Get Status Badge HTML
 */
function getStatusBadge($status) {
    $map = [
        'Applied' => ['bg' => 'primary', 'icon' => 'bi-file-earmark-arrow-up', 'text' => 'Applied'],
        'HR Review' => ['bg' => 'info text-dark', 'icon' => 'bi-person-check', 'text' => 'HR Review'],
        'Document Screening' => ['bg' => 'secondary', 'icon' => 'bi-files', 'text' => 'Document Screening'],
        'Interview Scheduling' => ['bg' => 'warning text-dark', 'icon' => 'bi-calendar-event', 'text' => 'Interview Scheduling'],
        'Interview' => ['bg' => 'warning text-dark', 'icon' => 'bi-camera-video', 'text' => 'Interview'],
        'Final Decision' => ['bg' => 'info text-dark', 'icon' => 'bi-hourglass-split', 'text' => 'Final Decision'],
        'Accepted' => ['bg' => 'success', 'icon' => 'bi-check-circle-fill', 'text' => 'Accepted'],
        'Rejected' => ['bg' => 'danger', 'icon' => 'bi-x-circle-fill', 'text' => 'Rejected'],
        'Talent Pool' => ['bg' => 'purple text-white', 'icon' => 'bi-star-fill', 'text' => 'Talent Pool']
    ];

    $info = $map[$status] ?? ['bg' => 'secondary', 'icon' => 'bi-circle', 'text' => $status];
    return "<span class='badge bg-{$info['bg']} d-inline-flex align-items-center gap-1 px-2.5 py-1.5 shadow-sm rounded-pill font-semibold'>
                <i class='bi {$info['icon']}'></i> {$info['text']}
            </span>";
}
