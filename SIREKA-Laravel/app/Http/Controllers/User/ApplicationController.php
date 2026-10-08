<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** user/applications.php */
class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = currentUser();
        $nik = $user['nik'];

        $statusFilter = trim($request->query('status', ''));

        $sql = "
            SELECT a.*, j.nama_job, j.job_type, j.sistem_kerja, c.nama_company, d.nama_divisi,
                   (SELECT COUNT(*) FROM interview i WHERE i.id_application = a.id_application AND i.status = 'Scheduled') as has_interview,
                   (SELECT id_loa FROM loa l WHERE l.id_application = a.id_application) as loa_id
            FROM application a
            JOIN job j ON a.id_job = j.id_job
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            WHERE a.nik = ?
        ";
        $params = [$nik];

        if ($statusFilter !== '') {
            $sql .= ' AND a.current_status = ?';
            $params[] = $statusFilter;
        }

        $sql .= ' ORDER BY a.applied_at DESC';

        $applications = DB::select($sql, $params);

        return view('user.applications', [
            'pageTitle' => 'Daftar Lamaran Saya - SIREKA',
            'activeSidebar' => 'applications',
            'statusFilter' => $statusFilter,
            'applications' => $applications,
        ]);
    }
}
