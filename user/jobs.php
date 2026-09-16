<?php
require_once __DIR__ . '/../config/database.php';
requireRole('user');

$pdo = getDB();
$user = currentUser();
$nik = $user['nik'];

$pageTitle = 'Cari Lowongan Pekerjaan - SIREKA';
$activeSidebar = 'jobs';

$search = trim($_GET['search'] ?? '');
$jobType = trim($_GET['type'] ?? '');
$companyId = !empty($_GET['company']) ? (int)$_GET['company'] : null;
$divisionId = !empty($_GET['division']) ? (int)$_GET['division'] : null;

// Query jobs
$sql = "
    SELECT j.*, d.nama_divisi, c.nama_company, c.id_company,
           (SELECT id_application FROM application a WHERE a.id_job = j.id_job AND a.nik = ?) AS my_app_id
    FROM job j
    JOIN division d ON j.id_division = d.id_division
    JOIN company c ON d.id_company = c.id_company
    WHERE 1=1
";
$params = [$nik];

if ($search !== '') {
    $sql .= " AND (j.nama_job LIKE ? OR j.deskripsi LIKE ? OR j.location LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}
if ($jobType !== '') {
    $sql .= " AND j.job_type = ?";
    $params[] = $jobType;
}
if ($divisionId) {
    $sql .= " AND d.id_division = ?";
    $params[] = $divisionId;
}

$sql .= " ORDER BY j.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Dropdowns
$divisions = $pdo->query("SELECT id_division, nama_divisi FROM division WHERE id_company = 1 ORDER BY nama_divisi ASC")->fetchAll();

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
                    <h5 class="fw-bold mb-0">Eksplorasi Lowongan</h5>
                    <small class="text-muted">Pilih tipe pekerjaan, perusahaan, dan divisi sesuai minat Anda</small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/user/profile.php" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-stars me-1"></i> Update Keahlian Saya
            </a>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Filter Box -->
            <div class="card-custom p-4 mb-4 border-0 shadow-sm">
                <form method="GET" action="<?= BASE_URL ?>/user/jobs.php" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Cari Posisi</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="cth: Data Analyst, Backend..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Divisi IT</label>
                        <select name="division" id="filter_division" class="form-select form-select-sm">
                            <option value="">Semua Divisi IT</option>
                            <?php foreach ($divisions as $d): ?>
                                <option value="<?= $d['id_division'] ?>" <?= $divisionId === (int)$d['id_division'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['nama_divisi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tipe Pekerjaan</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">Semua Tipe</option>
                            <option value="Kerja" <?= $jobType === 'Kerja' ? 'selected' : '' ?>>Kerja (Full-Time)</option>
                            <option value="Magang" <?= $jobType === 'Magang' ? 'selected' : '' ?>>Magang (Internship)</option>
                            <option value="Management Trainee" <?= $jobType === 'Management Trainee' ? 'selected' : '' ?>>Management Trainee (MT)</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                        <a href="<?= BASE_URL ?>/user/jobs.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                        <button type="submit" class="btn btn-sm btn-primary px-3"><i class="bi bi-funnel me-1"></i> Terapkan Filter</button>
                    </div>
                </form>
            </div>

            <!-- Jobs List -->
            <div class="row g-4">
                <?php if (empty($jobs)): ?>
                    <div class="col-12">
                        <div class="card-custom p-5 text-center">
                            <i class="bi bi-search text-muted display-4 mb-3"></i>
                            <h5 class="fw-bold">Tidak ada lowongan yang sesuai filter</h5>
                            <p class="text-muted small">Coba sesuaikan filter atau reset untuk melihat semua lowongan aktif.</p>
                            <a href="<?= BASE_URL ?>/user/jobs.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <?php
                        $matchScore = calculateMatchScore($pdo, $nik, $job['id_job']);
                        $hasApplied = !empty($job['my_app_id']);
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card-custom h-100 p-4 d-flex flex-column card-hover position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="badge bg-primary-light text-primary border">
                                            <?= htmlspecialchars($job['job_type']) ?>
                                        </span>
                                        <?php if ($job['status'] === 'Open'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-record-circle me-1"></i>Buka
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                <i class="bi bi-lock-fill me-1"></i>Tutup
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="score-badge <?= $matchScore >= 80 ? 'score-high' : ($matchScore >= 50 ? 'score-medium' : 'score-low') ?>" title="Kecocokan Skill Anda">
                                        <i class="bi bi-bullseye"></i> <?= $matchScore ?>% Match
                                    </span>
                                </div>
                                <h5 class="fw-bold mb-1 text-dark">
                                    <?= htmlspecialchars($job['nama_job']) ?>
                                </h5>
                                <div class="text-muted small mb-2 fw-semibold">
                                    <i class="bi bi-diagram-3-fill text-primary me-1"></i> Divisi: <?= htmlspecialchars($job['nama_divisi']) ?>
                                </div>
                                <p class="text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($job['deskripsi']) ?>
                                </p>
                                <div class="pt-3 border-top">
                                    <div class="d-flex justify-content-between text-muted small mb-3">
                                        <span><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($job['location']) ?></span>
                                        <span class="text-success fw-bold"><?= formatRupiah($job['salary_min']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="<?= BASE_URL ?>/job_detail.php?id=<?= $job['id_job'] ?>" class="btn btn-sm btn-outline-secondary">
                                            Detail Info
                                        </a>
                                        <?php if ($hasApplied): ?>
                                            <a href="<?= BASE_URL ?>/user/tracking.php?id=<?= $job['my_app_id'] ?>" class="btn btn-sm btn-warning text-dark fw-bold">
                                                <i class="bi bi-clock-history me-1"></i> Tracking
                                            </a>
                                        <?php elseif ($job['status'] === 'Open'): ?>
                                            <a href="<?= BASE_URL ?>/user/apply.php?job_id=<?= $job['id_job'] ?>" class="btn btn-sm btn-primary">
                                                Lamar Posisi
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary opacity-75" disabled title="Pendaftaran lowongan ini telah ditutup">
                                                <i class="bi bi-lock me-1"></i> Ditutup
                                            </button>
                                        <?php endif; ?>
                                    </div>
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
