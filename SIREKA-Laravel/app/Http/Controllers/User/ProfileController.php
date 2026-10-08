<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Concerns\UploadsProfilePhoto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** user/profile.php */
class ProfileController extends Controller
{
    use UploadsProfilePhoto;

    public function index(Request $request)
    {
        $nik = currentUser()['nik'];

        // Ambil data terbaru (akun + data pelamar) lalu simpan ulang ke session
        $user = loadUserProfile($nik);
        session(['user' => $user]);

        $isPost = $request->isMethod('post');

        // Handle profile update
        if ($isPost && $request->request->has('action_update_profile')) {
            $nama = trim($request->post('nama', ''));
            $no_telepon = trim($request->post('no_telepon', ''));
            $tanggal_lahir = trim($request->post('tanggal_lahir', ''));
            $jenjang = trim($request->post('jenjang_pendidikan', ''));
            $jurusan = trim($request->post('jurusan', ''));
            $institusi = trim($request->post('institusi', ''));
            $statusPendidikan = trim($request->post('status_pendidikan', 'Lulus'));
            $tahun_lulus = (int) $request->post('tahun_lulus', 0);
            $alamat = trim($request->post('alamat', ''));

            if (! empty($nama) && ! empty($no_telepon) && in_array($jenjang, jenjangOptions()) && ! empty($jurusan) && ! empty($institusi)) {
                // Data akun disimpan di user_all
                DB::update('UPDATE user_all SET nama = ?, no_telepon = ? WHERE nik = ?', [$nama, $no_telepon, $nik]);

                // Data khusus pelamar disimpan di tabel pelamar
                DB::update('
                    UPDATE pelamar
                    SET tanggal_lahir = ?, jenjang_pendidikan = ?, jurusan = ?, institusi = ?, status_pendidikan = ?, tahun_lulus = ?, alamat = ?
                    WHERE nik = ?
                ', [$tanggal_lahir, $jenjang, $jurusan, $institusi, $statusPendidikan, $tahun_lulus, $alamat, $nik]);

                // Refresh session
                session(['user' => loadUserProfile($nik), 'user_name' => $nama]);

                setFlash('success', 'Profil Anda berhasil diperbarui.');

                return redirect()->route('user.profile');
            } else {
                setFlash('danger', 'Semua field wajib harus diisi dengan benar.');
            }
        }

        // Handle upload / ganti foto profil
        if ($isPost && $request->request->has('action_upload_photo')) {
            $this->uploadProfilePhoto($request, $nik, $user);

            return redirect()->route('user.profile');
        }

        // Handle Add Skill
        if ($isPost && $request->request->has('action_add_skill')) {
            $skillId = (int) $request->post('id_skill', 0);
            $level = trim($request->post('level', 'Intermediate'));

            if ($skillId > 0) {
                $exists = DB::scalar('SELECT COUNT(*) FROM user_skill WHERE nik = ? AND id_skill = ?', [$nik, $skillId]);
                if ($exists == 0) {
                    DB::insert('INSERT INTO user_skill (nik, id_skill, level) VALUES (?, ?, ?)', [$nik, $skillId, $level]);
                    setFlash('success', 'Keahlian baru berhasil ditambahkan.');
                } else {
                    setFlash('warning', 'Keahlian tersebut sudah terdaftar pada profil Anda.');
                }
            }

            return redirect()->route('user.profile');
        }

        // Handle Delete Skill
        if ($request->query('delete_skill') !== null) {
            $delSkillId = (int) $request->query('delete_skill');
            DB::delete('DELETE FROM user_skill WHERE nik = ? AND id_skill = ?', [$nik, $delSkillId]);
            setFlash('info', 'Keahlian berhasil dihapus dari profil.');

            return redirect()->route('user.profile');
        }

        // Handle Change Password
        if ($isPost && $request->request->has('action_change_password')) {
            $oldPass = $request->post('old_password', '');
            $newPass = $request->post('new_password', '');
            $confirmPass = $request->post('confirm_password', '');

            $currentHash = DB::scalar('SELECT password FROM user_all WHERE nik = ?', [$nik]);

            if (! Hash::check($oldPass, $currentHash)) {
                setFlash('danger', 'Kata sandi saat ini tidak sesuai.');
            } elseif (strlen($newPass) < 6) {
                setFlash('danger', 'Kata sandi baru minimal 6 karakter.');
            } elseif ($newPass !== $confirmPass) {
                setFlash('danger', 'Konfirmasi kata sandi baru tidak cocok.');
            } else {
                DB::update('UPDATE user_all SET password = ? WHERE nik = ?', [Hash::make($newPass), $nik]);
                setFlash('success', 'Kata sandi Anda berhasil diperbarui.');
            }

            return redirect()->route('user.profile');
        }

        // Get candidate skills
        $mySkills = DB::select('
            SELECT us.*, s.nama_skill, s.category
            FROM user_skill us
            JOIN skill s ON us.id_skill = s.id_skill
            WHERE us.nik = ?
            ORDER BY s.nama_skill ASC
        ', [$nik]);

        // Get all available skills for dropdown
        $availableSkills = DB::select('SELECT * FROM skill ORDER BY nama_skill ASC');

        return view('user.profile', [
            'pageTitle' => 'Profil & Pengelolaan Keahlian - SIREKA',
            'activeSidebar' => 'profile',
            'user' => $user,
            'mySkills' => $mySkills,
            'availableSkills' => $availableSkills,
        ]);
    }
}
