<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$pageTitle = 'Ulasan & Feedback Pelamar - SIREKA Admin';
$activeSidebar = 'feedback';

// Compute average rating
$avgRating = $pdo->query("SELECT AVG(rating) FROM feedback")->fetchColumn();
$totalFeedback = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();

// Fetch feedback list
$stmt = $pdo->query("
    SELECT f.*, u.nama as nama_kandidat, u.email, u.pendidikan_terakhir
    FROM feedback f
    JOIN user_all u ON f.nik = u.nik
    ORDER BY f.created_at DESC
");
$feedbacks = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/sidebar_admin.php'; ?>

    <main class="main-content">
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0">Ulasan & Feedback Pelamar</h5>
                    <small class="text-muted">Evaluasi kepuasan pengguna terhadap platform dan proses rekrutmen</small>
                </div>
            </div>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Rating Overview Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card-custom p-4 border-0 shadow-sm d-flex align-items-center gap-4">
                        <div class="display-4 fw-extrabold text-warning">
                            <?= number_format((float)($avgRating ?? 5.0), 1) ?>
                        </div>
                        <div>
                            <div class="mb-1">
                                <?php
                                $roundAvg = round((float)($avgRating ?? 5));
                                for ($s = 1; $s <= 5; $s++) {
                                    echo "<i class='bi bi-star-fill " . ($s <= $roundAvg ? "text-warning" : "text-muted opacity-25") . " fs-5'></i> ";
                                }
                                ?>
                            </div>
                            <span class="fw-bold text-dark d-block">Rata-Rata Kepuasan Pelamar</span>
                            <small class="text-muted">Berdasarkan <?= (int)$totalFeedback ?> ulasan terverifikasi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-custom p-4 border-0 shadow-sm d-flex align-items-center gap-3 bg-light">
                        <div class="bg-primary-light text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-chat-quote fs-2"></i>
                        </div>
                        <div>
                            <span class="fw-bold text-dark d-block">Mendukung SDGs 8</span>
                            <small class="text-muted">
                                Umpan balik transparansi memastikan proses seleksi adil, terbuka, dan menghargai setiap kandidat.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedbacks Grid -->
            <div class="row g-4">
                <?php if (empty($feedbacks)): ?>
                    <div class="col-12">
                        <div class="card-custom p-5 text-center text-muted">
                            <i class="bi bi-chat-square-dots fs-1 d-block mb-2"></i>
                            Belum ada ulasan yang masuk.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($feedbacks as $fb): ?>
                        <div class="col-md-6">
                            <div class="card-custom p-4 h-100 border-0 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($fb['nama_kandidat']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($fb['email']) ?></small>
                                    </div>
                                    <div>
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <i class="bi bi-star-fill <?= $s <= $fb['rating'] ? 'text-warning' : 'text-muted opacity-25' ?> small"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-secondary small mb-3 flex-grow-1" style="line-height: 1.6;">
                                    "<?= nl2br(htmlspecialchars($fb['message'])) ?>"
                                </p>
                                <div class="pt-2 border-top text-muted" style="font-size: 0.75rem;">
                                    Dikirim pada: <?= formatTanggalIndo($fb['created_at']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
