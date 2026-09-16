<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$adminUser = currentUser();
$adminNik = $adminUser['nik'];

$appId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch application dossier
$stmt = $pdo->prepare("
    SELECT a.*, 
           u.nama as nama_kandidat, u.nik, u.email as email_kandidat, u.no_telepon, u.tanggal_lahir,
           u.pendidikan_terakhir, u.tahun_lulus, u.alamat as alamat_kandidat,
           j.nama_job, j.job_type, j.location, j.salary_min, j.salary_max, j.id_job,
           c.id_company, c.nama_company, c.alamat as alamat_company, c.email_corporate,
           d.nama_divisi,
           (SELECT id_loa FROM loa l WHERE l.id_application = a.id_application) as loa_id
    FROM application a
    JOIN user_all u ON a.nik = u.nik
    JOIN job j ON a.id_job = j.id_job
    JOIN division d ON j.id_division = d.id_division
    JOIN company c ON d.id_company = c.id_company
    WHERE a.id_application = ?
");
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
    setFlash('danger', 'Data berkas lamaran tidak ditemukan.');
    header('Location: ' . BASE_URL . '/admin/applications.php');
    exit;
}

// Handle Update Recruitment Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_status'])) {
    $newStatus = trim($_POST['new_status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $allowedStatuses = [
        'Applied', 'HR Review', 'Document Screening', 
        'Interview Scheduling', 'Interview', 'Final Decision', 
        'Accepted', 'Rejected', 'Talent Pool'
    ];

    if (in_array($newStatus, $allowedStatuses)) {
        // 1. Update status in APPLICATION table
        $stmtUpdate = $pdo->prepare("UPDATE application SET current_status = ?, updated_at = NOW() WHERE id_application = ?");
        $stmtUpdate->execute([$newStatus, $appId]);

        // 2. Record stage history in relational table CANDIDATE_STAGE_HISTORY
        recordStageHistory($pdo, $appId, $newStatus, 'Completed', $notes, $adminNik);

        // 3. Automated action if Accepted => generate LOA if not exists
        if ($newStatus === 'Accepted') {
            $stmtLoaCheck = $pdo->prepare("SELECT id_loa FROM loa WHERE id_application = ?");
            $stmtLoaCheck->execute([$appId]);
            if (!$stmtLoaCheck->fetch()) {
                $compInit = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $app['nama_company']), 0, 3));
                $loaNumber = "LOA/{$compInit}-HC/" . date('Y') . "/" . strtoupper(date('M')) . "/" . str_pad($appId, 4, '0', STR_PAD_LEFT);
                $joinDate = date('Y-m-d', strtotime('+14 days'));
                $signer = getSystemSetting($pdo, 'loa_authorized_signer', 'Dr. Hendra Gunawan, S.E., M.M. (VP Human Capital)');

                $stmtNewLoa = $pdo->prepare("
                    INSERT INTO loa (id_application, loa_number, issue_date, join_date, position, division, status, authorized_by, notes)
                    VALUES (?, ?, CURDATE(), ?, ?, ?, 'Issued', ?, ?)
                ");
                $stmtNewLoa->execute([
                    $appId, $loaNumber, $joinDate, $app['nama_job'], $app['nama_divisi'], $signer,
                    'Selamat bergabung di perusahaan kami. Mohon membawa dokumen kelengkapan asli pada hari pertama bergabung.'
                ]);
            }
        }

        setFlash('success', "Status lamaran berhasil diperbarui menjadi {$newStatus}. Riwayat tahapan tersimpan di database.");
        header('Location: ' . BASE_URL . '/admin/application_detail.php?id=' . $appId);
        exit;
    }
}

