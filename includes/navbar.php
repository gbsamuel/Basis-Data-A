<?php
$user = currentUser();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-3 shadow-xs">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary fs-4" href="<?= BASE_URL ?>/index.php">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-1 px-2">
                <i class="bi bi-briefcase-fill fs-5"></i>
            </div>
            <span>SIREKA</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-3">
                <li class="nav-item">
                    <a class="nav-link fw-medium <?= ($activePage ?? '') === 'home' ? 'text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium <?= ($activePage ?? '') === 'about' ? 'text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/about.php">Tentang Kami</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium <?= ($activePage ?? '') === 'jobs' ? 'text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/jobs.php">Lowongan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium <?= ($activePage ?? '') === 'helpdesk' ? 'text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/helpdesk.php">Helpdesk</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if ($user): ?>
                    <?php if (hasRole(['admin', 'interviewer'])): ?>
                        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="bi bi-speedometer2"></i>
                            <span>Admin Panel</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="bi bi-person-circle"></i>
                            <span>Dashboard (<?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>)</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-outline-danger" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline-primary px-3">Login</a>
                    <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary px-3">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
