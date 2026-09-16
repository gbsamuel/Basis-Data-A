<?php
require_once __DIR__ . '/../config/database.php';

if (isLoggedIn()) {
    if (hasRole(['admin', 'interviewer'])) {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/user/dashboard.php');
    }
    exit;
}

$pageTitle = 'Login Portal - SIREKA';
$redirect = $_GET['redirect'] ?? '';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card-custom p-4 p-md-5 border-0 shadow-sm">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2 px-3 mb-2 shadow-sm">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Masuk ke SIREKA</h3>
                    <p class="text-muted small">Portal Rekrutmen Terpadu & Pengelolaan Kandidat</p>
                </div>

                <?php renderFlash(); ?>

                <form method="POST" action="<?= BASE_URL ?>/auth/process_login.php">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="nama@email.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label small fw-semibold mb-0">Kata Sandi</label>
                            <a href="#" class="small text-muted" data-bs-toggle="modal" data-bs-target="#forgotModal">Lupa Sandi?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label small text-muted" for="remember">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold mb-3 shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                    </button>

                    <div class="text-center small text-muted">
                        Belum memiliki akun pelamar? 
                        <a href="<?= BASE_URL ?>/auth/register.php" class="fw-bold text-primary">Daftar Akun Baru</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Forgot Password -->
<div class="modal fade" id="forgotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-question-circle me-1"></i> Lupa Kata Sandi?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small text-muted">
                <p>Untuk kebutuhan presentasi tugas Basis Data, seluruh akun pelamar demo menggunakan password <code>user123</code> dan akun admin menggunakan <code>admin123</code>.</p>
                <p class="mb-0">Jika Anda baru saja mendaftar, silakan gunakan kata sandi yang Anda tentukan pada form registrasi.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
