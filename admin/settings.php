<?php
require_once __DIR__ . '/../config/database.php';
requireRole('admin'); // khusus admin (data perusahaan dan sistem)

$pdo = getDB();
$pageTitle = 'Pengaturan Sistem - SIREKA Admin';
$activeSidebar = 'settings';

// Halaman ini menggabungkan 2 bagian:
// 1. Profil Perusahaan (tabel company)
// 2. Pengaturan Sistem (tabel system_settings)

// Handle simpan profil perusahaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_profile'])) {
    $nama = trim($_POST['nama_company'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $industri = trim($_POST['industri'] ?? '');
    $emailCompany = trim($_POST['email_corporate'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!empty($nama) && !empty($industri) && !empty($emailCompany)) {
        $exists = $pdo->query("SELECT COUNT(*) FROM company WHERE id_company = 1")->fetchColumn();
        if ($exists) {
            $stmt = $pdo->prepare("
                UPDATE company
                SET nama_company = ?, alamat = ?, industri = ?, email_corporate = ?, no_telepon = ?, deskripsi = ?
                WHERE id_company = 1
            ");
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO company (id_company, nama_company, alamat, industri, email_corporate, no_telepon, deskripsi)
                VALUES (1, ?, ?, ?, ?, ?, ?)
            ");
        }
        $stmt->execute([$nama, $alamat, $industri, $emailCompany, $telepon, $deskripsi]);
        setFlash('success', 'Profil perusahaan berhasil diperbarui.');
    } else {
        setFlash('danger', 'Nama perusahaan, industri, dan email resmi wajib diisi.');
    }
    header('Location: ' . BASE_URL . '/admin/settings.php');
    exit;
}

// Handle simpan pengaturan sistem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_settings'])) {
    $settingsToSave = [
        'helpdesk_whatsapp' => trim($_POST['helpdesk_whatsapp'] ?? '6281234567890'),
        'helpdesk_email' => trim($_POST['helpdesk_email'] ?? 'career@solusiteknologi.co.id'),
        'platform_name' => trim($_POST['platform_name'] ?? 'SIREKA - IT Career & Recruitment Portal'),
        'loa_authorized_signer' => trim($_POST['loa_authorized_signer'] ?? '')
    ];

    $stmt = $pdo->prepare("
        INSERT INTO system_settings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    foreach ($settingsToSave as $k => $v) {
        $stmt->execute([$k, $v]);
    }

    setFlash('success', 'Pengaturan sistem berhasil diperbarui.');
    header('Location: ' . BASE_URL . '/admin/settings.php');
    exit;
}

// Data profil perusahaan
$company = $pdo->query("SELECT * FROM company WHERE id_company = 1 LIMIT 1")->fetch();

// Ringkasan angka
$totalDivisi = $pdo->query("SELECT COUNT(*) FROM division WHERE id_company = 1")->fetchColumn();
$totalJobs = $pdo->query("SELECT COUNT(*) FROM job WHERE id_division IN (SELECT id_division FROM division WHERE id_company = 1)")->fetchColumn();
$totalAkun = $pdo->query("SELECT COUNT(*) FROM user_all")->fetchColumn();

// Data pengaturan sistem
$wa = getSystemSetting($pdo, 'helpdesk_whatsapp', '6281234567890');
$email = getSystemSetting($pdo, 'helpdesk_email', 'career@solusiteknologi.co.id');
$platform = getSystemSetting($pdo, 'platform_name', 'SIREKA - IT Career & Recruitment Portal');
$signer = getSystemSetting($pdo, 'loa_authorized_signer', 'Dr. Hendra Gunawan, S.Kom., M.M. (VP Human Capital & People Ops)');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/sidebar_admin.php'; ?>

    <main class="main-content">
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary" id="sidebarToggle" title="Tampilkan menu">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0">Pengaturan Sistem</h5>
                    <small class="text-muted">Kelola profil perusahaan dan konfigurasi sistem SIREKA</small>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/about.php" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i> Pratinjau Publik
            </a>
        <?php require __DIR__ . '/../includes/topbar_user.php'; ?>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Ringkasan -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm d-flex align-items-center gap-3">
                        <div class="bg-primary-light text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-diagram-3 fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0"><?= number_format($totalDivisi) ?></h4>
                            <small class="text-muted fw-semibold">Divisi IT</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm d-flex align-items-center gap-3">
                        <div class="bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-briefcase fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0"><?= number_format($totalJobs) ?></h4>
                            <small class="text-muted fw-semibold">Total Lowongan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm d-flex align-items-center gap-3">
                        <div class="bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0"><?= number_format($totalAkun) ?></h4>
                            <small class="text-muted fw-semibold">Total Akun</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Bagian 1: Profil Perusahaan -->
                <div class="col-lg-7">
                    <div class="card-custom p-4 border-0 shadow-sm h-100">
                        <h5 class="fw-bold mb-1"><i class="bi bi-building text-primary me-2"></i> Profil Perusahaan</h5>
                        <p class="text-muted small border-bottom pb-3 mb-3">Ditampilkan di halaman Tentang Kami, detail lowongan, dan kop surat LoA.</p>

                        <form method="POST" action="<?= BASE_URL ?>/admin/settings.php" class="row g-3">
                            <input type="hidden" name="action_save_profile" value="1">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Perusahaan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_company" class="form-control" required value="<?= htmlspecialchars($company['nama_company'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Sektor Industri <span class="text-danger">*</span></label>
                                <input type="text" name="industri" class="form-control" required value="<?= htmlspecialchars($company['industri'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Resmi Rekrutmen <span class="text-danger">*</span></label>
                                <input type="email" name="email_corporate" class="form-control" required value="<?= htmlspecialchars($company['email_corporate'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor Telepon Kantor</label>
                                <input type="text" name="no_telepon" class="form-control" value="<?= htmlspecialchars($company['no_telepon'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Alamat Kantor Pusat</label>
                                <textarea name="alamat" rows="2" class="form-control"><?= htmlspecialchars($company['alamat'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Deskripsi Perusahaan</label>
                                <textarea name="deskripsi" rows="4" class="form-control"><?= htmlspecialchars($company['deskripsi'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-check-circle me-1"></i> Simpan Profil Perusahaan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bagian 2: Pengaturan Sistem -->
                <div class="col-lg-5">
                    <div class="card-custom p-4 border-0 shadow-sm h-100">
                        <h5 class="fw-bold mb-1"><i class="bi bi-gear text-primary me-2"></i> Konfigurasi Sistem</h5>
                        <p class="text-muted small border-bottom pb-3 mb-3">Nama platform, kontak bantuan, dan pejabat pengesah LoA.</p>

                        <form method="POST" action="<?= BASE_URL ?>/admin/settings.php" class="row g-3">
                            <input type="hidden" name="action_save_settings" value="1">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Nama Sistem / Platform</label>
                                <input type="text" name="platform_name" class="form-control" value="<?= htmlspecialchars($platform) ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Nomor WhatsApp Helpdesk</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-whatsapp text-success"></i></span>
                                    <input type="text" name="helpdesk_whatsapp" class="form-control" value="<?= htmlspecialchars($wa) ?>" placeholder="Contoh: 6281234567890" required>
                                </div>
                                <small class="text-muted">Format internasional tanpa tanda plus.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Email Dukungan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-envelope text-primary"></i></span>
                                    <input type="email" name="helpdesk_email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Pejabat Pengesah LoA</label>
                                <input type="text" name="loa_authorized_signer" class="form-control" value="<?= htmlspecialchars($signer) ?>" required>
                                <small class="text-muted">Tercetak di bagian tanda tangan dokumen LoA.</small>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-save me-1"></i> Simpan Pengaturan
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
