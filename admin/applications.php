<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$pageTitle = 'Manajemen Lamaran Masuk - SIREKA Admin';
$activeSidebar = 'applications';

$search = trim($_GET['search'] ?? '');
$jobFilter = !empty($_GET['job_id']) ? (int)$_GET['job_id'] : null;
$statusFilter = trim($_GET['status'] ?? '');

$sql = "
    SELECT a.*, u.nama as nama_kandidat, u.nik, u.email as email_kandidat, u.no_telepon, u.pendidikan_terakhir,
           j.nama_job, j.job_type, c.nama_company, d.nama_divisi,
           (SELECT COUNT(*) FROM interview i WHERE i.id_application = a.id_application AND i.status = 'Scheduled') as has_interview
    FROM application a
    JOIN user_all u ON a.nik = u.nik
    JOIN job j ON a.id_job = j.id_job
    JOIN division d ON j.id_division = d.id_division
    JOIN company c ON d.id_company = c.id_company
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (u.nama LIKE ? OR u.nik LIKE ? OR u.email LIKE ? OR j.nama_job LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($jobFilter) {
    $sql .= " AND a.id_job = ?";
    $params[] = $jobFilter;
}

if ($statusFilter !== '') {
    $sql .= " AND a.current_status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY a.applied_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Jobs list for filter
$jobsList = $pdo->query("SELECT id_job, nama_job FROM job ORDER BY nama_job ASC")->fetchAll();

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
                    <h5 class="fw-bold mb-0">Manajemen Lamaran Masuk</h5>
                    <small class="text-muted">Proses seleksi, verifikasi berkas, dan pembaharuan tahapan rekrutmen</small>
                </div>
            </div>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Filter Controls -->
            <div class="card-custom p-3 mb-4 border-0 shadow-sm">
                <form method="GET" action="<?= BASE_URL ?>/admin/applications.php" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari kandidat atau posisi..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="job_id" class="form-select form-select-sm">
                            <option value="">Semua Lowongan</option>
                            <?php foreach ($jobsList as $jl): ?>
                                <option value="<?= $jl['id_job'] ?>" <?= $jobFilter === (int)$jl['id_job'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($jl['nama_job']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="Applied" <?= $statusFilter === 'Applied' ? 'selected' : '' ?>>Applied</option>
                            <option value="HR Review" <?= $statusFilter === 'HR Review' ? 'selected' : '' ?>>HR Review</option>
                            <option value="Document Screening" <?= $statusFilter === 'Document Screening' ? 'selected' : '' ?>>Document Screening</option>
                            <option value="Interview Scheduling" <?= $statusFilter === 'Interview Scheduling' ? 'selected' : '' ?>>Interview Scheduling</option>
                            <option value="Interview" <?= $statusFilter === 'Interview' ? 'selected' : '' ?>>Interview</option>
                            <option value="Final Decision" <?= $statusFilter === 'Final Decision' ? 'selected' : '' ?>>Final Decision</option>
                            <option value="Accepted" <?= $statusFilter === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                            <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary px-3">Filter</button>
                        <a href="<?= BASE_URL ?>/admin/applications.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Table of Applications -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-semibold">Menemukan <strong><?= count($applications) ?></strong> berkas lamaran</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kandidat</th>
                                <th>Posisi & Divisi IT</th>
                                <th>Tgl Apply</th>
                                <th>Match Score</th>
                                <th>Status Tahapan</th>
                                <th>Berkas CV</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($applications)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Tidak ada data lamaran yang sesuai dengan filter pencarian.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($app['nama_kandidat']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($app['email_kandidat']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($app['nama_job']) ?></div>
                                            <small class="text-muted"><i class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars($app['nama_divisi']) ?></small>
                                        </td>
                                        <td class="small text-muted">
                                            <?= formatTanggalIndo($app['applied_at']) ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress-match flex-grow-1" style="width: 65px;">
                                                    <div class="progress-bar <?= $app['match_score'] >= 80 ? 'bg-success' : ($app['match_score'] >= 50 ? 'bg-warning' : 'bg-danger') ?>" style="width: <?= $app['match_score'] ?>%"></div>
                                                </div>
                                                <span class="small fw-bold"><?= $app['match_score'] ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($app['current_status']) ?>
                                            <?php if ($app['has_interview'] > 0): ?>
                                                <span class="badge bg-warning text-dark mt-1 d-block" style="font-size: 0.68rem;">
                                                    <i class="bi bi-calendar-check me-1"></i> Interview Terjadwal
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/uploads/cv/<?= htmlspecialchars($app['cv_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary py-1">
                                                <i class="bi bi-file-earmark-pdf me-1"></i> CV
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/admin/application_detail.php?id=<?= $app['id_application'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-sliders me-1"></i> Review & Status
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
