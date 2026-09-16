<?php
require_once __DIR__ . '/config/database.php';
$pdo = getDB();

$pageTitle = 'Helpdesk Rekrutmen - SIREKA';
$activePage = 'helpdesk';

$waNumber = getSystemSetting($pdo, 'helpdesk_whatsapp', '6281234567890');
$helpdeskEmail = getSystemSetting($pdo, 'helpdesk_email', 'recruitment.support@sireka.id');

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="bg-corporate-blue py-5 text-white">
    <div class="container py-3">
        <h1 class="fw-bold text-white mb-2">Pusat Bantuan & Helpdesk</h1>
        <p class="text-light opacity-90 mb-0">Tim rekrutmen SIREKA siap membantu kendala teknis dan pertanyaan seputar proses seleksi.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- Direct Contact Card -->
        <div class="col-lg-5">
            <div class="card-custom p-4 text-center h-100 border-0 shadow-sm">
                <div class="bg-success text-white rounded-circle p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-whatsapp fs-1"></i>
                </div>
                <h3 class="fw-bold mb-2">Chat via WhatsApp</h3>
                <p class="text-muted small mb-4">
                    Hubungi tim administrasi dan rekrutmen perusahaan secara cepat melalui layanan pesan WhatsApp resmi kami.
                </p>
                <div class="d-grid mb-4">
                    <a href="https://wa.me/<?= htmlspecialchars($waNumber) ?>?text=Halo%20Tim%20Rekrutmen%20SIREKA,%20saya%20ingin%20bertanya%20seputar%20proses%20seleksi." target="_blank" class="btn btn-success btn-lg fw-bold shadow-sm">
                        <i class="bi bi-whatsapp me-2"></i> Chat via WhatsApp
                    </a>
                </div>

                <div class="p-3 bg-light rounded-3 text-start small border">
                    <div class="mb-2"><i class="bi bi-clock me-2 text-primary"></i> <strong>Jam Operasional:</strong> Senin - Jumat, 08:30 - 17:00 WIB</div>
                    <div class="mb-2"><i class="bi bi-envelope me-2 text-danger"></i> <strong>Email Resmi:</strong> <?= htmlspecialchars($helpdeskEmail) ?></div>
                    <div><i class="bi bi-shield-check me-2 text-success"></i> <strong>Catatan:</strong> Proses rekrutmen tidak dipungut biaya apapun.</div>
                </div>

                <?php if (isLoggedIn() && hasRole('user')): ?>
                    <div class="mt-4 pt-3 border-top">
                        <p class="text-muted small mb-2">Memiliki kendala spesifik pada lamaran Anda?</p>
                        <a href="<?= BASE_URL ?>/user/complaint.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-ticket-perforated me-1"></i> Buat Tiket Pengaduan Resmi
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- FAQ Accordion -->
        <div class="col-lg-7">
            <div class="card-custom p-4 h-100 border-0 shadow-sm">
                <h4 class="fw-bold mb-3"><i class="bi bi-question-circle text-primary me-2"></i> Pertanyaan yang Sering Diajukan (FAQ)</h4>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item mb-2 border rounded">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                Bagaimana cara kerja sistem Match Score?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary small">
                                Sistem menghitung persentase keahlian yang Anda miliki di profil pelamar (tabel <code>user_skill</code>) dengan daftar keahlian yang dipersyaratkan oleh lowongan (tabel <code>job_skill</code>). Formula: (Skill Cocok / Total Skill Diperlukan) × 100%.
                            </div>
                        </div>
                    </div>


                    <div class="accordion-item mb-2 border rounded">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                Berapa lama proses seleksi dan wawancara?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary small">
                                Proses seleksi rata-rata memakan waktu 1 hingga 2 minggu. Anda dapat memantau setiap langkah tahapan secara real-time melalui menu <strong>Tracking Lamaran</strong> di dashboard pelamar.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item mb-2 border rounded">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                Bagaimana cara mencetak Letter of Acceptance (LoA)?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary small">
                                Jika status lamaran Anda telah <strong>Accepted</strong>, sistem akan menerbitkan LoA digital dengan nomor unik dan tanda tangan pimpinan HR. Anda dapat membukanya di menu <strong>Letter of Acceptance</strong> lalu menekan tombol <em>Cetak / Simpan PDF</em>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
