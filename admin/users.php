<?php
require_once __DIR__ . '/../config/database.php';
requireRole('admin'); // khusus admin (data perusahaan dan sistem)

$pdo = getDB();
$me = currentUser();
$pageTitle = 'Kelola Akun - SIREKA Admin';
$activeSidebar = 'users';

// Handle tambah akun HR baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_hr'])) {
    $nik        = trim($_POST['nik'] ?? '');
    $nama       = trim($_POST['nama'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $noTelepon  = trim($_POST['no_telepon'] ?? '');
    $tglLahir   = trim($_POST['tanggal_lahir'] ?? '');
    $pendidikan = trim($_POST['pendidikan_terakhir'] ?? '');
    $tahunLulus = (int)($_POST['tahun_lulus'] ?? 0);
    $alamat     = trim($_POST['alamat'] ?? '');
    $jabatan    = trim($_POST['jabatan'] ?? 'HR Recruiter');
    $password   = $_POST['password'] ?? '';

    // Cek NIK atau email sudah dipakai atau belum
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM user_all WHERE nik = ? OR email = ?");
    $stmtCheck->execute([$nik, $email]);

    if (empty($nik) || empty($nama) || empty($email) || empty($noTelepon) || empty($tglLahir) || empty($pendidikan) || $tahunLulus <= 0) {
        setFlash('danger', 'Semua kolom bertanda * wajib diisi.');
    } elseif (strlen($password) < 6) {
        setFlash('danger', 'Password minimal 6 karakter.');
    } elseif ($stmtCheck->fetchColumn() > 0) {
        setFlash('danger', 'NIK atau email sudah terdaftar.');
    } else {
        // 1. Simpan akun ke user_all dengan role 'hr'
        $stmt = $pdo->prepare("
            INSERT INTO user_all (nik, nama, email, no_telepon, tanggal_lahir, pendidikan_terakhir, tahun_lulus, alamat, password, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'hr')
        ");
        $stmt->execute([$nik, $nama, $email, $noTelepon, $tglLahir, $pendidikan, $tahunLulus, $alamat, password_hash($password, PASSWORD_DEFAULT)]);

        // 2. Simpan jabatannya ke company_admin (data staf perusahaan)
        $stmtStaff = $pdo->prepare("INSERT INTO company_admin (id_company, nik, position) VALUES (1, ?, ?)");
        $stmtStaff->execute([$nik, $jabatan]);

        setFlash('success', 'Akun HR untuk ' . $nama . ' berhasil dibuat.');
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

// Handle hapus akun (pelamar atau HR). Akun admin tidak bisa dihapus dari sini.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete'])) {
    $delNik = trim($_POST['nik'] ?? '');

    $stmt = $pdo->prepare("DELETE FROM user_all WHERE nik = ? AND role IN ('user', 'hr') AND nik <> ?");
    $stmt->execute([$delNik, $me['nik']]);

    if ($stmt->rowCount() > 0) {
        setFlash('info', 'Akun berhasil dihapus beserta data yang terhubung.');
    } else {
        setFlash('danger', 'Akun tidak dapat dihapus.');
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

// Filter berdasarkan role dan kata kunci
$roleFilter = $_GET['role'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT u.nik, u.nama, u.email, u.no_telepon, u.role, u.created_at, ca.position
    FROM user_all u
    LEFT JOIN company_admin ca ON ca.nik = u.nik
    WHERE 1=1
";
$params = [];
if (in_array($roleFilter, ['user', 'hr', 'admin'])) {
    $sql .= " AND u.role = ?";
    $params[] = $roleFilter;
}
if ($search !== '') {
    $sql .= " AND (u.nama LIKE ? OR u.email LIKE ? OR u.nik LIKE ?)";
    $term = "%{$search}%";
    array_push($params, $term, $term, $term);
}
$sql .= " ORDER BY FIELD(u.role, 'admin', 'hr', 'user'), u.nama ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$accounts = $stmt->fetchAll();

// Jumlah akun per role untuk kartu ringkasan
$counts = ['user' => 0, 'hr' => 0, 'admin' => 0];
foreach ($pdo->query("SELECT role, COUNT(*) AS jumlah FROM user_all GROUP BY role") as $row) {
    $counts[$row['role']] = $row['jumlah'];
}

$roleLabels = ['user' => 'Pelamar', 'hr' => 'HR', 'admin' => 'Admin'];
$roleBadges = ['user' => 'bg-secondary', 'hr' => 'bg-primary', 'admin' => 'bg-dark'];

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
                    <h5 class="fw-bold mb-0">Kelola Akun</h5>
                    <small class="text-muted">Kelola akun pelamar dan HR, serta buat akun HR baru</small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addHrModal">
                <i class="bi bi-person-plus me-1"></i> Tambah Akun HR
            </button>
        <?php require __DIR__ . '/../includes/topbar_user.php'; ?>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Ringkasan jumlah akun -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm">
                        <small class="text-muted">Pelamar</small>
                        <h3 class="fw-bold mb-0"><?= (int)$counts['user'] ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm">
                        <small class="text-muted">HR</small>
                        <h3 class="fw-bold mb-0 text-primary"><?= (int)$counts['hr'] ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom p-3 border-0 shadow-sm">
                        <small class="text-muted">Admin</small>
                        <h3 class="fw-bold mb-0"><?= (int)$counts['admin'] ?></h3>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card-custom p-3 mb-4 border-0 shadow-sm">
                <form method="GET" action="<?= BASE_URL ?>/admin/users.php" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama, email, atau NIK..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select form-select-sm">
                            <option value="">Semua Role</option>
                            <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>Pelamar</option>
                            <option value="hr" <?= $roleFilter === 'hr' ? 'selected' : '' ?>>HR</option>
                            <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary px-3">Cari</button>
                        <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Tabel akun -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. Telepon</th>
                                <th>Role</th>
                                <th>Jabatan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($accounts)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Tidak ada akun yang sesuai filter.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($accounts as $acc): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($acc['nama']) ?></div>
                                        <small class="text-muted">NIK: <?= htmlspecialchars($acc['nik']) ?></small>
                                    </td>
                                    <td class="small"><?= htmlspecialchars($acc['email']) ?></td>
                                    <td class="small"><?= htmlspecialchars($acc['no_telepon']) ?></td>
                                    <td>
                                        <span class="badge <?= $roleBadges[$acc['role']] ?>"><?= $roleLabels[$acc['role']] ?></span>
                                    </td>
                                    <td class="small text-muted"><?= htmlspecialchars($acc['position'] ?? '-') ?></td>
                                    <td class="text-end">
                                        <!-- Akun admin dan akun sendiri tidak bisa dihapus -->
                                        <?php if ($acc['role'] !== 'admin' && $acc['nik'] !== $me['nik']): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/users.php" class="d-inline"
                                                  onsubmit="return confirm('Hapus akun <?= htmlspecialchars($acc['nama'], ENT_QUOTES) ?>? Semua lamaran dan data yang terhubung juga akan terhapus.');">
                                                <input type="hidden" name="action_delete" value="1">
                                                <input type="hidden" name="nik" value="<?= htmlspecialchars($acc['nik']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Tambah Akun HR -->
<div class="modal fade" id="addHrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/users.php">
                <input type="hidden" name="action_add_hr" value="1">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-1"></i> Tambah Akun HR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">NIK <span class="text-danger">*</span></label>
                        <input type="text" name="nik" class="form-control" required maxlength="20">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">No. Telepon <span class="text-danger">*</span></label>
                        <input type="tel" name="no_telepon" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" value="HR Recruiter">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lahir" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Pendidikan Terakhir <span class="text-danger">*</span></label>
                        <input type="text" name="pendidikan_terakhir" class="form-control" required placeholder="cth: S1 Psikologi">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tahun Lulus <span class="text-danger">*</span></label>
                        <input type="number" name="tahun_lulus" class="form-control" required min="1970" max="<?= date('Y') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Akun HR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
