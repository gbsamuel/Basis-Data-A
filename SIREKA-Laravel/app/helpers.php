<?php

/**
 * SIREKA - Sistem Informasi Rekrutmen & Kandidat
 * Helper global aplikasi (pindahan dari config/database.php versi PHP native).
 *
 * Nama dan perilaku fungsi sengaja dibuat SAMA dengan versi native,
 * bedanya sekarang memakai fitur Laravel:
 *   - koneksi database  -> facade DB (config/database.php + file .env)
 *   - $_SESSION         -> session() Laravel
 *   - BASE_URL          -> route() / url() / asset()
 *
 * File ini dimuat otomatis lewat composer.json ("autoload" -> "files").
 */

use Illuminate\Support\Facades\DB;

/* ------------------------------------------------------------------
 | Authentication Helpers
 * ------------------------------------------------------------------ */

if (! function_exists('isLoggedIn')) {
    function isLoggedIn()
    {
        return ! empty(session('user_nik'));
    }
}

if (! function_exists('currentUser')) {
    function currentUser()
    {
        if (! isLoggedIn()) {
            return null;
        }

        return session('user');
    }
}

if (! function_exists('hasRole')) {
    function hasRole($roles)
    {
        if (! isLoggedIn()) {
            return false;
        }
        $currentRole = session('user_role', '');
        if (is_array($roles)) {
            return in_array($currentRole, $roles);
        }

        return $currentRole === $roles;
    }
}

