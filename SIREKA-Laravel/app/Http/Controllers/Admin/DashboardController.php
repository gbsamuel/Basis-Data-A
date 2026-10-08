<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/** admin/dashboard.php (dashboard rekrutmen khusus HR, admin langsung ke Pengaturan Sistem) */
class DashboardController extends Controller
{
    public function index()
    {
        $user = currentUser();

        // Aggregate metrics
        $totalJobs = DB::scalar('SELECT COUNT(*) FROM job');
        $totalCandidates = DB::scalar("SELECT COUNT(*) FROM user_all WHERE role = 'user'");
        $totalApplications = DB::scalar('SELECT COUNT(*) FROM application');
        $inProcess = DB::scalar("SELECT COUNT(*) FROM application WHERE current_status IN ('Applied', 'HR Review', 'Document Screening', 'Interview Scheduling')");
        $interviewsScheduled = DB::scalar("SELECT COUNT(*) FROM interview WHERE status = 'Scheduled'");
        $acceptedCount = DB::scalar("SELECT COUNT(*) FROM application WHERE current_status = 'Accepted'");

        // Chart 1: Applications by Status
        $statusData = DB::select('
            SELECT current_status, COUNT(*) as total
            FROM application
            GROUP BY current_status
        ');
        $statusLabels = [];
        $statusTotals = [];
        foreach ($statusData as $row) {
            $statusLabels[] = $row['current_status'];
            $statusTotals[] = (int) $row['total'];
        }

        // Chart 2: Applications per Division
        $divData = DB::select('
            SELECT d.nama_divisi, COUNT(a.id_application) as total
            FROM division d
            LEFT JOIN job j ON d.id_division = j.id_division
            LEFT JOIN application a ON j.id_job = a.id_job
            GROUP BY d.id_division
            HAVING total > 0
            ORDER BY total DESC
            LIMIT 6
        ');
        $divLabels = [];
        $divTotals = [];
        foreach ($divData as $row) {
            $divLabels[] = $row['nama_divisi'];
            $divTotals[] = (int) $row['total'];
        }

        // Recent applications
        $recentApps = DB::select('
            SELECT a.*, u.nama as nama_kandidat, u.email as email_kandidat, j.nama_job, d.nama_divisi
            FROM application a
            JOIN user_all u ON a.nik = u.nik
            JOIN job j ON a.id_job = j.id_job
            JOIN division d ON j.id_division = d.id_division
            WHERE d.id_company = 1
            ORDER BY a.applied_at DESC
            LIMIT 6
        ');

        return view('admin.dashboard', [
            'pageTitle' => 'Dashboard Admin & HR - SIREKA',
            'activeSidebar' => 'dashboard',
            'user' => $user,
            'totalJobs' => $totalJobs,
            'totalCandidates' => $totalCandidates,
            'totalApplications' => $totalApplications,
            'inProcess' => $inProcess,
            'interviewsScheduled' => $interviewsScheduled,
            'acceptedCount' => $acceptedCount,
            'statusLabels' => $statusLabels,
            'statusTotals' => $statusTotals,
            'divLabels' => $divLabels,
            'divTotals' => $divTotals,
            'recentApps' => $recentApps,
        ]);
    }
}
