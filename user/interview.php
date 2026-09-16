<?php
require_once __DIR__ . '/../config/database.php';
requireRole('user');

$pdo = getDB();
$user = currentUser();
$nik = $user['nik'];

$pageTitle = 'Jadwal Interview Saya - SIREKA';
$activeSidebar = 'interview';

$stmt = $pdo->prepare("
    SELECT i.*, j.nama_job, j.job_type, c.nama_company, c.alamat as alamat_company, d.nama_divisi,
           itw.nama as nama_interviewer, itw.position as jabatan_interviewer, itw.email as email_interviewer
    FROM interview i
    JOIN application a ON i.id_application = a.id_application
    JOIN job j ON a.id_job = j.id_job
    JOIN division d ON j.id_division = d.id_division
    JOIN company c ON d.id_company = c.id_company
    JOIN interviewer itw ON i.id_interviewer = itw.id_interviewer
    WHERE a.nik = ?
    ORDER BY i.tanggal DESC, i.waktu DESC
");
$stmt->execute([$nik]);
$interviews = $stmt->fetchAll();

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
                    <h5 class="fw-bold mb-0">Jadwal Sesi Wawancara (Interview)</h5>
                    <small class="text-muted">Informasi jadwal wawancara, lokasi, dan tautan pertemuan virtual</small>
                </div>
            </div>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <?php if (empty($interviews)): ?>
                <div class="card-custom p-5 text-center border-0 shadow-sm">
                    <div class="display-4 text-muted mb-2"><i class="bi bi-calendar2-x"></i></div>
                    <h5 class="fw-bold">Belum Ada Jadwal Interview</h5>
                    <p class="text-muted small">Jadwal wawancara akan muncul di sini setelah lamaran Anda lolos tahap Document Screening.</p>
                    <a href="<?= BASE_URL ?>/user/applications.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-clock-history me-1"></i> Pantau Status Lamaran
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($interviews as $itw): ?>
                        <div class="col-lg-6">
                            <div class="card-custom h-100 p-4 border-0 shadow-sm position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-primary-light text-primary"><?= htmlspecialchars($itw['job_type']) ?></span>
                                    <span class="badge <?= $itw['status'] === 'Scheduled' ? 'bg-warning text-dark' : ($itw['status'] === 'Completed' ? 'bg-success' : 'bg-secondary') ?>">
                                        <?= htmlspecialchars($itw['status']) ?>
                                    </span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($itw['nama_job']) ?></h5>
                                <div class="text-muted small mb-3">
                                    <i class="bi bi-building me-1"></i> <?= htmlspecialchars($itw['nama_company']) ?> &bull; <?= htmlspecialchars($itw['nama_divisi']) ?>
                                </div>

                                <div class="p-3 bg-light rounded-3 mb-3 small border">
                                    <div class="mb-2">
                                        <i class="bi bi-calendar-event me-2 text-primary"></i> <strong>Tanggal:</strong> <?= formatTanggalIndo($itw['tanggal']) ?>
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-clock me-2 text-primary"></i> <strong>Waktu:</strong> <?= substr($itw['waktu'], 0, 5) ?> WIB
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-person-badge me-2 text-primary"></i> <strong>Pewawancara:</strong> <?= htmlspecialchars($itw['nama_interviewer']) ?> (<?= htmlspecialchars($itw['jabatan_interviewer']) ?>)
                                    </div>
                                    <div class="mb-2">
                                        <i class="bi bi-laptop me-2 text-primary"></i> <strong>Tipe:</strong> <?= htmlspecialchars($itw['type']) ?>
                                    </div>
                                    <?php if ($itw['type'] === 'Offline' && !empty($itw['location'])): ?>
                                        <div>
                                            <i class="bi bi-geo-alt me-2 text-danger"></i> <strong>Lokasi:</strong> <?= htmlspecialchars($itw['location']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($itw['meeting_link'])): ?>
                                    <div class="d-grid mb-3">
                                        <a href="<?= htmlspecialchars($itw['meeting_link']) ?>" target="_blank" class="btn btn-success fw-bold">
                                            <i class="bi bi-camera-video me-1"></i> Buka Tautan Meeting Virtual
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($itw['notes'])): ?>
                                    <div class="small text-muted border-top pt-2">
                                        <strong>Catatan HR:</strong> <?= nl2br(htmlspecialchars($itw['notes'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
