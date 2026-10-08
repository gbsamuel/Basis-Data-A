<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/** user/interview.php */
class InterviewController extends Controller
{
    public function index()
    {
        $user = currentUser();
        $nik = $user['nik'];

        $interviews = DB::select("
            SELECT i.*, j.nama_job, j.job_type, c.nama_company, c.alamat as alamat_company, d.nama_divisi,
                   COALESCE(itw.nama, 'HR (akun sudah dihapus)') as nama_interviewer, itws.jabatan as jabatan_interviewer, itw.email as email_interviewer
            FROM interview i
            JOIN application a ON i.id_application = a.id_application
            JOIN job j ON a.id_job = j.id_job
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            LEFT JOIN user_all itw ON itw.nik = i.interviewer_nik   -- pewawancara = akun HR
            LEFT JOIN staff itws ON itws.nik = i.interviewer_nik
            WHERE a.nik = ?
            ORDER BY i.tanggal DESC, i.waktu DESC
        ", [$nik]);

        return view('user.interview', [
            'pageTitle' => 'Jadwal Interview Saya - SIREKA',
            'activeSidebar' => 'interview',
            'interviews' => $interviews,
        ]);
    }
}
