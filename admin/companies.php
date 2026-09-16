<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$pageTitle = 'Profil Perusahaan - SIREKA Admin';
$activeSidebar = 'companies';

// Ensure company id 1 exists
$company = $pdo->query("SELECT * FROM company WHERE id_company = 1 LIMIT 1")->fetch();

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_profile'])) {
    $nama = trim($_POST['nama_company'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $industri = trim($_POST['industri'] ?? '');
    $email = trim($_POST['email_corporate'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!empty($nama) && !empty($industri) && !empty($email)) {
        if ($company) {
            $stmt = $pdo->prepare("
                UPDATE company 
                SET nama_company = ?, alamat = ?, industri = ?, email_corporate = ?, no_telepon = ?, deskripsi = ?
                WHERE id_company = 1
            ");
            $stmt->execute([$nama, $alamat, $industri, $email, $telepon, $deskripsi]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO company (id_company, nama_company, alamat, industri, email_corporate, no_telepon, deskripsi)
                VALUES (1, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nama, $alamat, $industri, $email, $telepon, $deskripsi]);
        }
        setFlash('success', 'Profil perusahaan IT berhasil diperbarui.');
    } else {
        setFlash('danger', 'Nama perusahaan, industri, dan email resmi wajib diisi.');
    }
    header('Location: ' . BASE_URL . '/admin/companies.php');
    exit;
}

// Refresh company data
$company = $pdo->query("SELECT * FROM company WHERE id_company = 1 LIMIT 1")->fetch();

// Summary stats for company
$totalDivisi = $pdo->query("SELECT COUNT(*) FROM division WHERE id_company = 1")->fetchColumn();
$totalJobs = $pdo->query("SELECT COUNT(*) FROM job WHERE id_division IN (SELECT id_division FROM division WHERE id_company = 1)")->fetchColumn();
$totalPelamar = $pdo->query("
    SELECT COUNT(a.id_application) 
    FROM application a
    JOIN job j ON a.id_job = j.id_job
    JOIN division d ON j.id_division = d.id_division
    WHERE d.id_company = 1
")->fetchColumn();

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
                    <h5 class="fw-bold mb-0">Profil Perusahaan IT</h5>
                    <small class="text-muted">Kelola identitas resmi, alamat kantor, dan profil korporasi perusahaan Anda</small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/about.php" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i> Pratinjau Publik
            </a>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Metrics Card -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card-custom p-3 bg-white border-0 shadow-sm d-flex align-items-center gap-3">
                        <div class="bg-primary-light text-primary rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-diagram-3 fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0"><?= number_format($totalDivisi) ?></h4>
                            <small class="text-muted fw-semibold">Divisi IT Internal</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3 bg-white border-0 shadow-sm d-flex align-items-center gap-3">
                        <div class="bg-success-subtle text-success rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-briefcase fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0"><?= number_format($totalJobs) ?></h4>
                            <small class="text-muted fw-semibold">Total Lowongan Dibuka</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3 bg-white border-0 shadow-sm d-flex align-items-center gap-3">
                        <div class="bg-warning-subtle text-warning rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0"><?= number_format($totalPelamar) ?></h4>
                            <small class="text-muted fw-semibold">Total Berkas Pelamar</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Edit Form -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Informasi Entitas Perusahaan</h5>
                        <p class="text-muted small mb-0">Informasi ini akan ditampilkan pada kop surat Letter of Acceptance (LoA), landing page karir, dan detail lowongan.</p>
                    </div>
                    <span class="badge bg-primary px-3 py-2">Single IT Enterprise</span>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/admin/companies.php">
                    <input type="hidden" name="action_save_profile" value="1">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_company" class="form-control" required value="<?= htmlspecialchars($company['nama_company'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sektor Industri <span class="text-danger">*</span></label>
                            <input type="text" name="industri" class="form-control" required value="<?= htmlspecialchars($company['industri'] ?? 'Information Technology & Software Engineering') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Resmi Rekrutmen <span class="text-danger">*</span></label>
                            <input type="email" name="email_corporate" class="form-control" required value="<?= htmlspecialchars($company['email_corporate'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Telepon Kantor</label>
                            <input type="text" name="no_telepon" class="form-control" value="<?= htmlspecialchars($company['no_telepon'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat Kantor Pusat / Tech Hub</label>
                            <textarea name="alamat" rows="2" class="form-control"><?= htmlspecialchars($company['alamat'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Deskripsi & Visi Perusahaan</label>
                            <textarea name="deskripsi" rows="4" class="form-control"><?= htmlspecialchars($company['deskripsi'] ?? '') ?></textarea>
                            <small class="text-muted">Deskripsi ini akan muncul di halaman "Tentang Kami" dan bagian informasi lowongan pekerjaan.</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
