<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** admin/candidates.php (khusus HR) */
class CandidateController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));

        $sql = "
            SELECT u.*, p.tanggal_lahir, p.jurusan, p.institusi, p.status_pendidikan, p.tahun_lulus, p.alamat,
                   CONCAT(p.jenjang_pendidikan, ' ', p.jurusan) AS pendidikan_terakhir,
                   COUNT(DISTINCT a.id_application) as total_applications,
                   COUNT(DISTINCT us.id_skill) as total_skills,
                   (SELECT current_status FROM application a2 WHERE a2.nik = u.nik ORDER BY a2.applied_at DESC LIMIT 1) as latest_status
            FROM user_all u
            JOIN pelamar p ON p.nik = u.nik
            JOIN application a ON u.nik = a.nik   -- JOIN biasa: hanya pelamar yang pernah melamar minimal 1 kali
            LEFT JOIN user_skill us ON u.nik = us.nik
            WHERE u.role = 'user'
        ";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (u.nama LIKE ? OR u.nik LIKE ? OR u.email LIKE ? OR p.jurusan LIKE ? OR p.institusi LIKE ?)';
            $term = "%{$search}%";
            $params = [$term, $term, $term, $term, $term];
        }

        $sql .= ' GROUP BY u.nik ORDER BY u.created_at DESC';

        $candidates = DB::select($sql, $params);

        return view('admin.candidates', [
            'pageTitle' => 'Direktori Data Kandidat - SIREKA Admin',
            'activeSidebar' => 'candidates',
            'search' => $search,
            'candidates' => $candidates,
        ]);
    }
}
