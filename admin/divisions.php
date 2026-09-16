<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

$pdo = getDB();
$pageTitle = 'Manajemen Divisi IT - SIREKA Admin';
$activeSidebar = 'divisions';

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save_division'])) {
    $idDivision = (int)($_POST['id_division'] ?? 0);
    $nama = trim($_POST['nama_divisi'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!empty($nama)) {
        if ($idDivision > 0) {
            $stmt = $pdo->prepare("UPDATE division SET id_company = 1, nama_divisi = ?, deskripsi = ? WHERE id_division = ?");
            $stmt->execute([$nama, $deskripsi, $idDivision]);
            setFlash('success', 'Data divisi IT berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO division (id_company, nama_divisi, deskripsi) VALUES (1, ?, ?)");
            $stmt->execute([$nama, $deskripsi]);
            setFlash('success', 'Divisi IT baru berhasil ditambahkan.');
        }
    } else {
        setFlash('danger', 'Nama divisi IT wajib diisi.');
    }
    header('Location: ' . BASE_URL . '/admin/divisions.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM division WHERE id_division = ? AND id_company = 1");
    $stmt->execute([$delId]);
    setFlash('info', 'Divisi IT berhasil dihapus beserta lowongan terkait.');
    header('Location: ' . BASE_URL . '/admin/divisions.php');
    exit;
}

// Query divisions for our IT company
$divisions = $pdo->query("
    SELECT d.*, c.nama_company,
           COUNT(j.id_job) as total_jobs
    FROM division d
    JOIN company c ON d.id_company = c.id_company
    WHERE d.id_company = 1
    GROUP BY d.id_division 
    ORDER BY d.nama_divisi ASC
")->fetchAll();

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
                    <h5 class="fw-bold mb-0">Manajemen Divisi IT</h5>
                    <small class="text-muted">Kelola struktur departemen dan divisi teknologi perusahaan internal</small>
                </div>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#divisionModal" onclick="resetForm()">
                <i class="bi bi-plus-circle me-1"></i> Tambah Divisi IT
            </button>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Divisi IT</th>
                                <th>Deskripsi & Ruang Lingkup</th>
                                <th class="text-center">Lowongan Terkait</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($divisions)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada data divisi IT.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($divisions as $d): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                                <i class="bi bi-diagram-3 text-primary"></i>
                                                <span><?= htmlspecialchars($d['nama_divisi']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted" style="max-width: 450px; display: block;">
                                                <?= htmlspecialchars($d['deskripsi'] ?? 'Divisi teknologi internal.') ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>/admin/jobs.php?division_id=<?= $d['id_division'] ?>" class="badge bg-primary-light text-primary text-decoration-none px-3 py-2">
                                                <i class="bi bi-briefcase me-1"></i> <?= $d['total_jobs'] ?> Lowongan
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick='editDivision(<?= json_encode($d) ?>)' title="Edit Divisi">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <a href="<?= BASE_URL ?>/admin/divisions.php?delete=<?= $d['id_division'] ?>" 
                                                   class="btn btn-outline-danger" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus divisi ini? Seluruh data lowongan terkait akan ikut terhapus.')" 
                                                   title="Hapus Divisi">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
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

<!-- Modal Form Divisi -->
<div class="modal fade" id="divisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= BASE_URL ?>/admin/divisions.php">
                <input type="hidden" name="action_save_division" value="1">
                <input type="hidden" name="id_division" id="form_id_division" value="0">
                
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="modalTitle">Tambah Divisi IT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Divisi IT <span class="text-danger">*</span></label>
                        <input type="text" name="nama_divisi" id="form_nama" class="form-control" placeholder="cth: Cloud & DevOps Engineering" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi / Tugas Pokok</label>
                        <textarea name="deskripsi" id="form_deskripsi" rows="3" class="form-control" placeholder="Jelaskan fokus teknis departemen ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Divisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').innerText = 'Tambah Divisi IT';
    document.getElementById('form_id_division').value = '0';
    document.getElementById('form_nama').value = '';
    document.getElementById('form_deskripsi').value = '';
}

function editDivision(d) {
    document.getElementById('modalTitle').innerText = 'Edit Divisi IT: ' + d.nama_divisi;
    document.getElementById('form_id_division').value = d.id_division;
    document.getElementById('form_nama').value = d.nama_divisi;
    document.getElementById('form_deskripsi').value = d.deskripsi || '';
    
    var modal = new bootstrap.Modal(document.getElementById('divisionModal'));
    modal.show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
