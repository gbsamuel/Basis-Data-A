<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** admin/divisions.php (khusus admin) */
class DivisionController extends Controller
{
    public function index(Request $request)
    {
        $isPost = $request->isMethod('post');

        // Handle Create / Update
        if ($isPost && $request->request->has('action_save_division')) {
            $idDivision = (int) $request->post('id_division', 0);
            $nama = trim($request->post('nama_divisi', ''));
            $deskripsi = trim($request->post('deskripsi', ''));

            if (! empty($nama)) {
                if ($idDivision > 0) {
                    DB::update('UPDATE division SET id_company = 1, nama_divisi = ?, deskripsi = ? WHERE id_division = ?', [$nama, $deskripsi, $idDivision]);
                    setFlash('success', 'Data divisi IT berhasil diperbarui.');
                } else {
                    DB::insert('INSERT INTO division (id_company, nama_divisi, deskripsi) VALUES (1, ?, ?)', [$nama, $deskripsi]);
                    setFlash('success', 'Divisi IT baru berhasil ditambahkan.');
                }
            } else {
                setFlash('danger', 'Nama divisi IT wajib diisi.');
            }

            return redirect()->route('admin.divisions');
        }

        // Handle ganti PIC lowongan (admin sebagai cadangan jika kepala HR berhalangan)
        if ($isPost && $request->request->has('action_set_pic')) {
            $idJob = (int) $request->post('id_job', 0);
            $picNik = trim($request->post('pic_nik', '')) ?: null;
            DB::update('UPDATE job SET pic_nik = ? WHERE id_job = ?', [$picNik, $idJob]);
            setFlash('success', 'PIC lowongan berhasil diperbarui.');

            return redirect()->route('admin.divisions');
        }

        // Handle Delete
        if ($request->query('delete') !== null) {
            $delId = (int) $request->query('delete');
            DB::delete('DELETE FROM division WHERE id_division = ? AND id_company = 1', [$delId]);
            setFlash('info', 'Divisi IT berhasil dihapus beserta lowongan terkait.');

            return redirect()->route('admin.divisions');
        }

        // Query divisions for our IT company
        $divisions = DB::select('
            SELECT d.*, c.nama_company,
                   COUNT(j.id_job) as total_jobs
            FROM division d
            JOIN company c ON d.id_company = c.id_company
            LEFT JOIN job j ON j.id_division = d.id_division
            WHERE d.id_company = 1
            GROUP BY d.id_division
            ORDER BY d.nama_divisi ASC
        ');

        // Semua lowongan, dikelompokkan per divisi (ditampilkan saat tombol "N Lowongan" diklik)
        $jobsByDivision = [];
        $allJobs = DB::select('
            SELECT id_job, id_division, pic_nik, nama_job, job_type, sistem_kerja, deadline, status
            FROM job
            ORDER BY created_at DESC
        ');
        foreach ($allJobs as $job) {
            $jobsByDivision[$job['id_division']][] = $job;
        }

        // Daftar akun HR untuk pilihan PIC
        $hrList = DB::select("
            SELECT u.nik, u.nama FROM user_all u
            WHERE u.role = 'hr'
            ORDER BY u.nama ASC
        ");

        return view('admin.divisions', [
            'pageTitle' => 'Manajemen Divisi IT - SIREKA Admin',
            'activeSidebar' => 'divisions',
            'divisions' => $divisions,
            'jobsByDivision' => $jobsByDivision,
            'hrList' => $hrList,
        ]);
    }
}
