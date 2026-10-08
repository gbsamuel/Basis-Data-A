<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** admin/talent_pool.php (khusus HR) */
class TalentPoolController extends Controller
{
    public function index(Request $request)
    {
        // Ubah status kandidat talent pool (Available / Considered / Hired / Inactive)
        if ($request->query('update_status') !== null && $request->query('id') !== null) {
            $newStatus = $request->query('update_status');
            if (in_array($newStatus, ['Available', 'Considered', 'Hired', 'Inactive'])) {
                DB::update('UPDATE talent_pool SET status = ? WHERE id_talent_pool = ?', [$newStatus, (int) $request->query('id')]);
                setFlash('success', 'Status talent pool berhasil diperbarui menjadi '.$newStatus.'.');
            }

            return redirect()->route('admin.talent_pool');
        }

        $search = trim($request->query('search', ''));
        $statusFilter = trim($request->query('status', ''));

        // Satu baris per kandidat, lengkap dengan lowongan asal dan daftar skill-nya
        $sql = "
            SELECT tp.*, u.nama as nama_kandidat, u.email, u.no_telepon,
                   CONCAT(p.jenjang_pendidikan, ' ', p.jurusan) AS pendidikan_terakhir,
                   j.nama_job, hr.nama AS nama_hr,
                   GROUP_CONCAT(s.nama_skill SEPARATOR ', ') as skills_list
            FROM talent_pool tp
            JOIN user_all u ON tp.nik = u.nik
            JOIN pelamar p ON p.nik = tp.nik
            LEFT JOIN application a ON tp.source_application = a.id_application
            LEFT JOIN job j ON a.id_job = j.id_job
            LEFT JOIN user_all hr ON hr.nik = tp.added_by
            LEFT JOIN user_skill us ON tp.nik = us.nik
            LEFT JOIN skill s ON us.id_skill = s.id_skill
            WHERE 1=1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (u.nama LIKE ? OR u.nik LIKE ? OR s.nama_skill LIKE ? OR tp.reason LIKE ?)';
            $term = "%{$search}%";
            $params = [$term, $term, $term, $term];
        }

        if ($statusFilter !== '') {
            $sql .= ' AND tp.status = ?';
            $params[] = $statusFilter;
        }

        $sql .= ' GROUP BY tp.id_talent_pool ORDER BY tp.added_at DESC';

        $talents = DB::select($sql, $params);

        return view('admin.talent_pool', [
            'pageTitle' => 'Talent Pool - SIREKA Admin',
            'activeSidebar' => 'talent_pool',
            'search' => $search,
            'statusFilter' => $statusFilter,
            'talents' => $talents,
        ]);
    }
}
