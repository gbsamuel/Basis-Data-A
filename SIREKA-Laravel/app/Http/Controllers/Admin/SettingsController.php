<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * admin/settings.php (khusus admin)
 * Halaman ini menggabungkan 2 bagian:
 * 1. Profil Perusahaan (tabel company)
 * 2. Pengaturan Sistem (tabel system_settings)
 */
class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $isPost = $request->isMethod('post');

        // Handle simpan profil perusahaan
        if ($isPost && $request->request->has('action_save_profile')) {
            $nama = trim($request->post('nama_company', ''));
            $alamat = trim($request->post('alamat', ''));
            $industri = trim($request->post('industri', ''));
            $emailCompany = trim($request->post('email_corporate', ''));
            $telepon = trim($request->post('no_telepon', ''));
            $deskripsi = trim($request->post('deskripsi', ''));

            if (! empty($nama) && ! empty($industri) && ! empty($emailCompany)) {
                $exists = DB::scalar('SELECT COUNT(*) FROM company WHERE id_company = 1');
                if ($exists) {
                    DB::update('
                        UPDATE company
                        SET nama_company = ?, alamat = ?, industri = ?, email_corporate = ?, no_telepon = ?, deskripsi = ?
                        WHERE id_company = 1
                    ', [$nama, $alamat, $industri, $emailCompany, $telepon, $deskripsi]);
                } else {
                    DB::insert('
                        INSERT INTO company (id_company, nama_company, alamat, industri, email_corporate, no_telepon, deskripsi)
                        VALUES (1, ?, ?, ?, ?, ?, ?)
                    ', [$nama, $alamat, $industri, $emailCompany, $telepon, $deskripsi]);
                }
                setFlash('success', 'Profil perusahaan berhasil diperbarui.');
            } else {
                setFlash('danger', 'Nama perusahaan, industri, dan email resmi wajib diisi.');
            }

            return redirect()->route('admin.settings');
        }

        // Handle simpan pengaturan sistem
        if ($isPost && $request->request->has('action_save_settings')) {
            $settingsToSave = [
                'helpdesk_whatsapp' => trim($request->post('helpdesk_whatsapp', '6281234567890')),
                'helpdesk_email' => trim($request->post('helpdesk_email', 'career@solusiteknologi.co.id')),
                'platform_name' => trim($request->post('platform_name', 'SIREKA - IT Career & Recruitment Portal')),
                'loa_authorized_signer' => trim($request->post('loa_authorized_signer', '')),
            ];

            foreach ($settingsToSave as $k => $v) {
                DB::insert('
                    INSERT INTO system_settings (setting_key, setting_value)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ', [$k, $v]);
            }

            setFlash('success', 'Pengaturan sistem berhasil diperbarui.');

            return redirect()->route('admin.settings');
        }

        // Data profil perusahaan
        $company = DB::selectOne('SELECT * FROM company WHERE id_company = 1 LIMIT 1');

        // Ringkasan angka
        $totalDivisi = DB::scalar('SELECT COUNT(*) FROM division WHERE id_company = 1');
        $totalJobs = DB::scalar('SELECT COUNT(*) FROM job WHERE id_division IN (SELECT id_division FROM division WHERE id_company = 1)');
        $totalAkun = DB::scalar('SELECT COUNT(*) FROM user_all');

        return view('admin.settings', [
            'pageTitle' => 'Pengaturan Sistem - SIREKA Admin',
            'activeSidebar' => 'settings',
            'company' => $company,
            'totalDivisi' => $totalDivisi,
            'totalJobs' => $totalJobs,
            'totalAkun' => $totalAkun,
            // Data pengaturan sistem
            'wa' => getSystemSetting('helpdesk_whatsapp', '6281234567890'),
            'email' => getSystemSetting('helpdesk_email', 'career@solusiteknologi.co.id'),
            'platform' => getSystemSetting('platform_name', 'SIREKA - IT Career & Recruitment Portal'),
            'signer' => getSystemSetting('loa_authorized_signer', 'Dr. Hendra Gunawan, S.Kom., M.M. (VP Human Capital & People Ops)'),
        ]);
    }
}
