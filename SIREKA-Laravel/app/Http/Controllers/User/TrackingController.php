<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** user/tracking.php */
class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $user = currentUser();
        $nik = $user['nik'];

        $appId = (int) $request->query('id', 0);

        // Fetch application and verify ownership
        $app = DB::selectOne('
            SELECT a.*, j.nama_job, j.job_type, j.sistem_kerja, j.salary_min, j.salary_max,
                   c.nama_company, c.alamat as alamat_company, d.nama_divisi
            FROM application a
            JOIN job j ON a.id_job = j.id_job
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            WHERE a.id_application = ? AND a.nik = ?
        ', [$appId, $nik]);

        if (! $app) {
            setFlash('danger', 'Data lamaran tidak ditemukan atau Anda tidak memiliki akses.');

            return redirect()->route('user.applications');
        }

        // Fetch stage history from database
        $history = DB::select('
            SELECT h.*, u.nama as nama_petugas, u.role as role_petugas
            FROM candidate_stage_history h
            LEFT JOIN user_all u ON h.changed_by = u.nik
            WHERE h.id_application = ?
            ORDER BY h.changed_at ASC, h.id_history ASC
        ', [$appId]);

        // Fetch scheduled interview if any
        $interview = DB::selectOne("
            SELECT i.*, COALESCE(itw.nama, 'HR (akun sudah dihapus)') as nama_interviewer, itw.email as email_interviewer, itw.no_telepon as telp_interviewer, itws.jabatan as jabatan_interviewer
            FROM interview i
            LEFT JOIN user_all itw ON itw.nik = i.interviewer_nik   -- pewawancara = akun HR
            LEFT JOIN staff itws ON itws.nik = i.interviewer_nik
            WHERE i.id_application = ?
            ORDER BY i.created_at DESC
            LIMIT 1
        ", [$appId]);

        // Fetch LOA if accepted
        $loa = DB::selectOne('SELECT * FROM loa WHERE id_application = ?', [$appId]);

        // Define standard recruitment stages order
        $stages = [
            'Applied' => ['label' => 'Application Submitted', 'icon' => 'bi-file-earmark-check'],
            'HR Review' => ['label' => 'HR Review', 'icon' => 'bi-person-check'],
            'Document Screening' => ['label' => 'Document Screening', 'icon' => 'bi-files'],
            'Interview Scheduling' => ['label' => 'Interview Scheduling', 'icon' => 'bi-calendar-event'],
            'Interview' => ['label' => 'Interview Session', 'icon' => 'bi-camera-video'],
            'Final Decision' => ['label' => 'Final Decision', 'icon' => 'bi-check2-all'],
        ];

        $currentStatus = $app['current_status'];

        // Determine history maps
        $historyByStage = [];
        foreach ($history as $h) {
            $historyByStage[$h['stage']] = $h;
        }

        return view('user.tracking', [
            'pageTitle' => 'Tracking Lamaran: '.htmlspecialchars($app['nama_job']).' - SIREKA',
            'activeSidebar' => 'applications',
            'app' => $app,
            'history' => $history,
            'interview' => $interview,
            'loa' => $loa,
            'stages' => $stages,
            'currentStatus' => $currentStatus,
            'historyByStage' => $historyByStage,
        ]);
    }
}
