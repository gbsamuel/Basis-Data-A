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
    'pendidikan_terakhir' => '',
    'tahun_lulus' => date('Y'),
    'alamat' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['nik'] = trim($_POST['nik'] ?? '');
    $formData['nama'] = trim($_POST['nama'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['no_telepon'] = trim($_POST['no_telepon'] ?? '');
    $formData['tanggal_lahir'] = trim($_POST['tanggal_lahir'] ?? '');
    $formData['pendidikan_terakhir'] = trim($_POST['pendidikan_terakhir'] ?? '');
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

    if (empty($formData['pendidikan_terakhir'])) {
        $errors[] = 'Pendidikan terakhir wajib dipilih.';
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

    // Insert user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmtInsert = $pdo->prepare("
            INSERT INTO user_all (nik, nama, email, no_telepon, tanggal_lahir, pendidikan_terakhir, tahun_lulus, alamat, password, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'user')
        ");
        $success = $stmtInsert->execute([
            $formData['nik'],
            $formData['nama'],
            $formData['email'],
            $formData['no_telepon'],
            $formData['tanggal_lahir'],
            $formData['pendidikan_terakhir'],
            $formData['tahun_lulus'],
            $formData['alamat'],
            $hashedPassword
        ]);

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
                        <label class="form-label small fw-semibold">Pendidikan Terakhir <span class="text-danger">*</span></label>
                        <select name="pendidikan_terakhir" required class="form-select">
                            <option value="">Pilih Jenjang</option>
                            <option value="SMA / SMK" <?= $formData['pendidikan_terakhir'] === 'SMA / SMK' ? 'selected' : '' ?>>SMA / SMK</option>
                            <option value="D3" <?= $formData['pendidikan_terakhir'] === 'D3' ? 'selected' : '' ?>>D3</option>
                            <option value="D4 / S1" <?= $formData['pendidikan_terakhir'] === 'D4 / S1' ? 'selected' : '' ?>>D4 / S1</option>
                            <option value="S2" <?= $formData['pendidikan_terakhir'] === 'S2' ? 'selected' : '' ?>>S2</option>
                            <option value="S3" <?= $formData['pendidikan_terakhir'] === 'S3' ? 'selected' : '' ?>>S3</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tahun Lulus <span class="text-danger">*</span></label>
                        <input type="number" name="tahun_lulus" min="1970" max="<?= date('Y') + 1 ?>" required class="form-control" value="<?= htmlspecialchars((string)$formData['tahun_lulus']) ?>">
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
