<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\UploadsProfilePhoto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** admin/profile.php (profil staf: HR dan admin) */
class ProfileController extends Controller
{
    use UploadsProfilePhoto;

    public function index(Request $request)
    {
        $nik = currentUser()['nik'];

        // Ambil data terbaru (akun + data staf) lalu simpan ulang ke session
        $user = loadUserProfile($nik);
        session(['user' => $user]);

        $isPost = $request->isMethod('post');

        // Handle upload / ganti foto profil (sama seperti di profil pelamar)
        if ($isPost && $request->request->has('action_upload_photo')) {
            $this->uploadProfilePhoto($request, $nik, $user);

            return redirect()->route('admin.profile');
        }

        // Handle update data diri
        if ($isPost && $request->request->has('action_update_profile')) {
            $nama = trim($request->post('nama', ''));
            $noTelepon = trim($request->post('no_telepon', ''));

            if (! empty($nama) && ! empty($noTelepon)) {
                DB::update('UPDATE user_all SET nama = ?, no_telepon = ? WHERE nik = ?', [$nama, $noTelepon, $nik]);

                // Perbarui session supaya nama baru langsung tampil di header
                session(['user' => loadUserProfile($nik), 'user_name' => $nama]);
                setFlash('success', 'Data diri berhasil diperbarui.');
            } else {
                setFlash('danger', 'Nama dan nomor telepon wajib diisi.');
            }

            return redirect()->route('admin.profile');
        }

        // Handle ganti kata sandi
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
                setFlash('success', 'Kata sandi berhasil diperbarui.');
            }

            return redirect()->route('admin.profile');
        }

        // Jabatan staf diambil dari tabel staff (sudah ikut dimuat oleh loadUserProfile)
        $jabatan = $user['jabatan'] ?? '-';
        $roleLabel = $user['role'] === 'admin' ? 'Admin' : (isKepalaHr() ? 'Kepala HR' : 'HR');

        return view('admin.profile', [
            'pageTitle' => 'Profil Saya - SIREKA Admin',
            'activeSidebar' => 'profile',
            'user' => $user,
            'jabatan' => $jabatan,
            'roleLabel' => $roleLabel,
        ]);
    }
}
