<?php
$activeSidebar = $activeSidebar ?? 'dashboard';
$user = currentUser();
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-1 px-2">
            <i class="bi bi-shield-lock-fill fs-5"></i>
        </div>
        <div class="d-flex flex-column">
            <span class="fs-5 leading-tight">SIREKA</span>
            <small class="text-muted fw-normal fs-7" style="font-size:0.75rem;">HR & Admin Portal</small>
        </div>
    </div>

    <div class="p-3 border-bottom bg-light d-flex align-items-center gap-2">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
            <?= strtoupper(substr($user['nama'] ?? 'A', 0, 1)) ?>
        </div>
        <div class="overflow-hidden">
            <div class="fw-bold text-truncate" style="font-size: 0.88rem;"><?= htmlspecialchars($user['nama'] ?? 'Admin') ?></div>
            <span class="badge bg-primary text-white" style="font-size: 0.68rem;"><?= strtoupper($user['role'] ?? 'ADMIN') ?></span>
        </div>
    </div>

    <ul class="sidebar-nav">
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="sidebar-link <?= $activeSidebar === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/companies.php" class="sidebar-link <?= $activeSidebar === 'companies' ? 'active' : '' ?>">
                <i class="bi bi-building"></i>
                <span>Profil Perusahaan</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/divisions.php" class="sidebar-link <?= $activeSidebar === 'divisions' ? 'active' : '' ?>">
                <i class="bi bi-diagram-3"></i>
                <span>Divisi IT</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/jobs.php" class="sidebar-link <?= $activeSidebar === 'jobs' ? 'active' : '' ?>">
                <i class="bi bi-briefcase"></i>
                <span>Lowongan Kerja</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/candidates.php" class="sidebar-link <?= $activeSidebar === 'candidates' ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                <span>Data Kandidat</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/applications.php" class="sidebar-link <?= $activeSidebar === 'applications' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span>Lamaran Masuk</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/interviews.php" class="sidebar-link <?= $activeSidebar === 'interviews' ? 'active' : '' ?>">
                <i class="bi bi-calendar2-check"></i>
                <span>Jadwal Wawancara</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/loa_manage.php" class="sidebar-link <?= $activeSidebar === 'loa' ? 'active' : '' ?>">
                <i class="bi bi-award text-success"></i>
                <span>Letter of Acceptance (LoA)</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/complaints.php" class="sidebar-link <?= $activeSidebar === 'complaints' ? 'active' : '' ?>">
                <i class="bi bi-chat-left-dots"></i>
                <span>Keluhan & Tiket</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/feedback.php" class="sidebar-link <?= $activeSidebar === 'feedback' ? 'active' : '' ?>">
                <i class="bi bi-chat-heart"></i>
                <span>Ulasan & Feedback</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/reports.php" class="sidebar-link <?= $activeSidebar === 'reports' ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line"></i>
                <span>Laporan & Statistik</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= BASE_URL ?>/admin/settings.php" class="sidebar-link <?= $activeSidebar === 'settings' ? 'active' : '' ?>">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Sistem</span>
            </a>
        </li>
    </ul>

    <div class="p-3 border-top mt-auto">
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-sm btn-light w-100 text-start mb-2 text-muted">
            <i class="bi bi-house me-1"></i> Ke Halaman Publik
        </a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
