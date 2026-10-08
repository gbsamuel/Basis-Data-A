<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/** admin/reports.php - Laporan & Statistik Rekrutmen */
class ReportController extends Controller
{
    public function index()
    {
        // Aggregate Queries for Basis Data Demonstration
        $totalCandidates = DB::scalar("SELECT COUNT(*) FROM user_all WHERE role = 'user'");
        $totalJobs = DB::scalar('SELECT COUNT(*) FROM job');
        $totalApps = DB::scalar('SELECT COUNT(*) FROM application');
        $avgMatch = DB::scalar('SELECT AVG(match_score) FROM application');
        $totalAccepted = DB::scalar("SELECT COUNT(*) FROM application WHERE current_status = 'Accepted'");

        // Breakdown by Job
        $reportJobs = DB::select("
            SELECT j.id_job, j.nama_job, j.job_type, c.nama_company, d.nama_divisi,
                   COUNT(a.id_application) as total_applied,
                   AVG(a.match_score) as avg_score,
                   SUM(CASE WHEN a.current_status = 'Accepted' THEN 1 ELSE 0 END) as accepted_count,
                   SUM(CASE WHEN a.current_status = 'Interview' THEN 1 ELSE 0 END) as interview_count
            FROM job j
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            LEFT JOIN application a ON j.id_job = a.id_job
            GROUP BY j.id_job
            ORDER BY total_applied DESC
        ");

        // Breakdown by Division
        $reportDivs = DB::select("
            SELECT d.id_division, d.nama_divisi, c.nama_company,
                   COUNT(DISTINCT j.id_job) as total_jobs,
                   COUNT(a.id_application) as total_applied,
                   SUM(CASE WHEN a.current_status = 'Accepted' THEN 1 ELSE 0 END) as total_accepted
            FROM division d
            JOIN company c ON d.id_company = c.id_company
            LEFT JOIN job j ON d.id_division = j.id_division
            LEFT JOIN application a ON j.id_job = a.id_job
            GROUP BY d.id_division
            ORDER BY total_applied DESC
        ");

        return view('admin.reports', [
            'pageTitle' => 'Laporan & Statistik Rekrutmen - SIREKA Admin',
            'activeSidebar' => 'reports',
            'totalCandidates' => $totalCandidates,
            'totalJobs' => $totalJobs,
            'totalApps' => $totalApps,
            'avgMatch' => $avgMatch,
            'totalAccepted' => $totalAccepted,
            'reportJobs' => $reportJobs,
            'reportDivs' => $reportDivs,
        ]);
    }
}
