<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** user/loa.php (pelamar melihat LoA miliknya, HR boleh mencetak LoA semua kandidat) */
class LoaController extends Controller
{
    public function index(Request $request)
    {
        $user = currentUser();
        $nik = $user['nik'];

        $appId = (int) $request->query('app_id', 0);
        $isHr = hasRole('hr');

        // Pelamar menjawab offering: terima atau tolak LoA (hanya selama statusnya masih Issued)
        if ($request->isMethod('post') && ! $isHr && $request->request->has('action_respond_loa')) {
            $jawaban = $request->post('jawaban', '');
            $idLoa = (int) $request->post('id_loa', 0);
            if (in_array($jawaban, ['Accepted', 'Declined'])) {
                DB::update("
                    UPDATE loa l
                    JOIN application a ON a.id_application = l.id_application
                    SET l.status = ?, l.responded_at = NOW()
                    WHERE l.id_loa = ? AND a.nik = ? AND l.status = 'Issued'
                ", [$jawaban, $idLoa, $nik]);
                setFlash('success', $jawaban === 'Accepted' ? 'Terima kasih, Anda telah menerima offering ini.' : 'Jawaban Anda (menolak offering) sudah tercatat.');
            }

            return redirect(route('user.loa').($appId ? '?app_id='.$appId : ''));
        }

        // Fetch LOA
        if ($appId > 0) {
            // Pelamar hanya bisa membuka LoA miliknya sendiri, HR bisa membuka semua LoA
            $loa = DB::selectOne('
                SELECT l.*, a.applied_at, a.current_status,
                       j.nama_job, j.job_type, j.sistem_kerja,
                       c.nama_company, c.alamat as alamat_company, c.email_corporate, c.no_telepon as telp_company, c.industri,
                       u.nama as nama_kandidat, u.nik, u.email as email_kandidat, u.no_telepon as telp_kandidat, p.alamat as alamat_kandidat
                FROM loa l
                JOIN application a ON l.id_application = a.id_application
                JOIN job j ON a.id_job = j.id_job
                JOIN division d ON j.id_division = d.id_division
                JOIN company c ON d.id_company = c.id_company
                JOIN user_all u ON a.nik = u.nik
                JOIN pelamar p ON p.nik = u.nik
                WHERE l.id_application = ? AND (a.nik = ? OR ? = 1)
            ', [$appId, $nik, $isHr ? 1 : 0]);
        } else {
            // Find latest LOA for this user
            $loa = DB::selectOne('
                SELECT l.*, a.applied_at, a.current_status,
                       j.nama_job, j.job_type, j.sistem_kerja,
                       c.nama_company, c.alamat as alamat_company, c.email_corporate, c.no_telepon as telp_company, c.industri,
                       u.nama as nama_kandidat, u.nik, u.email as email_kandidat, u.no_telepon as telp_kandidat, p.alamat as alamat_kandidat
                FROM loa l
                JOIN application a ON l.id_application = a.id_application
                JOIN job j ON a.id_job = j.id_job
                JOIN division d ON j.id_division = d.id_division
                JOIN company c ON d.id_company = c.id_company
                JOIN user_all u ON a.nik = u.nik
                JOIN pelamar p ON p.nik = u.nik
                WHERE a.nik = ?
                ORDER BY l.issue_date DESC
                LIMIT 1
            ', [$nik]);
        }

        return view('user.loa', [
            'pageTitle' => 'Letter of Acceptance (LoA) - SIREKA',
            'isHr' => $isHr,
            'loa' => $loa,
        ]);
    }
}
