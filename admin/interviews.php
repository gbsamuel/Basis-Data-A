<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$pageTitle = 'Manajemen Jadwal Wawancara - SIREKA Admin';
$activeSidebar = 'interviews';

// Update interview status
if (isset($_GET['set_status']) && isset($_GET['id'])) {
    $itwId = (int)$_GET['id'];
    $st = trim($_GET['set_status']);
    if (in_array($st, ['Scheduled', 'Completed', 'Cancelled'])) {
        $stmt = $pdo->prepare("UPDATE interview SET status = ? WHERE id_interview = ?");
        $stmt->execute([$st, $itwId]);
        setFlash('success', "Status sesi interview berhasil diperbarui menjadi {$st}.");
    }
    header('Location: ' . BASE_URL . '/admin/interviews.php');
    exit;
}

// Fetch interviews
$stmt = $pdo->query("
    SELECT i.*, a.id_application, u.nama as nama_kandidat, u.email as email_kandidat, u.no_telepon as telp_kandidat,
           j.nama_job, c.nama_company, itw.nama as nama_interviewer
    FROM interview i
    JOIN application a ON i.id_application = a.id_application
    JOIN user_all u ON a.nik = u.nik
    JOIN job j ON a.id_job = j.id_job
    JOIN division d ON j.id_division = d.id_division
    JOIN company c ON d.id_company = c.id_company
    JOIN interviewer itw ON i.id_interviewer = itw.id_interviewer
    ORDER BY i.tanggal DESC, i.waktu DESC
");
$interviews = $stmt->fetchAll();

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
                    <h5 class="fw-bold mb-0">Manajemen Jadwal Wawancara (Interview)</h5>
                    <small class="text-muted">Pantau jadwal wawancara teknis, user, dan HR bersama kandidat</small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/admin/applications.php?status=Document+Screening" class="btn btn-sm btn-primary">
                <i class="bi bi-calendar-plus me-1"></i> Jadwalkan dari Lamaran
            </a>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kandidat</th>
                                <th>Posisi & Perusahaan</th>
                                <th>Tanggal & Waktu</th>
                                <th>Pewawancara</th>
                                <th>Tipe / Link</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($interviews)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Belum ada jadwal interview yang dibuat.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($interviews as $itw): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($itw['nama_kandidat']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($itw['telp_kandidat']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($itw['nama_job']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($itw['nama_company']) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= formatTanggalIndo($itw['tanggal']) ?></strong>
                                            <small class="text-muted d-block"><?= substr($itw['waktu'], 0, 5) ?> WIB</small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($itw['nama_interviewer']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= $itw['type'] ?></span>
                                            <?php if (!empty($itw['meeting_link'])): ?>
                                                <a href="<?= htmlspecialchars($itw['meeting_link']) ?>" target="_blank" class="small d-block text-primary text-truncate" style="max-width: 150px;">
                                                    <i class="bi bi-link me-1"></i> Link Meeting
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $itw['status'] === 'Scheduled' ? 'bg-warning text-dark' : ($itw['status'] === 'Completed' ? 'bg-success' : 'bg-secondary') ?>">
                                                <?= $itw['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Status
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/interviews.php?set_status=Completed&id=<?= $itw['id_interview'] ?>">Tandai Selesai (Completed)</a></li>
                                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/interviews.php?set_status=Cancelled&id=<?= $itw['id_interview'] ?>">Batalkan (Cancelled)</a></li>
                                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/interviews.php?set_status=Scheduled&id=<?= $itw['id_interview'] ?>">Jadwalkan Ulang (Scheduled)</a></li>
                                                </ul>
                                            </div>
                                            <a href="<?= BASE_URL ?>/admin/application_detail.php?id=<?= $itw['id_application'] ?>" class="btn btn-sm btn-primary ms-1">
                                                Dossier
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
