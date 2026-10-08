<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** admin/interviews.php (khusus HR) */
class InterviewController extends Controller
{
    public function index(Request $request)
    {
        // Update interview status
        if ($request->query('set_status') !== null && $request->query('id') !== null) {
            $itwId = (int) $request->query('id');
            $st = trim($request->query('set_status'));
            // Cari lowongan dari interview ini untuk mengecek hak PIC
            $jobId = (int) DB::scalar('
                SELECT a.id_job FROM interview i JOIN application a ON a.id_application = i.id_application
                WHERE i.id_interview = ?
            ', [$itwId]);

            if (! canProcessJob($jobId)) {
                setFlash('danger', 'Hanya PIC lowongan atau kepala HR yang boleh mengubah status interview ini.');
            } elseif (in_array($st, ['Scheduled', 'Completed', 'Cancelled'])) {
                DB::update('UPDATE interview SET status = ? WHERE id_interview = ?', [$st, $itwId]);
                setFlash('success', "Status sesi interview berhasil diperbarui menjadi {$st}.");
            }

            return redirect()->route('admin.interviews');
        }

        // Fetch interviews
        $interviews = DB::select("
            SELECT i.*, a.id_application, u.nama as nama_kandidat, u.email as email_kandidat, u.no_telepon as telp_kandidat,
                   j.nama_job, j.pic_nik, c.nama_company, COALESCE(itw.nama, 'HR (akun sudah dihapus)') as nama_interviewer
            FROM interview i
            JOIN application a ON i.id_application = a.id_application
            JOIN user_all u ON a.nik = u.nik
            JOIN job j ON a.id_job = j.id_job
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            LEFT JOIN user_all itw ON itw.nik = i.interviewer_nik   -- pewawancara = akun HR
            LEFT JOIN staff itws ON itws.nik = i.interviewer_nik
            ORDER BY i.tanggal DESC, i.waktu DESC
        ");
        $myNik = currentUser()['nik'];

        return view('admin.interviews', [
            'pageTitle' => 'Manajemen Jadwal Wawancara - SIREKA Admin',
            'activeSidebar' => 'interviews',
            'interviews' => $interviews,
            'myNik' => $myNik,
        ]);
    }
}
