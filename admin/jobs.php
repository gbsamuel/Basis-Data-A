<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$pageTitle = 'Manajemen Lowongan Pekerjaan - SIREKA Admin';
$activeSidebar = 'jobs';

$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

// Handle Save (Create / Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_job'])) {
    $idJob = (int)($_POST['id_job'] ?? 0);
    $idDivision = (int)($_POST['id_division'] ?? 0);
    $namaJob = trim($_POST['nama_job'] ?? '');
    $jobType = trim($_POST['job_type'] ?? 'Kerja');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $responsibilities = trim($_POST['responsibilities'] ?? '');
    $eduReq = trim($_POST['education_requirement'] ?? 'S1');
    $expReq = trim($_POST['experience_requirement'] ?? '1-2 Tahun');
    $salaryMin = !empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null;
    $salaryMax = !empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null;
    $location = trim($_POST['location'] ?? 'Jakarta');
    $deadline = trim($_POST['deadline'] ?? date('Y-m-d', strtotime('+30 days')));
    $status = trim($_POST['status'] ?? 'Open');
    $selectedSkills = $_POST['skills'] ?? []; // Array of skill IDs

    if ($idDivision > 0 && !empty($namaJob)) {
        if ($idJob > 0) {
            $stmt = $pdo->prepare("
                UPDATE job 
                SET id_division = ?, nama_job = ?, job_type = ?, deskripsi = ?, requirements = ?, responsibilities = ?,
                    education_requirement = ?, experience_requirement = ?, salary_min = ?, salary_max = ?,
                    location = ?, deadline = ?, status = ?
                WHERE id_job = ?
            ");
            $stmt->execute([
                $idDivision, $namaJob, $jobType, $deskripsi, $requirements, $responsibilities,
                $eduReq, $expReq, $salaryMin, $salaryMax, $location, $deadline, $status, $idJob
            ]);

            // Sync skills in junction table: job_skill
            $pdo->prepare("DELETE FROM job_skill WHERE id_job = ?")->execute([$idJob]);
            $stmtSkill = $pdo->prepare("INSERT INTO job_skill (id_job, id_skill, is_required) VALUES (?, ?, 1)");
            foreach ($selectedSkills as $sId) {
                $stmtSkill->execute([$idJob, (int)$sId]);
            }

            setFlash('success', 'Lowongan pekerjaan berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO job (id_division, nama_job, job_type, deskripsi, requirements, responsibilities,
                                 education_requirement, experience_requirement, salary_min, salary_max, location, deadline, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $idDivision, $namaJob, $jobType, $deskripsi, $requirements, $responsibilities,
                $eduReq, $expReq, $salaryMin, $salaryMax, $location, $deadline, $status
            ]);
            $newJobId = $pdo->lastInsertId();

            // Insert skills into junction table
            $stmtSkill = $pdo->prepare("INSERT INTO job_skill (id_job, id_skill, is_required) VALUES (?, ?, 1)");
            foreach ($selectedSkills as $sId) {
                $stmtSkill->execute([$newJobId, (int)$sId]);
            }

            setFlash('success', 'Lowongan pekerjaan baru berhasil diterbitkan.');
        }
        header('Location: ' . BASE_URL . '/admin/jobs.php');
        exit;
    } else {
        setFlash('danger', 'Divisi dan judul pekerjaan wajib diisi.');
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM job WHERE id_job = ?");
    $stmt->execute([$delId]);
    setFlash('info', 'Lowongan pekerjaan berhasil dihapus.');
    header('Location: ' . BASE_URL . '/admin/jobs.php');
    exit;
}

// Handle Toggle Status
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $tId = (int)$_GET['id'];
    $newSt = $_GET['toggle_status'] === 'Open' ? 'Closed' : 'Open';
    $stmt = $pdo->prepare("UPDATE job SET status = ? WHERE id_job = ?");
    $stmt->execute([$newSt, $tId]);
    setFlash('success', "Status lowongan berhasil diubah menjadi {$newSt}.");
    header('Location: ' . BASE_URL . '/admin/jobs.php');
    exit;
}

