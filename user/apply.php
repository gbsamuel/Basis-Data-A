<?php
require_once __DIR__ . '/../config/database.php';
requireRole('user');

$pdo = getDB();
$user = currentUser();
$nik = $user['nik'];

$jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Fetch job details
$stmt = $pdo->prepare("
    SELECT j.*, d.nama_divisi, c.nama_company, c.id_company
    FROM job j
    JOIN division d ON j.id_division = d.id_division
    JOIN company c ON d.id_company = c.id_company
    WHERE j.id_job = ? AND j.status = 'Open'
");
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    setFlash('danger', 'Lowongan tidak ditemukan atau sudah ditutup.');
    header('Location: ' . BASE_URL . '/user/jobs.php');
    exit;
}

// Check duplicate application
$stmtCheck = $pdo->prepare("SELECT id_application FROM application WHERE nik = ? AND id_job = ?");
$stmtCheck->execute([$nik, $jobId]);
$existingApp = $stmtCheck->fetch();

if ($existingApp) {
    setFlash('info', 'Anda sudah pernah melamar untuk posisi ini.');
    header('Location: ' . BASE_URL . '/user/tracking.php?id=' . $existingApp['id_application']);
    exit;
}

// Calculate match score
$matchScore = calculateMatchScore($pdo, $nik, $jobId);
$skillsDetail = getJobSkillsDetail($pdo, $nik, $jobId);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coverLetter = trim($_POST['cover_letter'] ?? '');
    $portfolioUrl = trim($_POST['portfolio_url'] ?? '');
    $agreement = isset($_POST['agreement']);

    if (!$agreement) {
        $errors[] = 'Anda harus menyetujui pernyataan kebenaran data sebelum mengirimkan lamaran.';
    }

    if (empty($_FILES['cv_file']['name'])) {
        $errors[] = 'Dokumen CV wajib diunggah.';
    } else {
        $file = $_FILES['cv_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Format CV hanya boleh berupa file PDF, DOC, atau DOCX.';
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            $errors[] = 'Ukuran berkas CV maksimal 5 MB.';
        }
    }

    if (empty($errors)) {
        // Upload CV
        $uploadDir = __DIR__ . '/../uploads/cv/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'cv_' . $nik . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Insert into APPLICATION
            $stmtApp = $pdo->prepare("
                INSERT INTO application (nik, id_job, cv_file, cover_letter, portfolio_url, match_score, applied_at, current_status)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Applied')
            ");
            $stmtApp->execute([
                $nik,
                $jobId,
                $fileName,
                $coverLetter,
                $portfolioUrl,
                $matchScore
            ]);
            $newAppId = $pdo->lastInsertId();

            // Record initial history
            recordStageHistory(
                $pdo, 
                $newAppId, 
                'Applied', 
                'Completed', 
                'Berkas lamaran berhasil disubmit oleh kandidat ke sistem SIREKA.', 
                null
            );

            setFlash('success', 'Lamaran Anda berhasil dikirimkan! Pantau progres proses rekrutmen melalui timeline di bawah ini.');
            header('Location: ' . BASE_URL . '/user/tracking.php?id=' . $newAppId);
            exit;
        } else {
            $errors[] = 'Gagal menyimpan file CV ke server. Silakan coba kembali.';
        }
    }
}