// Handle Schedule Interview from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_schedule_interview'])) {
    $idInterviewer = (int)($_POST['id_interviewer'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? '');
    $waktu = trim($_POST['waktu'] ?? '');
    $type = trim($_POST['type'] ?? 'Online');
    $location = trim($_POST['location'] ?? '');
    $meetingLink = trim($_POST['meeting_link'] ?? '');
    $itwNotes = trim($_POST['notes'] ?? '');

    if ($idInterviewer > 0 && !empty($tanggal) && !empty($waktu)) {
        // Insert interview
        $stmtItw = $pdo->prepare("
            INSERT INTO interview (id_application, id_interviewer, tanggal, waktu, type, location, meeting_link, notes, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Scheduled', NOW())
        ");
        $stmtItw->execute([$appId, $idInterviewer, $tanggal, $waktu, $type, $location, $meetingLink, $itwNotes]);

        // Auto update application status to 'Interview'
        $pdo->prepare("UPDATE application SET current_status = 'Interview', updated_at = NOW() WHERE id_application = ?")->execute([$appId]);
        recordStageHistory($pdo, $appId, 'Interview', 'Current', "Jadwal wawancara ditetapkan pada {$tanggal} pukul {$waktu}. {$itwNotes}", $adminNik);

        setFlash('success', 'Sesi interview berhasil dijadwalkan dan status lamaran otomatis disinkronkan ke Interview.');
        header('Location: ' . BASE_URL . '/admin/application_detail.php?id=' . $appId);
        exit;
    } else {
        setFlash('danger', 'Pewawancara, tanggal, dan waktu wajib diisi.');
    }
}

// Skills match calculation details
$skillsDetail = getJobSkillsDetail($pdo, $app['nik'], $app['id_job']);

// Fetch history
$stmtHist = $pdo->prepare("
    SELECT h.*, u.nama as nama_petugas
    FROM candidate_stage_history h
    LEFT JOIN user_all u ON h.changed_by = u.nik
    WHERE h.id_application = ?
    ORDER BY h.changed_at DESC, h.id_history DESC
");
$stmtHist->execute([$appId]);
$histories = $stmtHist->fetchAll();

// Fetch scheduled interview
$stmtItwData = $pdo->prepare("
    SELECT i.*, itw.nama as nama_interviewer
    FROM interview i
    JOIN interviewer itw ON i.id_interviewer = itw.id_interviewer
    WHERE i.id_application = ?
    ORDER BY i.created_at DESC
    LIMIT 1
");
$stmtItwData->execute([$appId]);
$existingInterview = $stmtItwData->fetch();

// Interviewers list for modal
$interviewers = $pdo->prepare("SELECT * FROM interviewer WHERE id_company = ? ORDER BY nama ASC");
$interviewers->execute([$app['id_company']]);
$interviewersList = $interviewers->fetchAll();

$pageTitle = 'Dossier Seleksi: ' . htmlspecialchars($app['nama_kandidat']) . ' - SIREKA Admin';
$activeSidebar = 'applications';
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
                    <h5 class="fw-bold mb-0">Dossier Penilaian Lamaran #APP-<?= str_pad($app['id_application'], 4, '0', STR_PAD_LEFT) ?></h5>
                    <small class="text-muted"><?= htmlspecialchars($app['nama_kandidat']) ?> &bull; <?= htmlspecialchars($app['nama_job']) ?></small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/admin/applications.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Lamaran
            </a>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <div class="row g-4">
                <!-- Left Column: Candidate & Job Details -->
                <div class="col-lg-8">
                    <!-- Candidate Dossier Card -->
                    <div class="card-custom p-4 mb-4 border-0 shadow-sm">
                        <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">
                                    <?= strtoupper(substr($app['nama_kandidat'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($app['nama_kandidat']) ?></h4>
                                    <span class="text-muted small">NIK: <?= htmlspecialchars($app['nik']) ?> &bull; <?= htmlspecialchars($app['pendidikan_terakhir']) ?> (Lulus <?= $app['tahun_lulus'] ?>)</span>
                                </div>
                            </div>
                            <div>
                                <a href="<?= BASE_URL ?>/uploads/cv/<?= htmlspecialchars($app['cv_file']) ?>" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Unduh / Buka CV
                                </a>
                            </div>
                        </div>

                        <div class="row g-3 small mb-4">
                            <div class="col-md-6">
                                <span class="text-muted d-block">Alamat Email:</span>
                                <strong><?= htmlspecialchars($app['email_kandidat']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nomor Telepon:</span>
                                <strong><?= htmlspecialchars($app['no_telepon']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Tanggal Lahir:</span>
                                <strong><?= formatTanggalIndo($app['tanggal_lahir']) ?></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Alamat Domisili:</span>
                                <strong><?= htmlspecialchars($app['alamat_kandidat']) ?></strong>
                            </div>
                            <?php if (!empty($app['portfolio_url'])): ?>
                                <div class="col-12">
                                    <span class="text-muted d-block">Tautan Portofolio / GitHub / LinkedIn:</span>
                                    <a href="<?= htmlspecialchars($app['portfolio_url']) ?>" target="_blank" class="fw-bold text-primary">
                                        <?= htmlspecialchars($app['portfolio_url']) ?> <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($app['cover_letter'])): ?>
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <strong class="small d-block mb-1 text-dark">Surat Pengantar (Cover Letter):</strong>
                                <p class="text-secondary small mb-0" style="white-space: pre-line;"><?= htmlspecialchars($app['cover_letter']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Match Score & Skills Evaluation Card -->
                    <div class="card-custom p-4 mb-4 border-primary-subtle shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-bullseye text-primary me-2"></i> Evaluasi Skill Match (Kecocokan Posisi)</h5>
                                <small class="text-muted">Formula: (<?= $skillsDetail['matched_count'] ?> Cocok / <?= $skillsDetail['total_required'] ?> Dibutuhkan) &times; 100%</small>
                            </div>
                            <div class="text-end">
                                <span class="fs-3 fw-extrabold text-primary"><?= $skillsDetail['score'] ?>%</span>
                                <span class="badge <?= $skillsDetail['score'] >= 80 ? 'bg-success' : ($skillsDetail['score'] >= 50 ? 'bg-warning text-dark' : 'bg-danger') ?> ms-1">
                                    <?= $skillsDetail['score'] >= 80 ? 'High Match' : ($skillsDetail['score'] >= 50 ? 'Medium Match' : 'Low Match') ?>
                                </span>
                            </div>
                        </div>

                        <div class="progress-match mb-3" style="height: 10px;">
                            <div class="progress-bar <?= $skillsDetail['score'] >= 80 ? 'bg-success' : ($skillsDetail['score'] >= 50 ? 'bg-warning' : 'bg-danger') ?>" style="width: <?= $skillsDetail['score'] ?>%"></div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($skillsDetail['skills'] as $sk): ?>
                                <span class="badge <?= $sk['matched'] ? 'bg-success text-white' : 'bg-light text-muted border' ?> p-2 d-inline-flex align-items-center gap-1.5">
                                    <i class="bi <?= $sk['matched'] ? 'bi-check-circle-fill' : 'bi-x-circle' ?>"></i>
                                    <?= htmlspecialchars($sk['nama_skill']) ?>
                                    <small class="opacity-75"><?= $sk['matched'] ? '(Tersedia)' : '(Belum Ada)' ?></small>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Stage History Timeline Log -->
                    <div class="card-custom p-4 border-0 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-clock-history text-primary me-2"></i> Riwayat Perkembangan Tahapan Seleksi
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle small mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tahapan</th>
                                        <th>Status</th>
                                        <th>Catatan Evaluasi</th>
                                        <th>Petugas HR</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($histories as $h): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($h['stage']) ?></strong></td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($h['status']) ?></span></td>
                                            <td class="text-secondary"><?= htmlspecialchars($h['notes'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($h['nama_petugas'] ?? 'Pelamar') ?></td>
                                            <td class="text-muted"><?= date('d M Y, H:i', strtotime($h['changed_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Status Controls & Interview Scheduler -->
                <div class="col-lg-4">
                    <!-- Update Status Card -->
                    <div class="card-custom p-4 mb-4 border-0 shadow-sm bg-white">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-sliders text-primary me-2"></i> Perbarui Status Seleksi
                        </h5>
                        <div class="mb-3">
                            <span class="small text-muted d-block mb-1">Status Saat Ini:</span>
                            <?= getStatusBadge($app['current_status']) ?>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>/admin/application_detail.php?id=<?= $appId ?>">
                            <input type="hidden" name="action_update_status" value="1">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Pilih Status Baru <span class="text-danger">*</span></label>
                                <select name="new_status" class="form-select" required>
                                    <option value="Applied" <?= $app['current_status'] === 'Applied' ? 'selected' : '' ?>>Applied</option>
                                    <option value="HR Review" <?= $app['current_status'] === 'HR Review' ? 'selected' : '' ?>>HR Review</option>
                                    <option value="Document Screening" <?= $app['current_status'] === 'Document Screening' ? 'selected' : '' ?>>Document Screening</option>
                                    <option value="Interview Scheduling" <?= $app['current_status'] === 'Interview Scheduling' ? 'selected' : '' ?>>Interview Scheduling</option>
                                    <option value="Interview" <?= $app['current_status'] === 'Interview' ? 'selected' : '' ?>>Interview</option>
                                    <option value="Final Decision" <?= $app['current_status'] === 'Final Decision' ? 'selected' : '' ?>>Final Decision</option>
                                    <option value="Accepted" <?= $app['current_status'] === 'Accepted' ? 'selected' : '' ?>>Accepted (Terima & Terbitkan LoA)</option>
                                    <option value="Rejected" <?= $app['current_status'] === 'Rejected' ? 'selected' : '' ?>>Rejected (Tolak)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Catatan Evaluasi / Alasan</label>
                                <textarea name="notes" rows="3" class="form-control" placeholder="Tuliskan catatan internal atau alasan keputusan..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                <i class="bi bi-arrow-repeat me-1"></i> Simpan Status Baru
                            </button>
                        </form>

                        <?php if ($app['current_status'] === 'Accepted' && !empty($app['loa_id'])): ?>
                            <div class="mt-3 pt-3 border-top">
                                <a href="<?= BASE_URL ?>/user/loa.php?app_id=<?= $appId ?>" target="_blank" class="btn btn-success btn-sm w-100 fw-bold">
                                    <i class="bi bi-award me-1"></i> Buka / Cetak Dokumen LoA
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Schedule Interview Card / Trigger -->
                    <div class="card-custom p-4 mb-4 border-0 shadow-sm bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h5 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-camera-video text-warning me-2"></i> Jadwal Interview
                            </h5>
                        </div>

                        <?php if ($existingInterview): ?>
                            <div class="p-3 bg-light rounded-3 border small mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Status:</strong>
                                    <span class="badge bg-warning text-dark"><?= $existingInterview['status'] ?></span>
                                </div>
                                <div class="mb-1"><i class="bi bi-calendar-check me-1 text-primary"></i> <?= formatTanggalIndo($existingInterview['tanggal']) ?> (<?= substr($existingInterview['waktu'], 0, 5) ?> WIB)</div>
                                <div class="mb-1"><i class="bi bi-person me-1 text-primary"></i> <?= htmlspecialchars($existingInterview['nama_interviewer']) ?></div>
                                <div class="mb-2"><i class="bi bi-laptop me-1 text-primary"></i> Tipe: <?= $existingInterview['type'] ?></div>
                                <?php if (!empty($existingInterview['meeting_link'])): ?>
                                    <a href="<?= htmlspecialchars($existingInterview['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-outline-success w-100">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tautan Meeting
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <button class="btn btn-outline-warning text-dark w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#interviewModal">
                            <i class="bi bi-calendar-plus me-1"></i> <?= $existingInterview ? 'Jadwalkan Ulang Interview' : 'Jadwalkan Interview' ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Schedule Interview -->
<div class="modal fade" id="interviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/application_detail.php?id=<?= $appId ?>">
                <input type="hidden" name="action_schedule_interview" value="1">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calendar-event me-2"></i> Jadwalkan Sesi Wawancara</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Pilih Pewawancara (Interviewer) <span class="text-danger">*</span></label>
                        <select name="id_interviewer" required class="form-select">
                            <option value="">Pilih Pewawancara...</option>
                            <?php foreach ($interviewersList as $itw): ?>
                                <option value="<?= $itw['id_interviewer'] ?>">
                                    <?= htmlspecialchars($itw['nama']) ?> (<?= htmlspecialchars($itw['position']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tanggal Wawancara <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" required class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Waktu / Jam <span class="text-danger">*</span></label>
                        <input type="time" name="waktu" required class="form-control" value="10:00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tipe Wawancara</label>
                        <select name="type" class="form-select">
                            <option value="Online">Online (Virtual)</option>
                            <option value="Offline">Offline (Tatap Muka)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Lokasi (Jika Offline)</label>
                        <input type="text" name="location" class="form-control" placeholder="Ruang Meeting Lt. 3">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Tautan Meeting Virtual (Jika Online)</label>
                        <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/xxx-xxxx-xxx">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Catatan / Panduan untuk Kandidat</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Siapkan portofolio dan berpakaian rapi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">Konfirmasi Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
