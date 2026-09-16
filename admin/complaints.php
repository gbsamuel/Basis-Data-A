<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$pageTitle = 'Manajemen Keluhan Pelamar - SIREKA Admin';
$activeSidebar = 'complaints';

// Handle response submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_respond_complaint'])) {
    $idComplaint = (int)($_POST['id_complaint'] ?? 0);
    $response = trim($_POST['admin_response'] ?? '');
    $status = trim($_POST['status'] ?? 'Resolved');

    if ($idComplaint > 0 && !empty($response)) {
        $stmt = $pdo->prepare("
            UPDATE complaint 
            SET admin_response = ?, status = ?, resolved_at = NOW() 
            WHERE id_complaint = ?
        ");
        $stmt->execute([$response, $status, $idComplaint]);
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
                    <h5 class="fw-bold mb-0">Manajemen Keluhan & Tiket Pelamar</h5>
                    <small class="text-muted">Respon tiket kendala seleksi dan pertanyaan teknis dari pelamar</small>
                </div>
            </div>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

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