$pageTitle = 'Formulir Lamaran Pekerjaan - SIREKA';
$activeSidebar = 'jobs';
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
                    <h5 class="fw-bold mb-0">Formulir Lamaran Pekerjaan</h5>
                    <small class="text-muted">Lengkapi berkas pendaftaran Anda dengan teliti</small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/job_detail.php?id=<?= $jobId ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Lowongan
            </a>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <div class="row g-4 justify-content-center">
                <div class="col-lg-10">
                    <!-- Position Summary Card with Match Score -->
                    <div class="card-custom p-4 mb-4 border-primary-subtle bg-white shadow-sm">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <span class="badge bg-primary-light text-primary mb-2"><?= htmlspecialchars($job['job_type']) ?></span>
                                <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($job['nama_job']) ?></h4>
                                <div class="text-muted small">
                                    <i class="bi bi-building me-1"></i> <?= htmlspecialchars($job['nama_company']) ?> &bull; 
                                    <i class="bi bi-diagram-3 me-1 ms-2"></i> <?= htmlspecialchars($job['nama_divisi']) ?> &bull; 
                                    <i class="bi bi-geo-alt me-1 ms-2"></i> <?= htmlspecialchars($job['location']) ?>
                                </div>
                            </div>
                            <div class="col-md-5 mt-3 mt-md-0 text-md-end border-start-md">
                                <span class="d-block small text-muted mb-1">Skor Kecocokan Keahlian (Match Score)</span>
                                <div class="d-inline-flex align-items-center gap-2">
                                    <span class="display-6 fw-extrabold text-primary"><?= $matchScore ?>%</span>
                                    <span class="badge <?= $matchScore >= 80 ? 'bg-success' : ($matchScore >= 50 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                        <?= $matchScore >= 80 ? 'Sangat Cocok' : ($matchScore >= 50 ? 'Cukup Cocok' : 'Perlu Peningkatan') ?>
                                    </span>
                                </div>
                                <div class="progress-match mt-2" style="height: 8px;">
                                    <div class="progress-bar <?= $matchScore >= 80 ? 'bg-success' : ($matchScore >= 50 ? 'bg-warning' : 'bg-danger') ?>" style="width: <?= $matchScore ?>%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 mt-3 border-top">
                            <span class="small fw-bold text-muted d-block mb-2">Keahlian yang Dibutuhkan Posisi Ini:</span>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($skillsDetail['skills'] as $sk): ?>
                                    <span class="badge <?= $sk['matched'] ? 'bg-success' : 'bg-light text-muted border' ?> p-1.5 px-2 small">
                                        <i class="bi <?= $sk['matched'] ? 'bi-check-circle-fill' : 'bi-x-circle' ?> me-1"></i>
                                        <?= htmlspecialchars($sk['nama_skill']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Application Form -->
                    <div class="card-custom p-4 p-md-5 border-0 shadow-sm">
                        <h5 class="fw-bold mb-4 pb-2 border-bottom"><i class="bi bi-file-earmark-arrow-up text-primary me-2"></i> Pengisian Formulir Lamaran</h5>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0 small ps-3">
                                    <?php foreach ($errors as $err): ?>
                                        <li><?= htmlspecialchars($err) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= BASE_URL ?>/user/apply.php?job_id=<?= $jobId ?>" enctype="multipart/form-data">
                            <!-- Pre-filled Candidate Biodata (Readonly) -->
                            <div class="p-3 bg-light rounded-3 mb-4 border">
                                <span class="small fw-bold text-muted text-uppercase d-block mb-3">Informasi Biodata Anda (Otomatis dari Akun)</span>
                                <div class="row g-3 small">
                                    <div class="col-md-6">
                                        <span class="text-muted d-block">Nama Lengkap:</span>
                                        <strong><?= htmlspecialchars($user['nama']) ?></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block">NIK:</span>
                                        <strong><?= htmlspecialchars($user['nik']) ?></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block">Alamat Email:</span>
                                        <strong><?= htmlspecialchars($user['email']) ?></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block">Nomor Telepon:</span>
                                        <strong><?= htmlspecialchars($user['no_telepon']) ?></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block">Pendidikan Terakhir:</span>
                                        <strong><?= htmlspecialchars($user['pendidikan_terakhir']) ?> (Lulus <?= $user['tahun_lulus'] ?>)</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block">Alamat Domisili:</span>
                                        <strong><?= htmlspecialchars($user['alamat']) ?></strong>
                                    </div>
                                </div>
                                <div class="mt-2 pt-2 border-top text-end">
                                    <a href="<?= BASE_URL ?>/user/profile.php" class="small text-primary fw-semibold" target="_blank">
                                        <i class="bi bi-pencil-square me-1"></i> Edit Biodata di Profil
                                    </a>
                                </div>
                            </div>

                            <!-- Upload CV -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Unggah Berkas CV (Curriculum Vitae) <span class="text-danger">*</span></label>
                                <input type="file" name="cv_file" class="form-control" accept=".pdf,.doc,.docx" required>
                                <small class="text-muted d-block mt-1">Format yang didukung: PDF, DOC, DOCX. Ukuran maksimal: 5 MB.</small>
                            </div>

                            <!-- Cover Letter -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Surat Pengantar (Cover Letter)</label>
                                <textarea name="cover_letter" rows="4" class="form-control" placeholder="Tuliskan motivasi Anda melamar, ringkasan pengalaman, dan mengapa Anda cocok untuk posisi ini..."></textarea>
                            </div>

                            <!-- Portfolio URL -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Tautan Portofolio / GitHub / LinkedIn (Opsional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-link-45deg"></i></span>
                                    <input type="url" name="portfolio_url" class="form-control" placeholder="https://github.com/username atau https://linkedin.com/in/profil">
                                </div>
                            </div>

                            <!-- Agreement -->
                            <div class="form-check mb-4 p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="agreement" id="agreement" required>
                                <label class="form-check-label small" for="agreement">
                                    <strong>Pernyataan Kebenaran Data:</strong> Saya menyatakan bahwa seluruh informasi, keahlian, dan dokumen yang saya berikan adalah benar dan dapat dipertanggungjawabkan dalam proses rekrutmen ini.
                                </label>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= BASE_URL ?>/job_detail.php?id=<?= $jobId ?>" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-send-check me-1"></i> Kirimkan Lamaran
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
