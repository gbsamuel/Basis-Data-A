<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * auth/login.php, auth/process_login.php, auth/register.php, auth/logout.php
 */
class AuthController extends Controller
{
    /** auth/login.php */
    public function login(Request $request)
    {
        if (isLoggedIn()) {
            return redirect(homeUrl());
        }

        return view('auth.login', [
            'pageTitle' => 'Login Portal - SIREKA',
            'redirect' => $request->query('redirect', ''),
            // Halaman login tampil penuh tanpa navbar dan footer biasa
            'hideFooter' => true,
        ]);
    }

    /** auth/process_login.php */
    public function processLogin(Request $request)
    {
        if (! $request->isMethod('post')) {
            return redirect()->route('auth.login');
        }

        $email = trim($request->post('email', ''));
        $password = trim($request->post('password', ''));
        $redirect = trim($request->post('redirect', ''));

        if (empty($email) || empty($password)) {
            setFlash('danger', 'Email dan password wajib diisi.');

            return redirect()->route('auth.login');
        }

        $user = DB::selectOne('SELECT * FROM user_all WHERE email = ?', [$email]);

        if (! $user || ! Hash::check($password, $user['password'])) {
            setFlash('danger', 'Email atau password yang Anda masukkan salah.');

            return redirect()->route('auth.login');
        }

        // Set session (nama kunci session sama dengan versi native)
        $request->session()->regenerate();
        session([
            'user_nik' => $user['nik'],
            'user_name' => $user['nama'],
            'user_role' => $user['role'],
            'user_email' => $user['email'],
            // Simpan data lengkap (akun + data pelamar / data staf) ke session
            'user' => loadUserProfile($user['nik']),
        ]);

        setFlash('success', 'Selamat datang kembali, '.htmlspecialchars($user['nama']).'!');

        // Redirect logic
        if (! empty($redirect)) {
            return redirect(url('/'.ltrim($redirect, '/')));
        }

        // Arahkan ke halaman pertama sesuai role (lihat fungsi homeUrl di app/helpers.php)
        return redirect(homeUrl());
    }

    /** auth/register.php */
    public function register(Request $request)
    {
        if (isLoggedIn()) {
            return redirect()->route('user.dashboard');
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
            'alamat' => '',
        ];

        if ($request->isMethod('post')) {
            $formData['nik'] = trim($request->post('nik', ''));
            $formData['nama'] = trim($request->post('nama', ''));
            $formData['email'] = trim($request->post('email', ''));
            $formData['no_telepon'] = trim($request->post('no_telepon', ''));
            $formData['tanggal_lahir'] = trim($request->post('tanggal_lahir', ''));
            $formData['jenjang_pendidikan'] = trim($request->post('jenjang_pendidikan', ''));
            $formData['jurusan'] = trim($request->post('jurusan', ''));
            $formData['institusi'] = trim($request->post('institusi', ''));
            $formData['status_pendidikan'] = trim($request->post('status_pendidikan', 'Lulus'));
            $formData['tahun_lulus'] = (int) $request->post('tahun_lulus', 0);
            $formData['alamat'] = trim($request->post('alamat', ''));
            $password = $request->post('password', '');
            $confirmPassword = $request->post('confirm_password', '');

            // Validation
            if (empty($formData['nik']) || strlen($formData['nik']) !== 16 || ! ctype_digit($formData['nik'])) {
                $errors[] = 'NIK harus berupa 16 digit angka sesuai KTP.';
            }

            if (empty($formData['nama'])) {
                $errors[] = 'Nama lengkap wajib diisi.';
            }

            if (empty($formData['email']) || ! filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format alamat email tidak valid.';
            }

            if (empty($formData['no_telepon'])) {
                $errors[] = 'Nomor telepon/WhatsApp wajib diisi.';
            }

            if (empty($formData['tanggal_lahir'])) {
                $errors[] = 'Tanggal lahir wajib diisi.';
            }

            if (! in_array($formData['jenjang_pendidikan'], jenjangOptions())) {
                $errors[] = 'Jenjang pendidikan wajib dipilih.';
            }

            if (empty($formData['jurusan']) || empty($formData['institusi'])) {
                $errors[] = 'Jurusan dan nama sekolah/kampus wajib diisi.';
            }

            if (! in_array($formData['status_pendidikan'], ['Lulus', 'Masih Sekolah/Kuliah'])) {
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
                if (DB::selectOne('SELECT nik FROM user_all WHERE nik = ?', [$formData['nik']])) {
                    $errors[] = 'NIK tersebut sudah terdaftar di sistem SIREKA.';
                }

                if (DB::selectOne('SELECT email FROM user_all WHERE email = ?', [$formData['email']])) {
                    $errors[] = 'Alamat email tersebut sudah terdaftar di sistem SIREKA.';
                }
            }

            // Simpan akun: 1 baris di user_all (data akun) + 1 baris di pelamar (data khusus pelamar).
            // Dipakai transaksi supaya keduanya tersimpan bersama, atau tidak sama sekali.
            if (empty($errors)) {
                $hashedPassword = Hash::make($password);
                try {
                    DB::beginTransaction();

                    DB::insert("
                        INSERT INTO user_all (nik, nama, email, password, no_telepon, role)
                        VALUES (?, ?, ?, ?, ?, 'user')
                    ", [
                        $formData['nik'], $formData['nama'], $formData['email'], $hashedPassword, $formData['no_telepon'],
                    ]);

                    DB::insert('
                        INSERT INTO pelamar (nik, tanggal_lahir, jenjang_pendidikan, jurusan, institusi, status_pendidikan, tahun_lulus, alamat)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ', [
                        $formData['nik'], $formData['tanggal_lahir'], $formData['jenjang_pendidikan'], $formData['jurusan'],
                        $formData['institusi'], $formData['status_pendidikan'], $formData['tahun_lulus'], $formData['alamat'],
                    ]);

                    DB::commit();
                    $success = true;
                } catch (Exception $e) {
                    DB::rollBack();
                    $success = false;
                }

                if ($success) {
                    setFlash('success', 'Pendaftaran akun berhasil! Silakan login dan lengkapi keahlian Anda.');

                    return redirect()->route('auth.login');
                } else {
                    $errors[] = 'Gagal menyimpan data akun. Silakan coba lagi.';
                }
            }
        }

        return view('auth.register', [
            'pageTitle' => 'Pendaftaran Akun Pelamar - SIREKA',
            'errors' => $errors,
            'formData' => $formData,
        ]);
    }

    /** auth/logout.php */
    public function logout(Request $request)
    {
        // Hapus seluruh isi session lalu buat session baru
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        setFlash('info', 'Anda telah berhasil keluar dari sistem SIREKA.');

        return redirect()->route('auth.login');
    }
}
