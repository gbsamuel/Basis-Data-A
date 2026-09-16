<?php
require_once __DIR__ . '/config/database.php';
$pdo = getDB();

$pageTitle = 'Tentang Kami - PT Solusi Teknologi Nusantara';
$activePage = 'about';

// Get company profile
$company = $pdo->query("SELECT * FROM company WHERE id_company = 1 LIMIT 1")->fetch();

// Get internal IT divisions with open job counts
$divisions = $pdo->query("
    SELECT d.*, COUNT(j.id_job) AS total_jobs
    FROM division d
    LEFT JOIN job j ON d.id_division = j.id_division AND j.status = 'Open'
    WHERE d.id_company = 1
    GROUP BY d.id_division
    ORDER BY d.nama_divisi ASC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Hero Section -->
<div class="bg-corporate-blue py-5 text-white position-relative overflow-hidden">
    <div class="container py-4 position-relative" style="z-index: 2;">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill mb-3 shadow-sm">
                    <i class="bi bi-cpu-fill me-1"></i> Dedicated Tech Career Portal
                </span>
                <h1 class="display-5 fw-bold text-white mb-3">
                    <?= htmlspecialchars($company['nama_company'] ?? 'PT Solusi Teknologi Nusantara') ?>
                </h1>
                <p class="lead text-light opacity-90 mb-4" style="max-width: 680px;">
                    Pelopor inovasi rekayasa perangkat lunak enterprise, cloud-native architecture, dan kecerdasan buatan (Artificial Intelligence) untuk mempercepat transformasi digital Indonesia.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>/jobs.php" class="btn btn-light text-primary fw-bold px-4 py-2 shadow-sm">
                        <i class="bi bi-briefcase-fill me-1"></i> Jelajahi Lowongan IT
                    </a>
                    <a href="<?= BASE_URL ?>/helpdesk.php" class="btn btn-outline-light px-4 py-2">
                        <i class="bi bi-chat-dots-fill me-1"></i> Kontak Tim Rekrutmen
                    </a>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block text-end">
                <div class="p-4 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-25 shadow-sm text-start text-white">
                    <h6 class="fw-bold mb-3 text-warning"><i class="bi bi-geo-alt-fill me-2"></i>Kantor Pusat</h6>
                    <p class="small text-light mb-2"><?= htmlspecialchars($company['alamat'] ?? 'Cyber 2 Tower Lt. 18, Jakarta Selatan') ?></p>
                    <hr class="border-white border-opacity-25 my-2">
                    <div class="small text-light mb-1"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($company['email_corporate'] ?? 'career@solusiteknologi.co.id') ?></div>
                    <div class="small text-light"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($company['no_telepon'] ?? '021-52901122') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Values & Tech Culture -->
<div class="container py-5">
    <div class="text-center mb-5">
        <span class="text-primary fw-bold text-uppercase small tracking-wide">Our Engineering Values</span>
        <h2 class="fw-bold text-dark mt-1">Mengapa Membangun Karier Bersama Kami?</h2>
        <p class="text-muted" style="max-width: 600px; margin: 0 auto;">
            Kami percaya bahwa produk teknologi kelas dunia lahir dari budaya kerja kolaboratif, kebebasan bereksperimen, dan apresiasi nyata terhadap talenta engineer.
        </p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card-custom h-100 p-4 border-0 shadow-sm text-center">
                <div class="bg-primary-light text-primary rounded-circle mx-auto p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-laptop fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Modern Tech Stack</h5>
                <p class="text-muted small mb-0">
                    Bekerja dengan teknologi mutakhir: Cloud Kubernetes, microservices, Python AI, React, Docker, dan arsitektur data terdistribusi.
                </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-custom h-100 p-4 border-0 shadow-sm text-center">
                <div class="bg-success-subtle text-success rounded-circle mx-auto p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-house-gear fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Fleksibilitas Hybrid</h5>
                <p class="text-muted small mb-0">
                    Mendukung keseimbangan kerja dan kehidupan melalui opsi kerja hybrid dan remote friendly dengan jam kerja berbasis hasil.
                </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-custom h-100 p-4 border-0 shadow-sm text-center">
                <div class="bg-warning-subtle text-warning rounded-circle mx-auto p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-mortarboard fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Growth & Sertifikasi</h5>
                <p class="text-muted small mb-0">
                    Alokasi anggaran khusus untuk sertifikasi profesional internasional (AWS, Google Cloud, CKAD) dan akses materi pembelajaran tak terbatas.
                </p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card-custom h-100 p-4 border-0 shadow-sm text-center">
                <div class="bg-info-subtle text-info rounded-circle mx-auto p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-shield-check fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Seleksi Adil & Terbuka</h5>
                <p class="text-muted small mb-0">
                    Didukung fitur Smart Match Score dan Relational Timeline Tracking di portal SIREKA agar setiap tahapan seleksi Anda terpantau transparan.
                </p>
            </div>
        </div>
    </div>

    <!-- Divisi IT Kami -->
    <div class="bg-light rounded-4 p-4 p-md-5 border mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Divisi & Departemen Teknologi</h3>
                <p class="text-muted mb-0">Pilih divisi yang sesuai dengan minat dan spesialisasi keahlian teknis Anda.</p>
            </div>
            <a href="<?= BASE_URL ?>/jobs.php" class="btn btn-outline-primary fw-semibold">
                Lihat Semua Lowongan <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-3">
            <?php foreach ($divisions as $div): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card-custom h-100 p-3 bg-white d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($div['nama_divisi']) ?></h6>
                            <span class="badge bg-primary-light text-primary"><?= $div['total_jobs'] ?> Posisi</span>
                        </div>
                        <p class="text-muted small flex-grow-1 mb-3">
                            <?= htmlspecialchars($div['deskripsi'] ?? 'Divisi rekayasa dan operasional teknologi internal.') ?>
                        </p>
                        <a href="<?= BASE_URL ?>/jobs.php?division=<?= $div['id_division'] ?>" class="btn btn-sm btn-light text-primary fw-semibold w-100">
                            Lihat Posisi di Divisi Ini
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Office & Contact Card -->
    <div class="card-custom p-4 p-md-5 border-0 shadow-sm">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="badge bg-primary text-white mb-2">Headquarters & Tech Hub</span>
                <h3 class="fw-bold text-dark mb-3"><?= htmlspecialchars($company['nama_company']) ?></h3>
                <p class="text-muted mb-4">
                    <?= htmlspecialchars($company['deskripsi']) ?>
                </p>
                <div class="vstack gap-2 text-muted small">
                    <div><i class="bi bi-geo-alt-fill text-danger me-2"></i><?= htmlspecialchars($company['alamat']) ?></div>
                    <div><i class="bi bi-envelope-fill text-primary me-2"></i><?= htmlspecialchars($company['email_corporate']) ?></div>
                    <div><i class="bi bi-telephone-fill text-success me-2"></i><?= htmlspecialchars($company['no_telepon']) ?></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-primary-light rounded-4 p-4 text-center border border-primary-subtle">
                    <i class="bi bi-people-fill text-primary display-4 mb-2"></i>
                    <h5 class="fw-bold text-dark mb-2">Punya Pertanyaan Mengenai Rekrutmen?</h5>
                    <p class="text-muted small mb-3">
                        Tim Human Capital & Tech Recruiter kami siap membantu Anda melalui layanan tiket bantuan atau WhatsApp Helpdesk resmi.
                    </p>
                    <a href="<?= BASE_URL ?>/helpdesk.php" class="btn btn-success fw-bold px-4 py-2">
                        <i class="bi bi-whatsapp me-1"></i> Hubungi WhatsApp Helpdesk
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
