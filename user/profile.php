<?php
require_once __DIR__ . '/../config/database.php';
requireRole('user');

$pdo = getDB();
$user = currentUser();
$nik = $user['nik'];

$pageTitle = 'Profil & Pengelolaan Keahlian - SIREKA';
$activeSidebar = 'profile';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_profile'])) {
    $nama = trim($_POST['nama'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $pendidikan = trim($_POST['pendidikan_terakhir'] ?? '');
    $tahun_lulus = (int)($_POST['tahun_lulus'] ?? 0);
    $alamat = trim($_POST['alamat'] ?? '');

    if (!empty($nama) && !empty($no_telepon) && !empty($pendidikan)) {
        $stmt = $pdo->prepare("
            UPDATE user_all 
            SET nama = ?, no_telepon = ?, tanggal_lahir = ?, pendidikan_terakhir = ?, tahun_lulus = ?, alamat = ?
            WHERE nik = ?
        ");
        $stmt->execute([$nama, $no_telepon, $tanggal_lahir, $pendidikan, $tahun_lulus, $alamat, $nik]);

        // Refresh session
        $stmtUser = $pdo->prepare("SELECT * FROM user_all WHERE nik = ?");
        $stmtUser->execute([$nik]);
        $_SESSION['user'] = $stmtUser->fetch();
        $_SESSION['user_name'] = $nama;

        setFlash('success', 'Profil Anda berhasil diperbarui.');
        header('Location: ' . BASE_URL . '/user/profile.php');
        exit;
    } else {
        setFlash('danger', 'Semua field wajib harus diisi dengan benar.');
    }
}

// Handle Add Skill
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_skill'])) {
    $skillId = (int)($_POST['id_skill'] ?? 0);
    $level = trim($_POST['level'] ?? 'Intermediate');

    if ($skillId > 0) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM user_skill WHERE nik = ? AND id_skill = ?");
        $stmtCheck->execute([$nik, $skillId]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmtInsert = $pdo->prepare("INSERT INTO user_skill (nik, id_skill, level) VALUES (?, ?, ?)");
            $stmtInsert->execute([$nik, $skillId, $level]);
            setFlash('success', 'Keahlian baru berhasil ditambahkan.');
        } else {
            setFlash('warning', 'Keahlian tersebut sudah terdaftar pada profil Anda.');
        }
    }
    header('Location: ' . BASE_URL . '/user/profile.php');
    exit;
}

// Handle Delete Skill
if (isset($_GET['delete_skill'])) {
    $delSkillId = (int)$_GET['delete_skill'];
    $stmtDel = $pdo->prepare("DELETE FROM user_skill WHERE nik = ? AND id_skill = ?");
    $stmtDel->execute([$nik, $delSkillId]);
    setFlash('info', 'Keahlian berhasil dihapus dari profil.');
    header('Location: ' . BASE_URL . '/user/profile.php');
    exit;
}

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_change_password'])) {
    $oldPass = $_POST['old_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $stmtCheck = $pdo->prepare("SELECT password FROM user_all WHERE nik = ?");
    $stmtCheck->execute([$nik]);
    $currentHash = $stmtCheck->fetchColumn();

    if (!password_verify($oldPass, $currentHash)) {
        setFlash('danger', 'Kata sandi saat ini tidak sesuai.');
    } elseif (strlen($newPass) < 6) {
        setFlash('danger', 'Kata sandi baru minimal 6 karakter.');
    } elseif ($newPass !== $confirmPass) {
        setFlash('danger', 'Konfirmasi kata sandi baru tidak cocok.');
    } else {
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $stmtUpdate = $pdo->prepare("UPDATE user_all SET password = ? WHERE nik = ?");
        $stmtUpdate->execute([$newHash, $nik]);
        setFlash('success', 'Kata sandi Anda berhasil diperbarui.');
    }
    header('Location: ' . BASE_URL . '/user/profile.php');
    exit;
}

