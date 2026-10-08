<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** admin/users.php - Kelola Akun (khusus admin) */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $me = currentUser();
        $isPost = $request->isMethod('post');

        // Handle tambah akun HR baru
        if ($isPost && $request->request->has('action_add_hr')) {
            $nik = trim($request->post('nik', ''));
            $nama = trim($request->post('nama', ''));
            $email = trim($request->post('email', ''));
            $noTelepon = trim($request->post('no_telepon', ''));
            $jabatan = trim($request->post('jabatan', 'HR Recruiter'));
            $isKepala = $request->request->has('is_kepala_hr') ? 1 : 0;
            $password = $request->post('password', '');

            // Cek NIK atau email sudah dipakai atau belum
            $sudahAda = DB::scalar('SELECT COUNT(*) FROM user_all WHERE nik = ? OR email = ?', [$nik, $email]);

            if (empty($nik) || empty($nama) || empty($email) || empty($noTelepon) || empty($jabatan)) {
                setFlash('danger', 'Semua kolom bertanda * wajib diisi.');
            } elseif (strlen($nik) !== 16 || ! ctype_digit($nik)) {
                setFlash('danger', 'NIK harus 16 digit angka.');
            } elseif (strlen($password) < 6) {
                setFlash('danger', 'Password minimal 6 karakter.');
            } elseif ($sudahAda > 0) {
                setFlash('danger', 'NIK atau email sudah terdaftar.');
            } else {
                // Akun HR = 1 baris di user_all (role 'hr') + 1 baris di staff (jabatan & penanda kepala HR)
                DB::transaction(function () use ($nik, $nama, $email, $password, $noTelepon, $jabatan, $isKepala) {
                    DB::insert("
                        INSERT INTO user_all (nik, nama, email, password, no_telepon, role)
                        VALUES (?, ?, ?, ?, ?, 'hr')
                    ", [$nik, $nama, $email, Hash::make($password), $noTelepon]);

                    DB::insert('INSERT INTO staff (nik, jabatan, is_kepala_hr) VALUES (?, ?, ?)', [$nik, $jabatan, $isKepala]);
                });

                setFlash('success', 'Akun HR untuk '.$nama.' berhasil dibuat.');
            }

            return redirect()->route('admin.users');
        }

        // Handle jadikan / cabut status kepala HR (hanya untuk akun HR)
        if ($isPost && $request->request->has('action_toggle_kepala')) {
            $hrNik = trim($request->post('nik', ''));
            DB::update("
                UPDATE staff s
                JOIN user_all u ON u.nik = s.nik
                SET s.is_kepala_hr = 1 - s.is_kepala_hr
                WHERE s.nik = ? AND u.role = 'hr'
            ", [$hrNik]);
            setFlash('success', 'Status kepala HR berhasil diperbarui.');

            return redirect()->route('admin.users');
        }

        // Handle reset password (Update pada kolom user_all.password).
        // Admin mengisi password sementara, lalu pengguna menggantinya sendiri di halaman Profil.
        if ($isPost && $request->request->has('action_reset_password')) {
            $resetNik = trim($request->post('nik', ''));
            $newPassword = $request->post('new_password', '');

            if (strlen($newPassword) < 6) {
                setFlash('danger', 'Password sementara minimal 6 karakter.');
            } else {
                // Hanya akun pelamar dan HR yang bisa direset dari sini
                $affected = DB::update("UPDATE user_all SET password = ? WHERE nik = ? AND role IN ('user', 'hr')", [Hash::make($newPassword), $resetNik]);
                if ($affected > 0) {
                    setFlash('success', 'Password berhasil direset. Berikan password sementara ini kepada pemilik akun.');
                } else {
                    setFlash('danger', 'Password akun ini tidak dapat direset.');
                }
            }

            return redirect()->route('admin.users');
        }

        // Handle hapus akun (pelamar atau HR). Akun admin tidak bisa dihapus dari sini.
        if ($isPost && $request->request->has('action_delete')) {
            $delNik = trim($request->post('nik', ''));

            $deleted = DB::delete("DELETE FROM user_all WHERE nik = ? AND role IN ('user', 'hr') AND nik <> ?", [$delNik, $me['nik']]);

            if ($deleted > 0) {
                setFlash('info', 'Akun berhasil dihapus beserta data yang terhubung.');
            } else {
                setFlash('danger', 'Akun tidak dapat dihapus.');
            }

            return redirect()->route('admin.users');
        }

        // Filter berdasarkan role dan kata kunci
        $roleFilter = $request->query('role', '');
        $search = trim($request->query('search', ''));

        $sql = '
            SELECT u.nik, u.nama, u.email, u.no_telepon, u.role, u.created_at,
                   s.jabatan AS position, s.is_kepala_hr,
                   (SELECT COUNT(*) FROM job j WHERE j.pic_nik = u.nik) AS jumlah_pic
            FROM user_all u
            LEFT JOIN staff s ON s.nik = u.nik
            WHERE 1=1
        ';
        $params = [];
        if (in_array($roleFilter, ['user', 'hr', 'admin'])) {
            $sql .= ' AND u.role = ?';
            $params[] = $roleFilter;
        }
        if ($search !== '') {
            $sql .= ' AND (u.nama LIKE ? OR u.email LIKE ? OR u.nik LIKE ?)';
            $term = "%{$search}%";
            array_push($params, $term, $term, $term);
        }
        $sql .= " ORDER BY FIELD(u.role, 'admin', 'hr', 'user'), u.nama ASC";

        $accounts = DB::select($sql, $params);

        // Jumlah akun per role untuk kartu ringkasan
        $counts = ['user' => 0, 'hr' => 0, 'admin' => 0];
        foreach (DB::select('SELECT role, COUNT(*) AS jumlah FROM user_all GROUP BY role') as $row) {
            $counts[$row['role']] = $row['jumlah'];
        }

        return view('admin.users', [
            'pageTitle' => 'Kelola Akun - SIREKA Admin',
            'activeSidebar' => 'users',
            'me' => $me,
            'roleFilter' => $roleFilter,
            'search' => $search,
            'accounts' => $accounts,
            'counts' => $counts,
            'roleLabels' => ['user' => 'Pelamar', 'hr' => 'HR', 'admin' => 'Admin'],
            'roleBadges' => ['user' => 'bg-secondary', 'hr' => 'bg-primary', 'admin' => 'bg-dark'],
        ]);
    }
}
