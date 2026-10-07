<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/user/dashboard.php');
    exit;
}

$errors = [];
$formData = [
    'nik' => '',
    'nama' => '',
    'email' => '',
    'no_telepon' => '',
    'tanggal_lahir' => '',
    'jenjang_pendidikan' => '',
    'jurusan' => '',
    'institusi' => '',
    'status_pendidikan' => 'Lulus',
    'tahun_lulus' => date('Y'),
    'alamat' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['nik'] = trim($_POST['nik'] ?? '');
    $formData['nama'] = trim($_POST['nama'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['no_telepon'] = trim($_POST['no_telepon'] ?? '');
    $formData['tanggal_lahir'] = trim($_POST['tanggal_lahir'] ?? '');
    $formData['jenjang_pendidikan'] = trim($_POST['jenjang_pendidikan'] ?? '');
    $formData['jurusan'] = trim($_POST['jurusan'] ?? '');
    $formData['institusi'] = trim($_POST['institusi'] ?? '');
    $formData['status_pendidikan'] = trim($_POST['status_pendidikan'] ?? 'Lulus');
    $formData['tahun_lulus'] = (int)($_POST['tahun_lulus'] ?? 0);
    $formData['alamat'] = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($formData['nik']) || strlen($formData['nik']) !== 16 || !ctype_digit($formData['nik'])) {
        $errors[] = 'NIK harus berupa 16 digit angka sesuai KTP.';
    }

    if (empty($formData['nama'])) {
        $errors[] = 'Nama lengkap wajib diisi.';
    }

    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format alamat email tidak valid.';
    }

    if (empty($formData['no_telepon'])) {
        $errors[] = 'Nomor telepon/WhatsApp wajib diisi.';
    }

    if (empty($formData['tanggal_lahir'])) {
        $errors[] = 'Tanggal lahir wajib diisi.';
    }

    if (!in_array($formData['jenjang_pendidikan'], jenjangOptions())) {
        $errors[] = 'Jenjang pendidikan wajib dipilih.';
    }

    if (empty($formData['jurusan']) || empty($formData['institusi'])) {
        $errors[] = 'Jurusan dan nama sekolah/kampus wajib diisi.';
    }

    if (!in_array($formData['status_pendidikan'], ['Lulus', 'Masih Sekolah/Kuliah'])) {
        $errors[] = 'Status pendidikan tidak valid.';
    }

    if (empty($formData['alamat'])) {
        $errors[] = 'Alamat domisili wajib diisi.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Kata sandi minimal 6 karakter.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi kata sandi tidak cocok.';
    }

    // Check unique NIK & Email
    if (empty($errors)) {
        $stmtCheck = $pdo->prepare("SELECT nik FROM user_all WHERE nik = ?");
        $stmtCheck->execute([$formData['nik']]);
        if ($stmtCheck->fetch()) {
            $errors[] = 'NIK tersebut sudah terdaftar di sistem SIREKA.';
        }

        $stmtCheckEmail = $pdo->prepare("SELECT email FROM user_all WHERE email = ?");
        $stmtCheckEmail->execute([$formData['email']]);
        if ($stmtCheckEmail->fetch()) {
            $errors[] = 'Alamat email tersebut sudah terdaftar di sistem SIREKA.';
        }
    }

    // Simpan akun: 1 baris di user_all (data akun) + 1 baris di pelamar (data khusus pelamar).
    // Dipakai transaksi supaya keduanya tersimpan bersama, atau tidak sama sekali.
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        try {
            $pdo->beginTransaction();

            $stmtUser = $pdo->prepare("
                INSERT INTO user_all (nik, nama, email, password, no_telepon, role)
                VALUES (?, ?, ?, ?, ?, 'user')
            ");
            $stmtUser->execute([
                $formData['nik'], $formData['nama'], $formData['email'], $hashedPassword, $formData['no_telepon']
            ]);

            $stmtPelamar = $pdo->prepare("
                INSERT INTO pelamar (nik, tanggal_lahir, jenjang_pendidikan, jurusan, institusi, status_pendidikan, tahun_lulus, alamat)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtPelamar->execute([
                $formData['nik'], $formData['tanggal_lahir'], $formData['jenjang_pendidikan'], $formData['jurusan'],
                $formData['institusi'], $formData['status_pendidikan'], $formData['tahun_lulus'], $formData['alamat']
            ]);

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $success = false;
        }

        if ($success) {
            setFlash('success', 'Pendaftaran akun berhasil! Silakan login dan lengkapi keahlian Anda.');
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan data akun. Silakan coba lagi.';
        }
    }
}

$pageTitle = 'Pendaftaran Akun Pelamar - SIREKA';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-custom p-4 p-md-5 border-0 shadow-sm">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2 px-3 mb-2 shadow-sm">
                        <i class="bi bi-person-plus-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Daftar Akun Pelamar</h3>
                    <p class="text-muted small">Bergabunglah dengan SIREKA untuk memulai perjalanan karier Anda</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon perbaiki kesalahan berikut:</div>
                        <ul class="mb-0 small ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/auth/register.php" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">NIK KTP (16 Digit) <span class="text-danger">*</span></label>
                        <input type="text" name="nik" maxlength="16" required class="form-control" placeholder="3201xxxxxxxxxxxx" value="<?= htmlspecialchars($formData['nik']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" required class="form-control" placeholder="Nama sesuai KTP" value="<?= htmlspecialchars($formData['nama']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" required class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($formData['email']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nomor WhatsApp / Telepon <span class="text-danger">*</span></label>
                        <input type="tel" name="no_telepon" required class="form-control" placeholder="0812xxxxxxxx" value="<?= htmlspecialchars($formData['no_telepon']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lahir" required class="form-control" value="<?= htmlspecialchars($formData['tanggal_lahir']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Jenjang Pendidikan <span class="text-danger">*</span></label>
                        <select name="jenjang_pendidikan" required class="form-select">
                            <option value="">Pilih Jenjang</option>
                            <?php foreach (jenjangOptions() as $jenjang): ?>
                                <option value="<?= $jenjang ?>" <?= $formData['jenjang_pendidikan'] === $jenjang ? 'selected' : '' ?>><?= $jenjang ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Status Pendidikan <span class="text-danger">*</span></label>
                        <select name="status_pendidikan" required class="form-select">
                            <option value="Lulus" <?= $formData['status_pendidikan'] === 'Lulus' ? 'selected' : '' ?>>Sudah Lulus</option>
                            <option value="Masih Sekolah/Kuliah" <?= $formData['status_pendidikan'] === 'Masih Sekolah/Kuliah' ? 'selected' : '' ?>>Masih Sekolah/Kuliah (untuk Magang/PKL)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Sekolah / Kampus <span class="text-danger">*</span></label>
                        <input type="text" name="institusi" required class="form-control" placeholder="cth: SMK Negeri 1 Depok / Universitas Airlangga" value="<?= htmlspecialchars($formData['institusi']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Jurusan <span class="text-danger">*</span></label>
                        <input type="text" name="jurusan" required class="form-control" placeholder="cth: Teknik Informatika" value="<?= htmlspecialchars($formData['jurusan']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tahun Lulus / Perkiraan Lulus <span class="text-danger">*</span></label>
                        <input type="number" name="tahun_lulus" min="1970" max="<?= date('Y') + 6 ?>" required class="form-control" value="<?= htmlspecialchars((string)$formData['tahun_lulus']) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Alamat Domisili <span class="text-danger">*</span></label>
                        <textarea name="alamat" rows="2" required class="form-control" placeholder="Alamat lengkap tempat tinggal saat ini"><?= htmlspecialchars($formData['alamat']) ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="password" minlength="6" required class="form-control" placeholder="Minimal 6 karakter">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" minlength="6" required class="form-control" placeholder="Ketik ulang kata sandi">
                    </div>

                    <div class="col-12 pt-2">
                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Selesaikan Pendaftaran
                        </button>
                    </div>

                    <div class="col-12 text-center small text-muted">
                        Sudah memiliki akun SIREKA? 
                        <a href="<?= BASE_URL ?>/auth/login.php" class="fw-bold text-primary">Masuk ke Akun</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
