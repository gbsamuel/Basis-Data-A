<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['hr', 'admin']);

$pdo = getDB();
$nik = currentUser()['nik'];

// Ambil data terbaru (akun + data staf) lalu simpan ulang ke session
$user = loadUserProfile($pdo, $nik);
$_SESSION['user'] = $user;

$pageTitle = 'Profil Saya - SIREKA Admin';
$activeSidebar = 'profile';

// Handle upload / ganti foto profil (sama seperti di profil pelamar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_upload_photo'])) {
    $file = $_FILES['profile_photo'] ?? null;
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 2 * 1024 * 1024; // 2 MB

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        setFlash('danger', 'Pilih file foto terlebih dahulu.');
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            setFlash('danger', 'Format foto harus JPG, PNG, atau WEBP.');
        } elseif ($file['size'] > $maxSize) {
            setFlash('danger', 'Ukuran foto maksimal 2 MB.');
        } elseif (getimagesize($file['tmp_name']) === false) {
            setFlash('danger', 'File yang diunggah bukan gambar.');
        } else {
            $newName = $nik . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/profiles/';

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                // Hapus foto lama agar folder tidak penuh
                if (!empty($user['profile_photo']) && file_exists($uploadDir . $user['profile_photo'])) {
                    unlink($uploadDir . $user['profile_photo']);
                }
                $stmt = $pdo->prepare("UPDATE user_all SET profile_photo = ? WHERE nik = ?");
                $stmt->execute([$newName, $nik]);
                $_SESSION['user']['profile_photo'] = $newName;
                setFlash('success', 'Foto profil berhasil diperbarui.');
            } else {
                setFlash('danger', 'Foto gagal diunggah. Coba lagi.');
            }
        }
    }
    header('Location: ' . BASE_URL . '/admin/profile.php');
    exit;
}

// Handle update data diri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_profile'])) {
    $nama = trim($_POST['nama'] ?? '');
    $noTelepon = trim($_POST['no_telepon'] ?? '');

    if (!empty($nama) && !empty($noTelepon)) {
        $stmt = $pdo->prepare("UPDATE user_all SET nama = ?, no_telepon = ? WHERE nik = ?");
        $stmt->execute([$nama, $noTelepon, $nik]);

        // Perbarui session supaya nama baru langsung tampil di header
        $_SESSION['user'] = loadUserProfile($pdo, $nik);
        $_SESSION['user_name'] = $nama;
        setFlash('success', 'Data diri berhasil diperbarui.');
    } else {
        setFlash('danger', 'Nama dan nomor telepon wajib diisi.');
    }
    header('Location: ' . BASE_URL . '/admin/profile.php');
    exit;
}

// Handle ganti kata sandi
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
        $stmtUpdate = $pdo->prepare("UPDATE user_all SET password = ? WHERE nik = ?");
        $stmtUpdate->execute([password_hash($newPass, PASSWORD_DEFAULT), $nik]);
        setFlash('success', 'Kata sandi berhasil diperbarui.');
    }
    header('Location: ' . BASE_URL . '/admin/profile.php');
    exit;
}

