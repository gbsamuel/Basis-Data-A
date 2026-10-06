<?php
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

// Halaman feedback sudah digabung ke halaman Keluhan & Feedback (tab "Ulasan & Feedback")
header('Location: ' . BASE_URL . '/admin/complaints.php?tab=feedback');
exit;
