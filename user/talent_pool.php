<?php
require_once __DIR__ . '/../config/database.php';
requireRole('user');

header('Location: ' . BASE_URL . '/user/dashboard.php');
exit;

// Check talent pool records
$stmt = $pdo->prepare("
    SELECT tp.*, c.nama_company, c.industri, a.id_application, j.nama_job
    FROM talent_pool tp
    JOIN company c ON tp.id_company = c.id_company
    LEFT JOIN application a ON tp.source_application = a.id_application
    LEFT JOIN job j ON a.id_job = j.id_job
    WHERE tp.nik = ?
    ORDER BY tp.added_at DESC
");
$stmt->execute([$nik]);
$talentRecords = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/sidebar_user.php'; ?>

    <main class="main-content">
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0">Status Talent Pool</h5>
                    <small class="text-muted">Bank Data Bakat & Kandidat Potensial Perusahaan</small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/user/jobs.php" class="btn btn-sm btn-primary">
                <i class="bi bi-search me-1"></i> Cari Lowongan Lainnya
            </a>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Concept Card -->
            <div class="card-custom p-4 mb-4 border-purple bg-light shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-purple text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-stars fs-2"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Mengenai Fitur Talent Pool</h4>
                        <p class="text-muted small mb-0">
                            <em>"Belum lolos bukan berarti hilang."</em> Sistem SIREKA tidak menghapus kandidat berprestasi yang belum terpilih karena kuota batch penuh. Data Anda tetap aktif dan tersimpan dalam radar HR untuk prioritas rekrutmen berikutnya.
                        </p>
                    </div>
                </div>
            </div>

            <?php if (empty($talentRecords)): ?>
                <div class="card-custom p-5 text-center border-0 shadow-sm">
                    <div class="display-4 text-muted mb-2"><i class="bi bi-person-badge"></i></div>
                    <h5 class="fw-bold">Anda Belum Terdaftar di Talent Pool Khusus</h5>
                    <p class="text-muted small">Jika Anda mengikuti seleksi dan memiliki profil yang menjanjikan, HR dapat merekomendasikan profil Anda ke dalam Talent Pool perusahaan.</p>
                    <a href="<?= BASE_URL ?>/user/jobs.php" class="btn btn-primary btn-sm">
                        Jelajahi Lowongan Pekerjaan
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($talentRecords as $tp): ?>
                        <div class="col-lg-6">
                            <div class="card-custom h-100 p-4 border-0 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge bg-purple text-white mb-1">Terdaftar di Talent Pool</span>
                                        <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($tp['nama_company']) ?></h5>
                                        <small class="text-muted"><?= htmlspecialchars($tp['industri']) ?></small>
                                    </div>
                                    <span class="badge <?= $tp['status'] === 'Available' ? 'bg-success' : ($tp['status'] === 'Considered' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                        <?= htmlspecialchars($tp['status']) ?>
                                    </span>
                                </div>

                                <div class="p-3 bg-light rounded-3 mb-3 small border">
                                    <?php if (!empty($tp['nama_job'])): ?>
                                        <div class="mb-2">
                                            <strong>Lamaran Asal:</strong> <?= htmlspecialchars($tp['nama_job']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mb-2">
                                        <strong>Tanggal Dimasukkan:</strong> <?= formatTanggalIndo($tp['added_at']) ?>
                                    </div>
                                    <div>
                                        <strong>Catatan Evaluasi HR:</strong><br>
                                        <span class="text-secondary"><?= nl2br(htmlspecialchars($tp['reason'])) ?></span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i> Data Tersimpan Aman</small>
                                    <a href="<?= BASE_URL ?>/user/profile.php" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-stars me-1"></i> Perbarui Keahlian
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
