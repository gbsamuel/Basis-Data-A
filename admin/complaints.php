<?php
require_once __DIR__ . '/../config/database.php';
requireRole('admin'); // khusus admin: keluhan dan feedback pelamar digabung di sini

$pdo = getDB();
$pageTitle = 'Keluhan & Feedback - SIREKA Admin';
$activeSidebar = 'complaints';

// Handle response submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_respond_complaint'])) {
    $idComplaint = (int)($_POST['id_complaint'] ?? 0);
    $response = trim($_POST['admin_response'] ?? '');
    $status = trim($_POST['status'] ?? 'Resolved');

    if ($idComplaint > 0 && !empty($response)) {
        $stmt = $pdo->prepare("
            UPDATE complaint 
            SET admin_response = ?, status = ?, resolved_at = NOW(), responded_by = ? 
            WHERE id_complaint = ?
        ");
        $stmt->execute([$response, $status, currentUser()['nik'], $idComplaint]);   // responded_by = admin yang membalas
        setFlash('success', 'Tanggapan resmi berhasil dikirimkan ke pelamar dan status tiket diperbarui.');
    } else {
        setFlash('danger', 'Tanggapan wajib diisi.');
    }
    header('Location: ' . BASE_URL . '/admin/complaints.php');
    exit;
}

