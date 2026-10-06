<?php
require_once __DIR__ . '/../config/database.php';

if (isLoggedIn()) {
    header('Location: ' . homeUrl());
    exit;
}

$pageTitle = 'Login Portal - SIREKA';
$redirect = $_GET['redirect'] ?? '';

require_once __DIR__ . '/../includes/header.php';
// Halaman login tampil penuh tanpa navbar dan footer biasa
$hideFooter = true;
?>

<!-- Halaman login: foto gedung (assets/img/building-bg.jpg) memenuhi layar -->
<div class="login-page">
    <!-- Header khusus halaman login -->
    <header class="login-header">
        <a href="<?= BASE_URL ?>/index.php" class="login-brand">
            <i class="bi bi-briefcase-fill"></i> SIREKA
        </a>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/jobs.php" class="login-pill login-pill-light">
                <i class="bi bi-search me-1"></i> Lowongan
            </a>
            <a href="<?= BASE_URL ?>/auth/register.php" class="login-pill login-pill-dark">Daftar Akun</a>
        </div>
    </header>

    <!-- Lingkaran tipis di belakang kartu (hiasan) -->
    <div class="login-circle" aria-hidden="true"></div>

    <!-- Kartu login transparan seperti kaca -->
    <div class="login-glass">
        <div class="text-center mb-4">
            <div class="login-icon-box">
                <i class="bi bi-box-arrow-in-right"></i>
            </div>
            <h4 class="fw-bold mb-1">Sign in with email</h4>
            <p class="text-muted small mb-0">Portal Rekrutmen Terpadu &amp; Pengelolaan Kandidat IT Nusantara</p>
        </div>

        <?php renderFlash(); ?>

        <form method="POST" action="<?= BASE_URL ?>/auth/process_login.php">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

            <div class="login-input mb-3">
                <i class="bi bi-envelope"></i>
                <input type="email" id="email" name="email" required placeholder="Email">
            </div>

            <div class="login-input mb-2">
                <i class="bi bi-lock"></i>
                <input type="password" id="password" name="password" required placeholder="Password">
                <!-- Tombol mata untuk menampilkan / menyembunyikan password -->
                <button type="button" class="login-eye" id="togglePassword" title="Tampilkan password">
                    <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
                </button>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 small">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label text-muted" for="remember">Ingat saya</label>
                </div>
                <a href="#" class="text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#forgotModal">Forgot password?</a>
            </div>

            <button type="submit" class="btn login-submit w-100 mb-3">Get Started</button>

            <div class="text-center small text-muted">
                Belum memiliki akun pelamar?
                <a href="<?= BASE_URL ?>/auth/register.php" class="fw-bold text-primary">Daftar Akun Baru</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Tampilkan / sembunyikan password saat ikon mata diklik
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye-slash';
        }
    });
</script>

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
