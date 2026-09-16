<?php
$activeSidebar = $activeSidebar ?? 'dashboard';
$user = currentUser();
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-1 px-2">
            <i class="bi bi-briefcase-fill fs-5"></i>
        </div>
        <div class="d-flex flex-column">
            <span class="fs-5 leading-tight">SIREKA</span>
            <small class="text-muted fw-normal fs-7" style="font-size:0.75rem;">Portal Pelamar</small>
        </div>
    </div>

    <div class="p-3 border-bottom bg-light d-flex align-items-center gap-2">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
            <?= strtoupper(substr($user['nama'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="overflow-hidden">
            <div class="fw-bold text-truncate" style="font-size: 0.88rem;"><?= htmlspecialchars($user['nama'] ?? 'Pelamar') ?></div>
            <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;"><?= htmlspecialchars($user['email'] ?? '') ?></small>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/user/dashboard.php" class="sidebar-link <?= $activeSidebar === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/user/jobs.php" class="sidebar-link <?= $activeSidebar === 'jobs' ? 'active' : '' ?>">
                <i class="bi bi-search"></i>
                <span>Cari Lowongan</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/user/applications.php" class="sidebar-link <?= $activeSidebar === 'applications' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-person"></i>
                <span>Lamaran Saya</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/user/interview.php" class="sidebar-link <?= $activeSidebar === 'interview' ? 'active' : '' ?>">
                <i class="bi bi-camera-video"></i>
                <span>Jadwal Interview</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/user/profile.php" class="sidebar-link <?= $activeSidebar === 'profile' ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i>
                <span>Profil & Keahlian</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/user/loa.php" class="sidebar-link <?= $activeSidebar === 'loa' ? 'active' : '' ?>">
                <i class="bi bi-award-fill text-success"></i>
                <span>Letter of Acceptance</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/user/complaint.php" class="sidebar-link <?= $activeSidebar === 'complaint' ? 'active' : '' ?>">
                <i class="bi bi-chat-dots"></i>
                <span>Layanan Keluhan</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/user/feedback.php" class="sidebar-link <?= $activeSidebar === 'feedback' ? 'active' : '' ?>">
                <i class="bi bi-star"></i>
                <span>Beri Penilaian</span>
            </a>
        </li>
    </ul>

    <div class="p-3 border-top mt-auto">
        <a href="<?= BASE_URL ?>/helpdesk.php" class="btn btn-sm btn-outline-success w-100 d-flex align-items-center justify-content-center gap-2 mb-2">
            <i class="bi bi-whatsapp"></i>
            <span>Bantuan WhatsApp</span>
        </a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
