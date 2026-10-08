<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/** user/dashboard.php */
class DashboardController extends Controller
{
    public function index()
    {
        $user = currentUser();
        $nik = $user['nik'];

        // Aggregate counters for candidate
        $counts = DB::selectOne("
            SELECT
                COUNT(*) AS total_applied,
                SUM(CASE WHEN current_status IN ('Applied', 'HR Review', 'Document Screening', 'Interview Scheduling') THEN 1 ELSE 0 END) AS in_process,
                SUM(CASE WHEN current_status = 'Interview' THEN 1 ELSE 0 END) AS interview_count,
                SUM(CASE WHEN current_status = 'Accepted' THEN 1 ELSE 0 END) AS accepted_count
            FROM application
            WHERE nik = ?
        ", [$nik]);

        // Get recent applications
        $recentApps = DB::select('
            SELECT a.*, j.nama_job, j.job_type, c.nama_company, d.nama_divisi
            FROM application a
            JOIN job j ON a.id_job = j.id_job
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            WHERE a.nik = ?
            ORDER BY a.applied_at DESC
            LIMIT 5
        ', [$nik]);

        // Get upcoming interviews
        $upcomingInterviews = DB::select("
            SELECT i.*, j.nama_job, c.nama_company, COALESCE(itw.nama, 'HR (akun sudah dihapus)') as nama_interviewer
            FROM interview i
            JOIN application a ON i.id_application = a.id_application
            JOIN job j ON a.id_job = j.id_job
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            LEFT JOIN user_all itw ON itw.nik = i.interviewer_nik   -- pewawancara = akun HR
            LEFT JOIN staff itws ON itws.nik = i.interviewer_nik
            WHERE a.nik = ? AND i.status = 'Scheduled' AND i.tanggal >= CURDATE()
            ORDER BY i.tanggal ASC, i.waktu ASC
            LIMIT 2
        ", [$nik]);

        // Get user skills count
        $userSkillsCount = DB::scalar('SELECT COUNT(*) FROM user_skill WHERE nik = ?', [$nik]);

        return view('user.dashboard', [
            'pageTitle' => 'Dashboard Pelamar - SIREKA',
            'activeSidebar' => 'dashboard',
            'user' => $user,
            'counts' => $counts,
            'recentApps' => $recentApps,
            'upcomingInterviews' => $upcomingInterviews,
            'userSkillsCount' => $userSkillsCount,
        ]);
    }
}
