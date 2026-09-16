<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$pageTitle = 'Pengaturan Sistem - SIREKA Admin';
$activeSidebar = 'settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wa = trim($_POST['helpdesk_whatsapp'] ?? '6281234567890');
    $email = trim($_POST['helpdesk_email'] ?? 'career@solusiteknologi.co.id');
    $platform = trim($_POST['platform_name'] ?? 'SIREKA - IT Career & Recruitment Portal');
    $signer = trim($_POST['loa_authorized_signer'] ?? 'Dr. Hendra Gunawan, S.Kom., M.M. (VP Human Capital & People Ops)');

    $settingsToSave = [
        'helpdesk_whatsapp' => $wa,
        'helpdesk_email' => $email,
        'platform_name' => $platform,
        'loa_authorized_signer' => $signer
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
                <button class="btn btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0">Pengaturan Sistem SIREKA</h5>
                    <small class="text-muted">Konfigurasi hotline bantuan, nama platform, dan parameter LoA resmi</small>
                </div>
            </div>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <div class="row justify-content-center">
                    <div class="card-custom p-3 bg-light border mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <div class="fw-bold text-dark"><i class="bi bi-building text-primary me-2"></i>Identitas Perusahaan IT</div>
                            <small class="text-muted">Untuk mengubah nama perusahaan, alamat kantor pusat, dan nomor kontak resmi, buka menu Profil Perusahaan.</small>
                        </div>
                        <a href="<?= BASE_URL ?>/admin/companies.php" class="btn btn-sm btn-outline-primary text-nowrap">
                            <i class="bi bi-pencil-square me-1"></i> Profil Perusahaan
                        </a>
                    </div>

                    <div class="card-custom p-4 p-md-5 border-0 shadow-sm">
                        <h5 class="fw-bold mb-4 pb-2 border-bottom">
                            <i class="bi bi-gear text-primary me-2"></i> Konfigurasi Parameter SIREKA
                        </h5>

                        <form method="POST" action="<?= BASE_URL ?>/admin/settings.php" class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Nama Sistem / Platform</label>
                                <input type="text" name="platform_name" class="form-control" value="<?= htmlspecialchars($platform) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor WhatsApp Hotline Helpdesk</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-whatsapp text-success"></i></span>
                                    <input type="text" name="helpdesk_whatsapp" class="form-control" value="<?= htmlspecialchars($wa) ?>" placeholder="Contoh: 6281234567890" required>
                                </div>
                                <small class="text-muted">Gunakan format internasional tanpa tanda plus (cth: 6281234567890).</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Alamat Email Dukungan Resmi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-envelope text-primary"></i></span>
                                    <input type="email" name="helpdesk_email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Pejabat Pengesah Letter of Acceptance (LoA)</label>
                                <input type="text" name="loa_authorized_signer" class="form-control" value="<?= htmlspecialchars($signer) ?>" required placeholder="Nama lengkap beserta gelar & jabatan">
                                <small class="text-muted">Nama ini akan tercetak secara otomatis pada bagian tanda tangan dokumen LoA digital.</small>
                            </div>

                            <div class="col-12 pt-3 border-top text-end">
                                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
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
