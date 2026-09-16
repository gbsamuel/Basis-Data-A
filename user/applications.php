<?php
require_once __DIR__ . '/../config/database.php';
requireRole('user');

$pdo = getDB();
$user = currentUser();
$nik = $user['nik'];

$pageTitle = 'Daftar Lamaran Saya - SIREKA';
$activeSidebar = 'applications';

$statusFilter = trim($_GET['status'] ?? '');

$sql = "
    SELECT a.*, j.nama_job, j.job_type, j.location, c.nama_company, d.nama_divisi,
           (SELECT COUNT(*) FROM interview i WHERE i.id_application = a.id_application AND i.status = 'Scheduled') as has_interview,
           (SELECT id_loa FROM loa l WHERE l.id_application = a.id_application) as loa_id
    FROM application a
    JOIN job j ON a.id_job = j.id_job
    JOIN division d ON j.id_division = d.id_division
    JOIN company c ON d.id_company = c.id_company
    WHERE a.nik = ?
";
$params = [$nik];

if ($statusFilter !== '') {
    $sql .= " AND a.current_status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY a.applied_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

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
                    <h5 class="fw-bold mb-0">Riwayat Lamaran Pekerjaan</h5>
                    <small class="text-muted">Kelola dan pantau seluruh posisi yang telah Anda lamar</small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/user/jobs.php" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Cari Lowongan Baru
            </a>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Status Tabs Filter -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="<?= BASE_URL ?>/user/applications.php" class="btn btn-sm <?= $statusFilter === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Semua Lamaran
                </a>
                <a href="<?= BASE_URL ?>/user/applications.php?status=Applied" class="btn btn-sm <?= $statusFilter === 'Applied' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Applied
                </a>
                <a href="<?= BASE_URL ?>/user/applications.php?status=HR+Review" class="btn btn-sm <?= $statusFilter === 'HR Review' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    HR Review
                </a>
                <a href="<?= BASE_URL ?>/user/applications.php?status=Interview" class="btn btn-sm <?= $statusFilter === 'Interview' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Interview
                </a>
                <a href="<?= BASE_URL ?>/user/applications.php?status=Accepted" class="btn btn-sm <?= $statusFilter === 'Accepted' ? 'btn-success text-white' : 'btn-outline-success' ?>">
                    Accepted (Diterima)
                </a>
                <a href="<?= BASE_URL ?>/user/applications.php?status=Rejected" class="btn btn-sm <?= $statusFilter === 'Rejected' ? 'btn-danger text-white' : 'btn-outline-danger' ?>">
                    Rejected
                </a>
            </div>

            <!-- Applications Cards / Table -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <?php if (empty($applications)): ?>
                    <div class="text-center py-5">
                        <div class="text-muted display-4 mb-2"><i class="bi bi-inbox"></i></div>
                        <h5 class="fw-bold">Tidak Ada Lamaran Ditemukan</h5>
                        <p class="text-muted small">Anda belum memiliki lamaran dengan status yang dipilih.</p>
                        <a href="<?= BASE_URL ?>/user/jobs.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i> Mulai Melamar Pekerjaan
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Posisi & Perusahaan</th>
                                    <th>Tanggal Apply</th>
                                    <th>Match Score</th>
                                    <th>Status Saat Ini</th>
                                    <th>Berkas CV</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($app['nama_job']) ?></div>
                                            <div class="small text-muted">
                                                <i class="bi bi-building me-1"></i> <?= htmlspecialchars($app['nama_company']) ?> &bull; <?= htmlspecialchars($app['nama_divisi']) ?>
                                            </div>
                                            <small class="badge bg-light text-muted border mt-1"><?= htmlspecialchars($app['job_type']) ?></small>
                                        </td>
                                        <td class="small text-muted">
                                            <?= formatTanggalIndo($app['applied_at']) ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress-match flex-grow-1" style="width: 70px;">
                                                    <div class="progress-bar <?= $app['match_score'] >= 80 ? 'bg-success' : ($app['match_score'] >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                                         style="width: <?= $app['match_score'] ?>%"></div>
                                                </div>
                                                <span class="small fw-bold"><?= $app['match_score'] ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($app['current_status']) ?>
                                            <?php if ($app['has_interview'] > 0): ?>
                                                <span class="badge bg-warning text-dark mt-1 d-inline-block">
                                                    <i class="bi bi-calendar-check me-1"></i> Jadwal Interview Ada
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/uploads/cv/<?= htmlspecialchars($app['cv_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-file-earmark-pdf me-1"></i> Lihat CV
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="<?= BASE_URL ?>/user/tracking.php?id=<?= $app['id_application'] ?>" class="btn btn-sm btn-outline-primary" title="Tracking Visual">
                                                    <i class="bi bi-clock-history me-1"></i> Tracking
                                                </a>
                                                <?php if ($app['current_status'] === 'Accepted'): ?>
                                                    <a href="<?= BASE_URL ?>/user/loa.php?app_id=<?= $app['id_application'] ?>" class="btn btn-sm btn-success" title="Cetak Surat Penerimaan">
                                                        <i class="bi bi-award me-1"></i> LoA
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
