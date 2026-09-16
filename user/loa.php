<?php
require_once __DIR__ . '/../config/database.php';
requireRole('user');

$pdo = getDB();
$user = currentUser();
$nik = $user['nik'];

$appId = isset($_GET['app_id']) ? (int)$_GET['app_id'] : 0;

// Fetch LOA
if ($appId > 0) {
    $stmt = $pdo->prepare("
        SELECT l.*, a.applied_at, a.current_status, 
               j.nama_job, j.job_type, j.location,
               c.nama_company, c.alamat as alamat_company, c.email_corporate, c.no_telepon as telp_company, c.industri,
               u.nama as nama_kandidat, u.nik, u.email as email_kandidat, u.no_telepon as telp_kandidat, u.alamat as alamat_kandidat
        FROM loa l
        JOIN application a ON l.id_application = a.id_application
        JOIN job j ON a.id_job = j.id_job
        JOIN division d ON j.id_division = d.id_division
        JOIN company c ON d.id_company = c.id_company
        JOIN user_all u ON a.nik = u.nik
        WHERE l.id_application = ? AND a.nik = ?
    ");
    $stmt->execute([$appId, $nik]);
} else {
    // Find latest LOA for this user
    $stmt = $pdo->prepare("
        SELECT l.*, a.applied_at, a.current_status, 
               j.nama_job, j.job_type, j.location,
               c.nama_company, c.alamat as alamat_company, c.email_corporate, c.no_telepon as telp_company, c.industri,
               u.nama as nama_kandidat, u.nik, u.email as email_kandidat, u.no_telepon as telp_kandidat, u.alamat as alamat_kandidat
        FROM loa l
        JOIN application a ON l.id_application = a.id_application
        JOIN job j ON a.id_job = j.id_job
        JOIN division d ON j.id_division = d.id_division
        JOIN company c ON d.id_company = c.id_company
        JOIN user_all u ON a.nik = u.nik
        WHERE a.nik = ?
        ORDER BY l.issue_date DESC
        LIMIT 1
    ");
    $stmt->execute([$nik]);
}

$loa = $stmt->fetch();

$pageTitle = 'Letter of Acceptance (LoA) - SIREKA';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Action Controls (No Print) -->
    <div class="no-print d-flex justify-content-between align-items-center mb-4">
        <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
        <div class="d-flex gap-2">
            <?php if ($loa): ?>
                <button onclick="window.print()" class="btn btn-primary fw-bold shadow-sm">
                    <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php renderFlash(); ?>

    <?php if (!$loa): ?>
        <div class="card-custom p-5 text-center border-0 shadow-sm">
            <div class="display-4 text-muted mb-2"><i class="bi bi-file-earmark-x"></i></div>
            <h4 class="fw-bold">Belum Ada Letter of Acceptance yang Diterbitkan</h4>
            <p class="text-muted small">Surat penerimaan kerja resmi hanya diterbitkan untuk pelamar yang telah dinyatakan <strong>Accepted</strong> oleh pihak HR perusahaan.</p>
            <a href="<?= BASE_URL ?>/user/applications.php" class="btn btn-primary btn-sm">
                Lihat Status Lamaran Saya
            </a>
        </div>
    <?php else: ?>
        <!-- Formal LoA Document Container -->
        <div class="loa-container shadow">
            <!-- Company Letterhead (Kop Surat) -->
            <div class="loa-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold text-dark mb-1 text-uppercase"><?= htmlspecialchars($loa['nama_company']) ?></h3>
                    <div class="text-secondary small">
                        <?= htmlspecialchars($loa['alamat_company']) ?><br>
                        Email: <?= htmlspecialchars($loa['email_corporate']) ?> | Telp: <?= htmlspecialchars($loa['telp_company']) ?>
                    </div>
                </div>
                <div class="text-end">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2 px-3 fw-bold">
                        <i class="bi bi-briefcase-fill fs-4 me-2"></i> SIREKA
                    </div>
                </div>
            </div>

            <!-- Title & Ref Number -->
            <div class="text-center my-4">
                <h4 class="fw-bold text-dark mb-1 text-decoration-underline text-uppercase">LETTER OF ACCEPTANCE</h4>
                <div class="text-muted small">Nomor Surat: <strong><?= htmlspecialchars($loa['loa_number']) ?></strong></div>
            </div>

            <!-- Letter Body -->
            <div class="mb-4 small" style="line-height: 1.8;">
                <p>Kepada Yth.,<br>
                <strong><?= htmlspecialchars($loa['nama_kandidat']) ?></strong><br>
                NIK: <?= htmlspecialchars($loa['nik']) ?><br>
                Alamat: <?= htmlspecialchars($loa['alamat_kandidat']) ?></p>

                <p>Dengan hormat,</p>

                <p>
                    Sehubungan dengan seluruh rangkaian tahapan seleksi rekrutmen dan wawancara yang telah Anda ikuti melalui platform <strong>SIREKA (Sistem Informasi Rekrutmen & Kandidat)</strong>, manajemen <strong><?= htmlspecialchars($loa['nama_company']) ?></strong> dengan bangga memberitahukan bahwa Anda dinyatakan:
                </p>

                <div class="text-center my-3 p-3 bg-light border rounded">
                    <span class="fs-5 fw-bold text-success text-uppercase letter-spacing-1">DITERIMA BEKERJA (ACCEPTED)</span>
                </div>

                <p>Adapun rincian penempatan kerja Anda adalah sebagai berikut:</p>

                <table class="table table-sm table-borderless my-2" style="max-width: 600px;">
                    <tr>
                        <td style="width: 200px;"><strong>Posisi Pekerjaan</strong></td>
                        <td>: <?= htmlspecialchars($loa['position']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Divisi Penempatan</strong></td>
                        <td>: <?= htmlspecialchars($loa['division']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tipe Pekerjaan</strong></td>
                        <td>: <?= htmlspecialchars($loa['job_type']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Lokasi Penempatan</strong></td>
                        <td>: <?= htmlspecialchars($loa['location']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Mulai Bekerja</strong></td>
                        <td>: <strong><?= formatTanggalIndo($loa['join_date']) ?></strong></td>
                    </tr>
                </table>

                <p>
                    <?= htmlspecialchars($loa['notes'] ?? 'Harap hadir di kantor operasional pada tanggal mulai bekerja pukul 08:30 WIB dengan membawa kelengkapan dokumen asli ijazah dan identitas diri untuk proses orientasi karyawan baru.') ?>
                </p>

                <p>Demikian surat penerimaan kerja ini diterbitkan secara resmi melalui sistem untuk dapat dipergunakan sebagaimana mestinya.</p>
            </div>

            <!-- Signatures -->
            <div class="loa-signature d-flex justify-content-between align-items-end pt-4">
                <div class="text-center" style="width: 220px;">
                    <div class="small text-muted mb-5">Diterima & Disetujui Oleh:</div>
                    <div class="fw-bold text-dark border-bottom pb-1"><?= htmlspecialchars($loa['nama_kandidat']) ?></div>
                    <div class="small text-muted">Kandidat Karyawan</div>
                </div>

                <div class="text-center" style="width: 280px;">
                    <div class="small text-muted mb-1">Jakarta, <?= formatTanggalIndo($loa['issue_date']) ?></div>
                    <div class="small text-muted mb-4"><?= htmlspecialchars($loa['nama_company']) ?></div>
                    <div class="badge bg-success-subtle text-success border border-success mb-2 px-2 py-1 small">
                        <i class="bi bi-patch-check-fill me-1"></i> VERIFIED DIGITAL LoA
                    </div>
                    <div class="fw-bold text-dark border-bottom pb-1"><?= htmlspecialchars($loa['authorized_by']) ?></div>
                    <div class="small text-muted">Authorized HR Executive</div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
