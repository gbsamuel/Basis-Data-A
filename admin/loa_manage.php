<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$pageTitle = 'Manajemen Letter of Acceptance (LoA) - SIREKA Admin';
$activeSidebar = 'loa';

// Fetch all LoAs
$stmt = $pdo->query("
    SELECT l.*, a.id_application, u.nama as nama_kandidat, u.nik, u.email,
           c.nama_company
    FROM loa l
    JOIN application a ON l.id_application = a.id_application
    JOIN user_all u ON a.nik = u.nik
    JOIN job j ON a.id_job = j.id_job
    JOIN division d ON j.id_division = d.id_division
    JOIN company c ON d.id_company = c.id_company
    ORDER BY l.issue_date DESC
");
$loas = $stmt->fetchAll();

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
                    <h5 class="fw-bold mb-0">Manajemen Letter of Acceptance (LoA)</h5>
                    <small class="text-muted">Daftar surat penerimaan kerja resmi yang telah diterbitkan sistem</small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/admin/applications.php?status=Accepted" class="btn btn-sm btn-primary">
                <i class="bi bi-file-earmark-check me-1"></i> Pelamar Diterima
            </a>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Surat LoA</th>
                                <th>Kandidat Penerima</th>
                                <th>Posisi & Divisi IT</th>
                                <th>Disahkan Oleh</th>
                                <th>Tgl Terbit</th>
                                <th>Tgl Mulai Kerja</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($loas)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        Belum ada surat penerimaan (LoA) yang diterbitkan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($loas as $l): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($l['loa_number']) ?></strong>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($l['nama_kandidat']) ?></div>
                                            <small class="text-muted">NIK: <?= htmlspecialchars($l['nik']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($l['position']) ?></div>
                                            <span class="badge bg-primary-light text-primary border border-primary-subtle"><?= htmlspecialchars($l['division']) ?></span>
                                        </td>
                                        <td>
                                            <small class="text-dark fw-semibold"><?= htmlspecialchars($l['authorized_by']) ?></small>
                                        </td>
                                        <td class="small text-muted">
                                            <?= formatTanggalIndo($l['issue_date']) ?>
                                        </td>
                                        <td class="small text-dark fw-bold">
                                            <?= formatTanggalIndo($l['join_date']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?= $l['status'] ?></span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/user/loa.php?app_id=<?= $l['id_application'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Cetak Surat">
                                                <i class="bi bi-printer me-1"></i> Cetak PDF
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
