<?php
require_once __DIR__ . '/../config/database.php';
requireRole(['admin', 'interviewer']);

header('Location: ' . BASE_URL . '/admin/dashboard.php');
exit;

$sql = "
    SELECT tp.*, u.nama as nama_kandidat, u.email, u.no_telepon, u.pendidikan_terakhir,
           c.nama_company, j.nama_job,
           GROUP_CONCAT(s.nama_skill SEPARATOR ', ') as skills_list
    FROM talent_pool tp
    JOIN user_all u ON tp.nik = u.nik
    JOIN company c ON tp.id_company = c.id_company
    LEFT JOIN application a ON tp.source_application = a.id_application
    LEFT JOIN job j ON a.id_job = j.id_job
    LEFT JOIN user_skill us ON u.nik = us.nik
    LEFT JOIN skill s ON us.id_skill = s.id_skill
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $sql .= " AND (u.nama LIKE ? OR u.nik LIKE ? OR s.nama_skill LIKE ? OR tp.reason LIKE ?)";
    $term = "%{$search}%";
    $params = [$term, $term, $term, $term];
}

if ($statusFilter !== '') {
    $sql .= " AND tp.status = ?";
    $params[] = $statusFilter;
}

$sql .= " GROUP BY tp.id_talent_pool ORDER BY tp.added_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$talents = $stmt->fetchAll();

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
                    <h5 class="fw-bold mb-0">Bank Bakat & Kandidat Potensial (Talent Pool)</h5>
                    <small class="text-muted">Kelola kandidat berkualitas untuk kebutuhan rekrutmen masa depan</small>
                </div>
            </div>
        </header>

        <div class="p-4">
            <?php renderFlash(); ?>

            <!-- Concept Card -->
            <div class="card-custom p-3 mb-4 bg-light border-purple shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-purple text-white rounded-3 p-2 px-3">
                        <i class="bi bi-stars fs-3"></i>
                    </div>
                    <div>
                        <strong class="text-dark">Talent Pool Database System:</strong>
                        <p class="text-muted small mb-0">
                            Kandidat yang tidak diterima pada batch seleksi karena keterbatasan kuota tetap diarsipkan di sini. Saat perusahaan membuka lowongan baru, HR dapat langsung mencari kandidat dari Talent Pool tanpa memulai seleksi dari nol.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="card-custom p-3 mb-4 border-0 shadow-sm">
                <form method="GET" action="<?= BASE_URL ?>/admin/talent_pool.php" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari keahlian (SQL, Python), nama kandidat..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status Talent Pool</option>
                            <option value="Available" <?= $statusFilter === 'Available' ? 'selected' : '' ?>>Available (Siap Dihubungi)</option>
                            <option value="Considered" <?= $statusFilter === 'Considered' ? 'selected' : '' ?>>Considered (Sedang Dipertimbangkan)</option>
                            <option value="Hired" <?= $statusFilter === 'Hired' ? 'selected' : '' ?>>Hired (Sudah Direkrut)</option>
                            <option value="Inactive" <?= $statusFilter === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary px-3">Cari</button>
                        <a href="<?= BASE_URL ?>/admin/talent_pool.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                    <div class="col text-md-end small text-muted">
                        Total: <strong><?= count($talents) ?></strong> Kandidat
                    </div>
                </form>
            </div>

            <!-- Talent Pool Table -->
            <div class="card-custom p-4 border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kandidat</th>
                                <th>Posisi Referensi</th>
                                <th>Keahlian Terdata</th>
                                <th>Alasan Pengarsipan</th>
                                <th>Tgl Dimasukkan</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($talents)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Tidak ada kandidat di dalam Talent Pool sesuai filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($talents as $tp): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($tp['nama_kandidat']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($tp['pendidikan_terakhir']) ?> &bull; <?= htmlspecialchars($tp['email']) ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($tp['nama_job'])): ?>
                                                <span class="badge bg-primary-light text-primary border border-primary-subtle">
                                                    <i class="bi bi-briefcase me-1"></i><?= htmlspecialchars($tp['nama_job']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border">Talenta Direct</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="max-width: 250px;">
                                            <span class="small text-dark fw-semibold">
                                                <?= htmlspecialchars($tp['skills_list'] ?? 'Belum ada skill') ?>
                                            </span>
                                        </td>
                                        <td class="small text-secondary" style="max-width: 250px;">
                                            <?= htmlspecialchars($tp['reason']) ?>
                                        </td>
                                        <td class="small text-muted">
                                            <?= formatTanggalIndo($tp['added_at']) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $tp['status'] === 'Available' ? 'bg-success' : ($tp['status'] === 'Considered' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                                <?= $tp['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Kelola
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/talent_pool.php?update_status=Considered&id=<?= $tp['id_talent_pool'] ?>">Pertimbangkan Posisi Baru (Considered)</a></li>
                                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/talent_pool.php?update_status=Hired&id=<?= $tp['id_talent_pool'] ?>">Tandai Telah Direkrut (Hired)</a></li>
                                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/talent_pool.php?update_status=Available&id=<?= $tp['id_talent_pool'] ?>">Set Available</a></li>
                                                </ul>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
