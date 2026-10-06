<?php
/**
 * Nama dan foto profil user di header (pojok kanan atas).
 * File ini dipanggil di dalam <header class="top-navbar"> pada setiap halaman user dan admin.
 */
$topUser = currentUser();

// Saat foto/nama diklik: pelamar ke profil pelamar, HR dan interviewer ke profil staf
$topLink = hasRole('user') ? BASE_URL . '/user/profile.php' : BASE_URL . '/admin/profile.php';

// Keterangan di bawah nama sesuai role
$roleLabels = ['user' => 'Pelamar', 'hr' => 'HR', 'admin' => 'Admin'];
$topRole = $roleLabels[$topUser['role'] ?? 'user'] ?? '';
?>
<a href="<?= $topLink ?>" class="topbar-user" title="Profil Saya">
    <!-- Nama di kiri (rata kanan), foto di kanan -->
    <span class="d-none d-sm-flex flex-column text-end lh-sm">
        <span class="fw-bold text-dark small"><?= htmlspecialchars($topUser['nama'] ?? '') ?></span>
        <span class="text-muted" style="font-size: 0.72rem;">
            <?= $topRole ?>
        </span>
    </span>

    <?php if (!empty($topUser['profile_photo'])): ?>
        <img src="<?= BASE_URL ?>/uploads/profiles/<?= htmlspecialchars($topUser['profile_photo']) ?>"
             class="topbar-avatar" alt="Foto profil">
    <?php else: ?>
        <!-- Jika belum ada foto, tampilkan huruf awal nama -->
        <span class="topbar-avatar topbar-avatar-initial">
            <?= strtoupper(substr($topUser['nama'] ?? 'U', 0, 1)) ?>
        </span>
    <?php endif; ?>
</a>