// Get lists
$divisions = $pdo->query("
    SELECT id_division, nama_divisi 
    FROM division 
    WHERE id_company = 1 
    ORDER BY nama_divisi ASC
")->fetchAll();

$allSkills = $pdo->query("SELECT * FROM skill ORDER BY nama_skill ASC")->fetchAll();

// If editing, fetch job and assigned skill IDs
$jobEdit = null;
$jobSkillIds = [];
if ($editId > 0 && ($action === 'edit' || $action === 'create')) {
    $stmtJ = $pdo->prepare("SELECT * FROM job WHERE id_job = ?");
    $stmtJ->execute([$editId]);
    $jobEdit = $stmtJ->fetch();

    $stmtS = $pdo->prepare("SELECT id_skill FROM job_skill WHERE id_job = ?");
    $stmtS->execute([$editId]);
    $jobSkillIds = $stmtS->fetchAll(PDO::FETCH_COLUMN);
}

// Query all jobs for list view
$stmtJobs = $pdo->query("
    SELECT j.*, d.nama_divisi, c.nama_company,
           (SELECT COUNT(*) FROM application a WHERE a.id_job = j.id_job) as total_applicants,
           (SELECT COUNT(*) FROM job_skill js WHERE js.id_job = j.id_job) as total_skills
    FROM job j
    JOIN division d ON j.id_division = d.id_division
    JOIN company c ON d.id_company = c.id_company
    ORDER BY j.created_at DESC
");
$jobs = $stmtJobs->fetchAll();

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
                    <h5 class="fw-bold mb-0">Manajemen Lowongan Pekerjaan</h5>
                    <small class="text-muted">Kelola posisi, prasyarat, gaji, dan mapping keahlian (Skill Match)</small>
                </div>
            </div>
            <?php if ($action === 'list'): ?>
                <a href="<?= BASE_URL ?>/admin/jobs.php?action=create" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Buka Lowongan Baru
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/admin/jobs.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            <?php endif; ?>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <?php if ($action === 'create' || $action === 'edit'): ?>
                <!-- Form Create / Edit Job -->
                <div class="card-custom p-4 p-md-5 border-0 shadow-sm">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">
                        <i class="bi bi-briefcase text-primary me-2"></i>
                        <?= $editId > 0 ? 'Edit Lowongan Pekerjaan' : 'Buka Lowongan Pekerjaan Baru' ?>
                    </h5>

                    <form method="POST" action="<?= BASE_URL ?>/admin/jobs.php" class="row g-3">
                        <input type="hidden" name="action_save_job" value="1">
                        <input type="hidden" name="id_job" value="<?= $editId ?>">

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Judul Posisi Lowongan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_job" required class="form-control" placeholder="Contoh: Senior Backend Engineer" value="<?= htmlspecialchars($jobEdit['nama_job'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tipe Pekerjaan <span class="text-danger">*</span></label>
                            <select name="job_type" class="form-select" required>
                                <option value="Kerja" <?= ($jobEdit['job_type'] ?? '') === 'Kerja' ? 'selected' : '' ?>>Kerja (Full-Time)</option>
                                <option value="Magang" <?= ($jobEdit['job_type'] ?? '') === 'Magang' ? 'selected' : '' ?>>Magang (Internship)</option>
                                <option value="Management Trainee" <?= ($jobEdit['job_type'] ?? '') === 'Management Trainee' ? 'selected' : '' ?>>Management Trainee (MT)</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Status Lowongan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="Open" <?= ($jobEdit['status'] ?? '') === 'Open' ? 'selected' : '' ?>>Open (Buka)</option>
                                <option value="Closed" <?= ($jobEdit['status'] ?? '') === 'Closed' ? 'selected' : '' ?>>Closed (Tutup)</option>
                                <option value="Draft" <?= ($jobEdit['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Draft (Konsep)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Divisi IT Penempatan <span class="text-danger">*</span></label>
                            <select name="id_division" class="form-select" required>
                                <option value="">Pilih Divisi IT Penempatan...</option>
                                <?php foreach ($divisions as $div): ?>
                                    <option value="<?= $div['id_division'] ?>" <?= ($jobEdit['id_division'] ?? 0) == $div['id_division'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($div['nama_divisi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Lokasi Penempatan <span class="text-danger">*</span></label>
                            <input type="text" name="location" required class="form-control" placeholder="Contoh: Jakarta Selatan" value="<?= htmlspecialchars($jobEdit['location'] ?? 'Jakarta') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Batas Akhir (Deadline) <span class="text-danger">*</span></label>
                            <input type="date" name="deadline" required class="form-control" value="<?= htmlspecialchars($jobEdit['deadline'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Pendidikan Minimal</label>
                            <select name="education_requirement" class="form-select">
                                <option value="SMA / SMK" <?= ($jobEdit['education_requirement'] ?? '') === 'SMA / SMK' ? 'selected' : '' ?>>SMA / SMK</option>
                                <option value="D3" <?= ($jobEdit['education_requirement'] ?? '') === 'D3' ? 'selected' : '' ?>>D3</option>
                                <option value="S1" <?= ($jobEdit['education_requirement'] ?? 'S1') === 'S1' ? 'selected' : '' ?>>S1</option>
                                <option value="S2" <?= ($jobEdit['education_requirement'] ?? '') === 'S2' ? 'selected' : '' ?>>S2</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Pengalaman Minimal</label>
                            <input type="text" name="experience_requirement" class="form-control" placeholder="cth: 1-2 Tahun / Fresh Graduate" value="<?= htmlspecialchars($jobEdit['experience_requirement'] ?? '1-2 Tahun') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Gaji Minimal (Rp)</label>
                            <input type="number" name="salary_min" class="form-control" placeholder="cth: 8000000" value="<?= htmlspecialchars((string)($jobEdit['salary_min'] ?? '')) ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Gaji Maksimal (Rp)</label>
                            <input type="number" name="salary_max" class="form-control" placeholder="cth: 12000000" value="<?= htmlspecialchars((string)($jobEdit['salary_max'] ?? '')) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Deskripsi Pekerjaan <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" rows="3" required class="form-control" placeholder="Jelaskan gambaran umum peran dan posisi ini..."><?= htmlspecialchars($jobEdit['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tanggung Jawab (Responsibilities) <span class="text-danger">*</span></label>
                            <textarea name="responsibilities" rows="4" required class="form-control" placeholder="Poin-poin tanggung jawab tugas harian..."><?= htmlspecialchars($jobEdit['responsibilities'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kualifikasi & Persyaratan (Requirements) <span class="text-danger">*</span></label>
                            <textarea name="requirements" rows="4" required class="form-control" placeholder="Kriteria latar belakang, soft skill, dan hard skill..."><?= htmlspecialchars($jobEdit['requirements'] ?? '') ?></textarea>
                        </div>

                        <!-- Required Skills Checklist for Junction Table JOB_SKILL -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 text-primary">
                                        <i class="bi bi-stars text-warning me-1"></i> Mapping Keahlian Wajib (Junction Table: JOB_SKILL)
                                    </label>
                                    <small class="text-muted">Pilih keahlian yang menjadi acuan kalkulasi Match Score pelamar.</small>
                                </div>
                                <div class="row g-2">
                                    <?php foreach ($allSkills as $s): ?>
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <div class="form-check p-2 bg-white rounded border">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="skills[]" value="<?= $s['id_skill'] ?>" id="sk_<?= $s['id_skill'] ?>" <?= in_array($s['id_skill'], $jobSkillIds) ? 'checked' : '' ?>>
                                                <label class="form-check-label small fw-semibold" for="sk_<?= $s['id_skill'] ?>">
                                                    <?= htmlspecialchars($s['nama_skill']) ?>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;"><?= htmlspecialchars($s['category']) ?></small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/admin/jobs.php" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Simpan & Terbitkan Lowongan
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Table of Jobs -->
                <div class="card-custom p-4 border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Posisi Pekerjaan</th>
                                    <th>Divisi IT</th>
                                    <th>Tipe</th>
                                    <th>Gaji</th>
                                    <th>Keahlian</th>
                                    <th>Pelamar</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jobs as $j): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($j['nama_job']) ?></div>
                                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($j['location']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-light text-primary border border-primary-subtle">
                                                <i class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars($j['nama_divisi']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-light text-primary"><?= htmlspecialchars($j['job_type']) ?></span>
                                        </td>
                                        <td class="small">
                                            <?= formatRupiah($j['salary_min']) ?> - <?= formatRupiah($j['salary_max']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-muted border"><?= $j['total_skills'] ?> Skill</span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/applications.php?job_id=<?= $j['id_job'] ?>" class="badge bg-primary text-white text-decoration-none">
                                                <?= $j['total_applicants'] ?> Pelamar
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/jobs.php?toggle_status=<?= $j['status'] ?>&id=<?= $j['id_job'] ?>" class="badge <?= $j['status'] === 'Open' ? 'bg-success' : 'bg-secondary' ?> text-decoration-none" title="Klik untuk ubah status">
                                                <?= $j['status'] ?>
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/admin/jobs.php?action=edit&id=<?= $j['id_job'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/jobs.php?delete=<?= $j['id_job'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus lowongan pekerjaan ini?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