// Jabatan staf diambil dari tabel staff (sudah ikut dimuat oleh loadUserProfile)
$jabatan = $user['jabatan'] ?? '-';
$roleLabel = $user['role'] === 'admin' ? 'Admin' : (isKepalaHr() ? 'Kepala HR' : 'HR');

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
                    <h5 class="fw-bold mb-0">Profil Saya</h5>
                    <small class="text-muted">Kelola foto, data diri, dan kata sandi akun Anda</small>
                </div>
            </div>
        <?php require __DIR__ . '/../includes/topbar_user.php'; ?>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <div class="row g-4">
                <div class="col-lg-7">
                    <!-- Foto Profil -->
                    <div class="card-custom p-4 border-0 shadow-sm mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-camera-fill text-primary me-2"></i> Foto Profil
                        </h5>
                        <form method="POST" action="<?= BASE_URL ?>/admin/profile.php" enctype="multipart/form-data"
                              class="d-flex flex-column flex-sm-row align-items-center gap-4">
                            <input type="hidden" name="action_upload_photo" value="1">

                            <?php if (!empty($user['profile_photo'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/profiles/<?= htmlspecialchars($user['profile_photo']) ?>"
                                     id="photoPreview" class="profile-photo-lg" alt="Foto profil">
                            <?php else: ?>
                                <img src="" id="photoPreview" class="profile-photo-lg d-none" alt="Foto profil">
                                <div id="photoInitial" class="profile-photo-lg profile-photo-initial">
                                    <?= strtoupper(substr($user['nama'] ?? 'A', 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <div class="flex-grow-1 w-100">
                                <!-- Tampilan biasa -->
                                <div id="photoInfo">
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($user['nama']) ?></h6>
                                    <small class="text-muted d-block"><?= htmlspecialchars($user['email']) ?></small>
                                    <span class="badge bg-primary-light text-primary my-2"><?= $roleLabel ?></span><br>
                                    <button type="button" id="photoEditBtn" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-camera me-1"></i>
                                        <?= empty($user['profile_photo']) ? 'Tambah Foto' : 'Ganti Foto' ?>
                                    </button>
                                </div>

                                <!-- Form upload, muncul setelah tombol diklik -->
                                <div id="photoForm" class="d-none">
                                    <label class="form-label small fw-semibold">Pilih foto baru</label>
                                    <input type="file" name="profile_photo" id="photoInput" class="form-control mb-2"
                                           accept=".jpg,.jpeg,.png,.webp" required>
                                    <small class="text-muted d-block mb-2">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-upload me-1"></i> Simpan Foto
                                    </button>
                                    <button type="button" id="photoCancelBtn" class="btn btn-light btn-sm">Batal</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Data Diri -->
                    <div class="card-custom p-4 border-0 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-person-lines-fill text-primary me-2"></i> Data Diri
                        </h5>
                        <form method="POST" action="<?= BASE_URL ?>/admin/profile.php" class="row g-3">
                            <input type="hidden" name="action_update_profile" value="1">

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">NIK</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['nik']) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Role</label>
                                <input type="text" class="form-control" value="<?= $roleLabel ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Jabatan</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($jabatan) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($user['nama']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                                <input type="tel" name="no_telepon" class="form-control" required value="<?= htmlspecialchars($user['no_telepon']) ?>">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ganti Kata Sandi -->
                <div class="col-lg-5">
                    <div class="card-custom p-4 border-0 shadow-sm">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="bi bi-key-fill text-primary me-2"></i> Ganti Kata Sandi
                        </h5>
                        <form method="POST" action="<?= BASE_URL ?>/admin/profile.php" class="vstack gap-3">
                            <input type="hidden" name="action_change_password" value="1">
                            <div>
                                <label class="form-label small fw-semibold">Kata Sandi Saat Ini</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div>
                                <label class="form-label small fw-semibold">Kata Sandi Baru</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>
                            <div>
                                <label class="form-label small fw-semibold">Ulangi Kata Sandi Baru</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-outline-primary">Perbarui Kata Sandi</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Tombol "Ganti Foto": sembunyikan info, tampilkan form upload
    document.getElementById('photoEditBtn').addEventListener('click', function () {
        document.getElementById('photoInfo').classList.add('d-none');
        document.getElementById('photoForm').classList.remove('d-none');
    });

    // Tombol "Batal": muat ulang halaman agar kembali ke tampilan biasa
    document.getElementById('photoCancelBtn').addEventListener('click', function () {
        window.location.reload();
    });

    // Pratinjau foto sebelum disimpan
    document.getElementById('photoInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const preview = document.getElementById('photoPreview');
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');

        const initial = document.getElementById('photoInitial');
        if (initial) initial.classList.add('d-none');
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
