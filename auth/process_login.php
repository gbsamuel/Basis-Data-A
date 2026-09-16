<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = trim($_POST['redirect'] ?? '');

if (empty($email) || empty($password)) {
    setFlash('danger', 'Email dan password wajib diisi.');
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM user_all WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    setFlash('danger', 'Email atau password yang Anda masukkan salah.');
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// Set session
$_SESSION['user_nik'] = $user['nik'];
$_SESSION['user_name'] = $user['nama'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user'] = $user;

setFlash('success', 'Selamat datang kembali, ' . htmlspecialchars($user['nama']) . '!');

// Redirect logic
if (!empty($redirect)) {
    header('Location: ' . BASE_URL . '/' . ltrim($redirect, '/'));
    exit;
}

if ($user['role'] === 'admin' || $user['role'] === 'interviewer') {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/user/dashboard.php');
}
exit;
