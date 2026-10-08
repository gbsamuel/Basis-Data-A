<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Upload / ganti foto profil.
 * Dipakai oleh profil pelamar (user/profile) dan profil staf (admin/profile),
 * sama seperti blok "action_upload_photo" di kedua file versi native.
 */
trait UploadsProfilePhoto
{
    protected function uploadProfilePhoto(Request $request, string $nik, array $user): void
    {
        $file = $request->file('profile_photo');
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 2 * 1024 * 1024; // 2 MB

        if (! $file || ! $file->isValid()) {
            setFlash('danger', 'Pilih file foto terlebih dahulu.');

            return;
        }

        $ext = strtolower($file->getClientOriginalExtension());

        if (! in_array($ext, $allowedExt)) {
            setFlash('danger', 'Format foto harus JPG, PNG, atau WEBP.');
        } elseif ($file->getSize() > $maxSize) {
            setFlash('danger', 'Ukuran foto maksimal 2 MB.');
        } elseif (@getimagesize($file->getRealPath()) === false) {
            setFlash('danger', 'File yang diunggah bukan gambar.');
        } else {
            // Nama file dibuat unik: NIK + waktu upload
            $newName = $nik.'_'.time().'.'.$ext;
            $uploadDir = public_path('uploads/profiles').DIRECTORY_SEPARATOR;

            try {
                $file->move($uploadDir, $newName);
                $moved = true;
            } catch (\Throwable $e) {
                $moved = false;
            }

            if ($moved) {
                // Hapus foto lama agar folder tidak penuh
                if (! empty($user['profile_photo']) && file_exists($uploadDir.$user['profile_photo'])) {
                    unlink($uploadDir.$user['profile_photo']);
                }

                // Simpan nama file ke database
                DB::update('UPDATE user_all SET profile_photo = ? WHERE nik = ?', [$newName, $nik]);

                // Perbarui data user di session supaya foto langsung tampil
                session()->put('user.profile_photo', $newName);
                setFlash('success', 'Foto profil berhasil diperbarui.');
            } else {
                setFlash('danger', 'Foto gagal diunggah. Coba lagi.');
            }
        }
    }
}