// Get candidate skills
$stmtSkills = $pdo->prepare("
    SELECT us.*, s.nama_skill, s.category
    FROM user_skill us
    JOIN skill s ON us.id_skill = s.id_skill
    WHERE us.nik = ?
    ORDER BY s.nama_skill ASC
");
$stmtSkills->execute([$nik]);
$mySkills = $stmtSkills->fetchAll();

// Get all available skills for dropdown
$availableSkills = $pdo->query("SELECT * FROM skill ORDER BY nama_skill ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/sidebar_user.php'; ?>

    <main class="main-content">
        <header class="top-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h5 class="fw-bold mb-0">Profil & Manajemen Keahlian</h5>
                    <small class="text-muted">Kelola data pribadi dan katalog keahlian untuk meningkatkan Match Score</small>
                </div>
            </div>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <div class="row g-4">
                <!-- Biodata Form -->
                <div class="col-lg-7">
                    <div class="card-custom p-4 border-0 shadow-sm mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-person-lines-fill text-primary me-2"></i> Biodata Pribadi
                        </h5>
                        <form method="POST" action="<?= BASE_URL ?>/user/profile.php" class="row g-3">
                            <input type="hidden" name="action_update_profile" value="1">

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor Induk Kependudukan (NIK)</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['nik']) ?>" disabled readonly>
                                <small class="text-muted">NIK bersifat permanen dan tidak dapat diubah.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Alamat Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($user['nama']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" name="no_telepon" class="form-control" required value="<?= htmlspecialchars($user['no_telepon']) ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_lahir" class="form-control" required value="<?= htmlspecialchars($user['tanggal_lahir']) ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                <select name="pendidikan_terakhir" class="form-select" required>
                                    <option value="SMA / SMK" <?= ($user['pendidikan_terakhir'] ?? '') === 'SMA / SMK' ? 'selected' : '' ?>>SMA / SMK</option>
                                    <option value="D3" <?= ($user['pendidikan_terakhir'] ?? '') === 'D3' ? 'selected' : '' ?>>D3</option>
                                    <option value="D4 / S1" <?= ($user['pendidikan_terakhir'] ?? '') === 'D4 / S1' ? 'selected' : '' ?>>D4 / S1</option>
                                    <option value="S2" <?= ($user['pendidikan_terakhir'] ?? '') === 'S2' ? 'selected' : '' ?>>S2</option>
                                    <option value="S3" <?= ($user['pendidikan_terakhir'] ?? '') === 'S3' ? 'selected' : '' ?>>S3</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Tahun Lulus <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_lulus" class="form-control" required value="<?= htmlspecialchars((string)$user['tahun_lulus']) ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Alamat Domisili <span class="text-danger">*</span></label>
                                <textarea name="alamat" rows="2" class="form-control" required><?= htmlspecialchars($user['alamat']) ?></textarea>
                            </div>

                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-save me-1"></i> Simpan Perubahan Biodata
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password Card -->
                    <div class="card-custom p-4 border-0 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-key text-primary me-2"></i> Ganti Kata Sandi
                        </h5>
                        <form method="POST" action="<?= BASE_URL ?>/user/profile.php" class="row g-3">
                            <input type="hidden" name="action_change_password" value="1">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Kata Sandi Saat Ini</label>
                                <input type="password" name="old_password" required class="form-control" placeholder="••••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Kata Sandi Baru</label>
                                <input type="password" name="new_password" minlength="6" required class="form-control" placeholder="Min. 6 karakter">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Ulangi Sandi Baru</label>
                                <input type="password" name="confirm_password" minlength="6" required class="form-control" placeholder="Konfirmasi">
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-outline-primary px-3">Update Kata Sandi</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Skills Management Card (Relational USER_SKILL) -->
                <div class="col-lg-5">
                    <div class="card-custom p-4 border-0 shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-stars text-warning me-2"></i> Katalog Keahlian Anda
                            </h5>
                            <span class="badge bg-primary"><?= count($mySkills) ?> Skill</span>
                        </div>

                        <p class="text-muted small">
                            Keahlian ini digunakan oleh sistem SIREKA untuk menghitung <strong>Match Score</strong> secara instan terhadap setiap lowongan.
                        </p>

                        <!-- Add Skill Form -->
                        <form method="POST" action="<?= BASE_URL ?>/user/profile.php" class="p-3 bg-light rounded-3 border mb-3">
                            <input type="hidden" name="action_add_skill" value="1">
                            <div class="fw-semibold small mb-2 text-dark">Tambah Keahlian Baru:</div>
                            <div class="row g-2">
                                <div class="col-7">
                                    <select name="id_skill" class="form-select form-select-sm" required>
                                        <option value="">Pilih Keahlian...</option>
                                        <?php foreach ($availableSkills as $as): ?>
                                            <option value="<?= $as['id_skill'] ?>">
                                                <?= htmlspecialchars($as['nama_skill']) ?> (<?= htmlspecialchars($as['category']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-5">
                                    <select name="level" class="form-select form-select-sm">
                                        <option value="Beginner">Beginner</option>
                                        <option value="Intermediate" selected>Intermediate</option>
                                        <option value="Advanced">Advanced</option>
                                        <option value="Expert">Expert</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                                        <i class="bi bi-plus-circle me-1"></i> Tambahkan Skill
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- List of User Skills -->
                        <div class="d-flex flex-column gap-2">
                            <?php if (empty($mySkills)): ?>
                                <div class="text-center py-4 text-muted small">
                                    <i class="bi bi-exclamation-circle fs-3 d-block mb-1"></i>
                                    Belum ada keahlian yang ditambahkan.<br>Tambahkan keahlian Anda untuk memaksimalkan peluang kerja.
                                </div>
                            <?php else: ?>
                                <?php foreach ($mySkills as $sk): ?>
                                    <div class="p-2.5 bg-white rounded-3 border d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($sk['nama_skill']) ?></span>
                                            <small class="text-muted"><?= htmlspecialchars($sk['category']) ?> &bull; <span class="badge bg-light text-primary border"><?= $sk['level'] ?></span></small>
                                        </div>
                                        <a href="<?= BASE_URL ?>/user/profile.php?delete_skill=<?= $sk['id_skill'] ?>" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Hapus keahlian ini dari profil?')" title="Hapus Skill">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