if (! function_exists('loadUserProfile')) {
    /**
     * Ambil data lengkap satu akun: data akun (user_all) + data pelamar ATAU data staf.
     * Hasilnya disimpan di session supaya halaman lain bisa langsung memakai $user['jurusan'], dll.
     */
    function loadUserProfile($nik)
    {
        return DB::selectOne("
            SELECT u.*,
                   p.tanggal_lahir, p.jenjang_pendidikan, p.jurusan, p.institusi,
                   p.status_pendidikan, p.tahun_lulus, p.alamat,
                   CONCAT(p.jenjang_pendidikan, ' ', p.jurusan) AS pendidikan_terakhir,
                   s.jabatan, s.is_kepala_hr
            FROM user_all u
            LEFT JOIN pelamar p ON p.nik = u.nik
            LEFT JOIN staff s ON s.nik = u.nik
            WHERE u.nik = ?
        ", [$nik]);
    }
}

if (! function_exists('isKepalaHr')) {
    /**
     * Kepala HR = akun ber-role 'hr' yang ditandai is_kepala_hr = 1 di tabel staff.
     */
    function isKepalaHr()
    {
        return hasRole('hr') && ! empty(session('user.is_kepala_hr'));
    }
}

if (! function_exists('canProcessJob')) {
    /**
     * Apakah HR yang sedang login boleh memproses lowongan ini?
     * Boleh jika ia PIC lowongan tersebut, atau jika ia kepala HR.
     */
    function canProcessJob($idJob)
    {
        if (! hasRole('hr')) {
            return false;
        }
        if (isKepalaHr()) {
            return true;
        }
        $picNik = DB::scalar('SELECT pic_nik FROM job WHERE id_job = ?', [$idJob]);

        return $picNik === session('user_nik', '');
    }
}

if (! function_exists('jenjangOptions')) {
    /**
     * Daftar pilihan jenjang pendidikan (sama dengan ENUM di database).
     */
    function jenjangOptions()
    {
        return ['SMA/SMK', 'D3', 'S1', 'S2', 'S3'];
    }
}

if (! function_exists('homeUrl')) {
    /**
     * Halaman pertama sesuai role:
     * admin -> Pengaturan Sistem, hr -> Dashboard HR, user -> Dashboard Pelamar
     */
    function homeUrl()
    {
        if (hasRole('admin')) {
            return route('admin.settings');
        }
        if (hasRole('hr')) {
            return route('admin.dashboard');
        }

        return route('user.dashboard');
    }
}

/* ------------------------------------------------------------------
 | Flash Notification Helpers
 | (sengaja tidak memakai session()->flash() bawaan Laravel, supaya pesan
 |  bisa tampil di request yang sama seperti versi native)
 * ------------------------------------------------------------------ */

if (! function_exists('setFlash')) {
    function setFlash($type, $message)
    {
        session()->put('flash', [
            'type' => $type, // success, danger, warning, info
            'message' => $message,
        ]);
    }
}

if (! function_exists('getFlash')) {
    function getFlash()
    {
        return session()->pull('flash');
    }
}

if (! function_exists('renderFlash')) {
    function renderFlash()
    {
        $flash = getFlash();
        if (! $flash) {
            return '';
        }

        $type = htmlspecialchars($flash['type']);
        $msg = htmlspecialchars($flash['message']);
        $icon = 'bi-info-circle';
        if ($type === 'success') {
            $icon = 'bi-check-circle-fill';
        } elseif ($type === 'danger') {
            $icon = 'bi-exclamation-triangle-fill';
        } elseif ($type === 'warning') {
            $icon = 'bi-exclamation-circle-fill';
        }

        return "<div class='alert alert-{$type} alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm' role='alert'>
                <i class='bi {$icon} me-2 fs-5'></i>
                <div>{$msg}</div>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

/* ------------------------------------------------------------------
 | Match Score Calculation System
 | Formula: Match Score = (Jumlah skill kandidat yang cocok / Jumlah skill yang dibutuhkan posisi) * 100%
 * ------------------------------------------------------------------ */

if (! function_exists('calculateMatchScore')) {
    function calculateMatchScore($nik, $id_job)
    {
        // 1. Ambil skill yang dibutuhkan oleh lowongan kerja
        $jobSkills = DB::table('job_skill')->where('id_job', $id_job)->where('is_required', 1)->pluck('id_skill')->all();

        $totalRequired = count($jobSkills);
        if ($totalRequired === 0) {
            return 100.00; // Posisi umum tanpa spesifikasi skill khusus
        }

        // 2. Ambil skill yang dimiliki kandidat
        $userSkills = DB::table('user_skill')->where('nik', $nik)->pluck('id_skill')->all();

        // 3. Hitung persentase kecocokan (Intersection)
        $matched = array_intersect($jobSkills, $userSkills);
        $matchedCount = count($matched);

        $score = ($matchedCount / $totalRequired) * 100;

        return round($score, 2);
    }
}

if (! function_exists('getJobSkillsDetail')) {
    /**
     * Get detailed skill match breakdown for UI chips
     */
    function getJobSkillsDetail($nik, $id_job)
    {
        // Ambil semua skill pada lowongan
        $jobSkills = DB::select('
            SELECT s.id_skill, s.nama_skill, s.category, js.is_required
            FROM job_skill js
            JOIN skill s ON js.id_skill = s.id_skill
            WHERE js.id_job = ?
            ORDER BY s.nama_skill ASC
        ', [$id_job]);

        // Ambil list skill id kandidat
        $userSkillIds = [];
        if (! empty($nik)) {
            $userSkillIds = DB::table('user_skill')->where('nik', $nik)->pluck('id_skill')->all();
        }

        $matchedCount = 0;
        $totalRequired = 0;

        foreach ($jobSkills as &$item) {
            $isMatched = in_array($item['id_skill'], $userSkillIds);
            $item['matched'] = $isMatched;
            if ($item['is_required']) {
                $totalRequired++;
                if ($isMatched) {
                    $matchedCount++;
                }
            }
        }
        unset($item);

        $score = $totalRequired > 0 ? round(($matchedCount / $totalRequired) * 100, 2) : 100.00;

        return [
            'skills' => $jobSkills,
            'matched_count' => $matchedCount,
            'total_required' => $totalRequired,
            'score' => $score,
        ];
    }
}

if (! function_exists('recordStageHistory')) {
    /**
     * Record recruitment stage progression in database history
     */
    function recordStageHistory($id_application, $stage, $status, $notes, $changed_by = null)
    {
        return DB::insert('
            INSERT INTO candidate_stage_history (id_application, stage, status, notes, changed_by, changed_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ', [$id_application, $stage, $status, $notes, $changed_by]);
    }
}

/* ------------------------------------------------------------------
 | Format Helpers
 * ------------------------------------------------------------------ */

if (! function_exists('formatTanggalIndo')) {
    /**
     * Format date in Indonesian
     */
    function formatTanggalIndo($datetime)
    {
        if (empty($datetime)) {
            return '-';
        }
        $timestamp = strtotime($datetime);
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $tgl = date('d', $timestamp);
        $bln = $bulan[(int) date('m', $timestamp)];
        $thn = date('Y', $timestamp);

        return "{$tgl} {$bln} {$thn}";
    }
}

if (! function_exists('formatRupiah')) {
    /**
     * Format currency to IDR
     */
    function formatRupiah($nominal)
    {
        if ($nominal === null || $nominal === '') {
            return 'Negosiasi';
        }

        return 'Rp '.number_format($nominal, 0, ',', '.');
    }
}

if (! function_exists('getSystemSetting')) {
    /**
     * Get system setting
     */
    function getSystemSetting($key, $default = '')
    {
        try {
            $val = DB::scalar('SELECT setting_value FROM system_settings WHERE setting_key = ?', [$key]);

            return $val !== null ? $val : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

if (! function_exists('getStatusBadge')) {
    /**
     * Get Status Badge HTML
     */
    function getStatusBadge($status)
    {
        $map = [
            'Applied' => ['bg' => 'primary', 'icon' => 'bi-file-earmark-arrow-up', 'text' => 'Applied'],
            'HR Review' => ['bg' => 'info text-dark', 'icon' => 'bi-person-check', 'text' => 'HR Review'],
            'Document Screening' => ['bg' => 'secondary', 'icon' => 'bi-files', 'text' => 'Document Screening'],
            'Interview Scheduling' => ['bg' => 'warning text-dark', 'icon' => 'bi-calendar-event', 'text' => 'Interview Scheduling'],
            'Interview' => ['bg' => 'warning text-dark', 'icon' => 'bi-camera-video', 'text' => 'Interview'],
            'Final Decision' => ['bg' => 'info text-dark', 'icon' => 'bi-hourglass-split', 'text' => 'Final Decision'],
            'Accepted' => ['bg' => 'success', 'icon' => 'bi-check-circle-fill', 'text' => 'Accepted'],
            'Rejected' => ['bg' => 'danger', 'icon' => 'bi-x-circle-fill', 'text' => 'Rejected'],
            'Talent Pool' => ['bg' => 'purple text-white', 'icon' => 'bi-star-fill', 'text' => 'Talent Pool'],
        ];

        $info = $map[$status] ?? ['bg' => 'secondary', 'icon' => 'bi-circle', 'text' => $status];

        return "<span class='badge bg-{$info['bg']} d-inline-flex align-items-center gap-1 px-2.5 py-1.5 shadow-sm rounded-pill font-semibold'>
                <i class='bi {$info['icon']}'></i> {$info['text']}
            </span>";
    }
}

if (! function_exists('aboutImageUrl')) {
    /**
     * Mengubah nilai 'foto' (config/about.php) menjadi URL yang bisa dipakai di tag <img>.
     * Kalau foto dari internet (http/https), dipakai apa adanya.
     * Kalau foto lokal (misal assets/img/team/budi.jpg), dijadikan URL lewat asset().
     */
    function aboutImageUrl($path)
    {
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
