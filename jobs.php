<?php
require_once __DIR__ . '/config/database.php';
$pdo = getDB();

$company = $pdo->query("SELECT * FROM company WHERE id_company = 1 LIMIT 1")->fetch();
$companyName = $company['nama_company'] ?? 'PT Solusi Teknologi Nusantara';

$pageTitle = 'Eksplorasi Lowongan IT - ' . $companyName;
$activePage = 'jobs';

$user = currentUser();
$userNik = $user['nik'] ?? null;

// Filter params
$search = trim($_GET['search'] ?? '');
$jobType = trim($_GET['type'] ?? '');
$divisionId = !empty($_GET['division']) ? (int)$_GET['division'] : null;

// Build query
$sql = "
    SELECT j.*, d.nama_divisi, c.nama_company, c.id_company,
           (SELECT COUNT(*) FROM application a WHERE a.id_job = j.id_job) AS total_applicants
    FROM job j
    JOIN division d ON j.id_division = d.id_division
    JOIN company c ON d.id_company = c.id_company
    WHERE 1=1
";
$params = [];

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

// Get divisions list for filter
$divisions = $pdo->query("SELECT id_division, nama_divisi FROM division WHERE id_company = 1 ORDER BY nama_divisi ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="bg-corporate-blue py-5 text-white">
    <div class="container py-3">
        <span class="badge bg-white text-primary mb-2 px-3 py-2 rounded-pill fw-bold shadow-sm">
            <i class="bi bi-briefcase-fill me-1"></i> Open Tech Positions
        </span>
        <h1 class="fw-bold text-white mb-2">Lowongan Karier Teknologi</h1>
        <p class="text-light opacity-90 mb-0">Temukan posisi yang cocok untuk keahlian Anda di <?= htmlspecialchars($companyName) ?>.</p>
    </div>
</div>

<div class="container py-5">
    <!-- Filter Card -->
    <div class="card-custom p-4 mb-4 border-0 shadow-sm">
        <form method="GET" action="<?= BASE_URL ?>/jobs.php" class="row g-3">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Cari Posisi / Keahlian / Lokasi</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="cth: Backend, Data Analyst, Cloud..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Divisi Teknologi</label>
                <select name="division" id="filter_division" class="form-select">
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
                <select name="type" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="Kerja" <?= $jobType === 'Kerja' ? 'selected' : '' ?>>Kerja (Full-Time)</option>
                    <option value="Magang" <?= $jobType === 'Magang' ? 'selected' : '' ?>>Magang (Internship)</option>
                    <option value="Management Trainee" <?= $jobType === 'Management Trainee' ? 'selected' : '' ?>>Management Trainee (MT)</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="<?= BASE_URL ?>/jobs.php" class="btn btn-outline-secondary">Reset Filter</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-funnel me-1"></i> Terapkan Filter</button>
            </div>
        </form>
    </div>

    <!-- Jobs Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted fw-semibold">Menampilkan <strong><?= count($jobs) ?></strong> lowongan pekerjaan teknologi</span>
        <?php if ($userNik): ?>
            <span class="badge bg-light text-primary border"><i class="bi bi-person-check me-1"></i> Match score dihitung otomatis dengan profil skill Anda</span>
        <?php endif; ?>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="card-custom p-5 text-center my-4">
            <div class="display-1 text-muted mb-3"><i class="bi bi-search"></i></div>
            <h4 class="fw-bold">Tidak ada lowongan yang sesuai kriteria</h4>
            <p class="text-muted">Coba ubah kata kunci pencarian atau reset filter untuk melihat lowongan lainnya.</p>
            <div>
                <a href="<?= BASE_URL ?>/jobs.php" class="btn btn-primary">Lihat Semua Lowongan</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($jobs as $job): ?>
                <?php
                $matchScore = null;
                if ($userNik) {
                    $matchScore = calculateMatchScore($pdo, $userNik, $job['id_job']);
                }
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card-custom h-100 p-4 d-flex flex-column card-hover position-relative">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="badge bg-primary-light text-primary border border-primary-subtle">
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
                            <?php if ($matchScore !== null): ?>
                                <span class="score-badge <?= $matchScore >= 80 ? 'score-high' : ($matchScore >= 50 ? 'score-medium' : 'score-low') ?>" title="Kecocokan Skill Anda">
                                    <i class="bi bi-bullseye"></i> <?= $matchScore ?>% Match
                                </span>
                            <?php endif; ?>
                        </div>
                        <h5 class="fw-bold mb-1">
                            <a href="<?= BASE_URL ?>/job_detail.php?id=<?= $job['id_job'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($job['nama_job']) ?>
                            </a>
                        </h5>
                        <div class="text-muted small mb-2 fw-semibold">
                            <i class="bi bi-diagram-3-fill text-primary me-1"></i> Divisi: <?= htmlspecialchars($job['nama_divisi']) ?>
                        </div>
                        <p class="text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($job['deskripsi']) ?>
                        </p>
                        <div class="pt-3 border-top d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($job['location']) ?></span>
                                <span><i class="bi bi-cash-stack me-1"></i> <?= formatRupiah($job['salary_min']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2">
                                <small class="text-muted">Deadline: <?= formatTanggalIndo($job['deadline']) ?></small>
                                <a href="<?= BASE_URL ?>/job_detail.php?id=<?= $job['id_job'] ?>" class="btn btn-sm btn-primary">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
