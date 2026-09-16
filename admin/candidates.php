<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$pageTitle = 'Direktori Data Kandidat - SIREKA Admin';
$activeSidebar = 'candidates';

$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT u.*,
           COUNT(DISTINCT a.id_application) as total_applications,
           COUNT(DISTINCT us.id_skill) as total_skills,
           (SELECT current_status FROM application a2 WHERE a2.nik = u.nik ORDER BY a2.applied_at DESC LIMIT 1) as latest_status
    FROM user_all u
    LEFT JOIN application a ON u.nik = a.nik
    LEFT JOIN user_skill us ON u.nik = us.nik
    WHERE u.role = 'user'
";
$params = [];

if ($search !== '') {
    $sql .= " AND (u.nama LIKE ? OR u.nik LIKE ? OR u.email LIKE ? OR u.pendidikan_terakhir LIKE ?)";
    $term = "%{$search}%";
    $params = [$term, $term, $term, $term];
}

$sql .= " GROUP BY u.nik ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$candidates = $stmt->fetchAll();

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
                    <h5 class="fw-bold mb-0">Direktori Data Pelamar / Kandidat</h5>
                    <small class="text-muted">Daftar pencari kerja terdaftar dengan ringkasan keahlian dan status rekrutmen</small>
                </div>
            </div>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Search bar -->
            <div class="card-custom p-3 mb-4 border-0 shadow-sm">
                <form method="GET" action="<?= BASE_URL ?>/admin/candidates.php" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, NIK, email, pendidikan..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary px-3">Cari</button>
                        <a href="<?= BASE_URL ?>/admin/candidates.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                    <div class="col text-md-end small text-muted">
                        Total: <strong><?= count($candidates) ?></strong> Kandidat
                    </div>
                </form>
            </div>

            <!-- Candidates Table -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kandidat</th>
                                <th>Kontak</th>
                                <th>Pendidikan</th>
                                <th>Keahlian</th>
                                <th>Lamaran</th>
                                <th>Status Terakhir</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $c): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary-light text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                                <?= strtoupper(substr($c['nama'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($c['nama']) ?></div>
                                                <small class="text-muted">NIK: <?= htmlspecialchars($c['nik']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="small">
                                        <div><i class="bi bi-envelope me-1 text-primary"></i> <?= htmlspecialchars($c['email']) ?></div>
                                        <div><i class="bi bi-telephone me-1 text-success"></i> <?= htmlspecialchars($c['no_telepon']) ?></div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($c['pendidikan_terakhir']) ?></span>
                                        <small class="text-muted d-block">Lulus: <?= $c['tahun_lulus'] ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border">
                                            <?= $c['total_skills'] ?> Skill
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/applications.php?search=<?= urlencode($c['nama']) ?>" class="badge bg-primary text-white text-decoration-none">
                                            <?= $c['total_applications'] ?> Lamaran
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($c['latest_status']): ?>
                                            <?= getStatusBadge($c['latest_status']) ?>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Belum Melamar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/admin/applications.php?search=<?= urlencode($c['nama']) ?>" class="btn btn-sm btn-outline-primary" title="Lihat Riwayat Lamaran">
                                            <i class="bi bi-eye me-1"></i> Dossier
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
