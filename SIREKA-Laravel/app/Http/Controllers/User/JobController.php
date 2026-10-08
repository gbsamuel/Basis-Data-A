<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** user/jobs.php */
class JobController extends Controller
{
    public function index(Request $request)
    {
        $user = currentUser();
        $nik = $user['nik'];

        $search = trim($request->query('search', ''));
        $jobType = trim($request->query('type', ''));
        $companyId = ! empty($request->query('company')) ? (int) $request->query('company') : null;
        $divisionId = ! empty($request->query('division')) ? (int) $request->query('division') : null;

        // Query jobs
        $sql = '
            SELECT j.*, d.nama_divisi, c.nama_company, c.id_company,
                   (SELECT id_application FROM application a WHERE a.id_job = j.id_job AND a.nik = ?) AS my_app_id
            FROM job j
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            WHERE 1=1
        ';
        $params = [$nik];

        if ($search !== '') {
            $sql .= ' AND (j.nama_job LIKE ? OR j.deskripsi LIKE ? OR j.sistem_kerja LIKE ?)';
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        if ($jobType !== '') {
            $sql .= ' AND j.job_type = ?';
            $params[] = $jobType;
        }
        if ($divisionId) {
            $sql .= ' AND d.id_division = ?';
            $params[] = $divisionId;
        }

        $sql .= ' ORDER BY j.created_at DESC';

        $jobs = DB::select($sql, $params);

        // Dropdowns
        $divisions = DB::select('SELECT id_division, nama_divisi FROM division WHERE id_company = 1 ORDER BY nama_divisi ASC');

        return view('user.jobs', [
            'pageTitle' => 'Cari Lowongan Pekerjaan - SIREKA',
            'activeSidebar' => 'jobs',
            'nik' => $nik,
            'search' => $search,
            'jobType' => $jobType,
            'companyId' => $companyId,
            'divisionId' => $divisionId,
            'jobs' => $jobs,
            'divisions' => $divisions,
        ]);
    }
}