$stmt = $pdo->query("
    SELECT c.*, u.nama as nama_kandidat, u.email, u.no_telepon
    FROM complaint c
    JOIN user_all u ON c.nik = u.nik
    ORDER BY c.created_at DESC
");
$complaints = $stmt->fetchAll();

// Data feedback (ulasan bintang) untuk tab kedua
$avgRating = $pdo->query("SELECT AVG(rating) FROM feedback")->fetchColumn();
$totalFeedback = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();
$feedbacks = $pdo->query("
    SELECT f.*, u.nama as nama_kandidat, u.email
    FROM feedback f
    JOIN user_all u ON f.nik = u.nik
    ORDER BY f.created_at DESC
")->fetchAll();

// Tab yang dibuka: 'keluhan' (default) atau 'feedback'
$activeTab = ($_GET['tab'] ?? '') === 'feedback' ? 'feedback' : 'keluhan';

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
                    <h5 class="fw-bold mb-0">Keluhan & Feedback Pelamar</h5>
                    <small class="text-muted">Tanggapi tiket keluhan dan pantau ulasan kepuasan pelamar</small>
                </div>
            </div>
        <?php require __DIR__ . '/../includes/topbar_user.php'; ?>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Tab: pilih Keluhan atau Feedback -->
            <ul class="nav nav-pills mb-4 gap-2">
                <li class="nav-item">
                    <a class="nav-link <?= $activeTab === 'keluhan' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/complaints.php">
                        <i class="bi bi-chat-dots me-1"></i> Keluhan & Tiket (<?= count($complaints) ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activeTab === 'feedback' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/complaints.php?tab=feedback">
                        <i class="bi bi-star me-1"></i> Ulasan & Feedback (<?= (int)$totalFeedback ?>)
                    </a>
                </li>
            </ul>

            <?php if ($activeTab === 'keluhan'): ?>
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Pelamar</th>
                                <th>Subjek & Kategori</th>
                                <th>Deskripsi Keluhan</th>
                                <th>Tgl Masuk</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($complaints)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Tidak ada tiket keluhan yang masuk.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($complaints as $c): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($c['nama_kandidat']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($c['email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border mb-1"><?= htmlspecialchars($c['category']) ?></span>
                                            <div class="fw-semibold small text-dark"><?= htmlspecialchars($c['subject']) ?></div>
                                        </td>
                                        <td class="small text-secondary" style="max-width: 280px;">
                                            <?= htmlspecialchars(mb_strimwidth($c['description'], 0, 100, '...')) ?>
                                            <?php if (!empty($c['admin_response'])): ?>
                                                <div class="text-success small mt-1"><i class="bi bi-check2-all me-1"></i> Telah direspons</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted">
                                            <?= formatTanggalIndo($c['created_at']) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $c['status'] === 'Resolved' ? 'bg-success' : ($c['status'] === 'In Review' ? 'bg-warning text-dark' : 'bg-primary') ?>">
                                                <?= $c['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-primary" onclick='openRespondModal(<?= json_encode($c) ?>)'>
                                                <i class="bi bi-reply me-1"></i> Respon
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php else: ?>
                <!-- Rating Overview Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card-custom p-4 border-0 shadow-sm d-flex align-items-center gap-4">
                            <div class="display-4 fw-extrabold text-warning">
                                <?= number_format((float)($avgRating ?? 5.0), 1) ?>
                            </div>
                            <div>
                                <div class="mb-1">
                                    <?php
                                    $roundAvg = round((float)($avgRating ?? 5));
                                    for ($s = 1; $s <= 5; $s++) {
                                        echo "<i class='bi bi-star-fill " . ($s <= $roundAvg ? "text-warning" : "text-muted opacity-25") . " fs-5'></i> ";
                                    }
                                    ?>
                                </div>
                                <span class="fw-bold text-dark d-block">Rata-Rata Kepuasan Pelamar</span>
                                <small class="text-muted">Berdasarkan <?= (int)$totalFeedback ?> ulasan terverifikasi</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-custom p-4 border-0 shadow-sm d-flex align-items-center gap-3 bg-light">
                            <div class="bg-primary-light text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-chat-quote fs-2"></i>
                            </div>
                            <div>
                                <span class="fw-bold text-dark d-block">Mendukung SDGs 8</span>
                                <small class="text-muted">
                                    Umpan balik transparansi memastikan proses seleksi adil, terbuka, dan menghargai setiap kandidat.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedbacks Grid -->
                <div class="row g-4">
                    <?php if (empty($feedbacks)): ?>
                        <div class="col-12">
                            <div class="card-custom p-5 text-center text-muted">
                                <i class="bi bi-chat-square-dots fs-1 d-block mb-2"></i>
                                Belum ada ulasan yang masuk.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $fb): ?>
                            <div class="col-md-6">
                                <div class="card-custom p-4 h-100 border-0 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($fb['nama_kandidat']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($fb['email']) ?></small>
                                        </div>
                                        <div>
                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                                <i class="bi bi-star-fill <?= $s <= $fb['rating'] ? 'text-warning' : 'text-muted opacity-25' ?> small"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="text-secondary small mb-3 flex-grow-1" style="line-height: 1.6;">
                                        "<?= nl2br(htmlspecialchars($fb['message'])) ?>"
                                    </p>
                                    <div class="pt-2 border-top text-muted" style="font-size: 0.75rem;">
                                        Dikirim pada: <?= formatTanggalIndo($fb['created_at']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal Respond Complaint -->
<div class="modal fade" id="respondModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/complaints.php">
                <input type="hidden" name="action_respond_complaint" value="1">
                <input type="hidden" name="id_complaint" id="form_id_complaint" value="0">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-chat-left-dots text-primary me-2"></i> Respon Tiket Keluhan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12 p-3 bg-light rounded-3 border">
                        <div class="small text-muted mb-1">Pengirim: <strong id="modal_kandidat" class="text-dark"></strong> &bull; Kategori: <span id="modal_category" class="badge bg-secondary"></span></div>
                        <h6 class="fw-bold text-dark mb-1" id="modal_subject"></h6>
                        <p class="text-secondary small mb-0" id="modal_description"></p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Status Tiket Baru <span class="text-danger">*</span></label>
                        <select name="status" id="form_status" class="form-select" required>
                            <option value="In Review">In Review (Sedang Ditelusuri)</option>
                            <option value="Resolved" selected>Resolved (Selesai)</option>
                            <option value="Closed">Closed (Ditutup)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Tanggapan Resmi Tim HR / Admin <span class="text-danger">*</span></label>
                        <textarea name="admin_response" id="form_admin_response" rows="4" class="form-control" placeholder="Tuliskan jawaban atau solusi resmi untuk pelamar..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">Kirim Tanggapan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRespondModal(c) {
    document.getElementById('form_id_complaint').value = c.id_complaint;
    document.getElementById('modal_kandidat').innerText = c.nama_kandidat + ' (' + c.email + ')';
    document.getElementById('modal_category').innerText = c.category;
    document.getElementById('modal_subject').innerText = c.subject;
    document.getElementById('modal_description').innerText = c.description;
    document.getElementById('form_status').value = c.status === 'Submitted' ? 'Resolved' : c.status;
    document.getElementById('form_admin_response').value = c.admin_response || '';

    new bootstrap.Modal(document.getElementById('respondModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
