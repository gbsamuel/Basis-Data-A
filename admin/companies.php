<?php
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

// Profil Perusahaan sudah digabung ke halaman Pengaturan Sistem
header('Location: ' . BASE_URL . '/admin/settings.php');
exit;
