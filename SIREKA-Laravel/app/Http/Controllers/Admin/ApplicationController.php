<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** admin/applications.php dan admin/application_detail.php (khusus HR) */
class ApplicationController extends Controller
{
    /** admin/applications.php */
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));
        $jobFilter = ! empty($request->query('job_id')) ? (int) $request->query('job_id') : null;
        $statusFilter = trim($request->query('status', ''));
        $onlyMine = $request->query('mine', '') === '1';   // hanya lamaran untuk lowongan yang saya pegang (PIC)
        $myNik = currentUser()['nik'];

        $sql = "
            SELECT a.*, u.nama as nama_kandidat, u.nik, u.email as email_kandidat, u.no_telepon, CONCAT(p.jenjang_pendidikan, ' ', p.jurusan) AS pendidikan_terakhir,
                   j.nama_job, j.job_type, j.pic_nik, c.nama_company, d.nama_divisi,
                   (SELECT COUNT(*) FROM interview i WHERE i.id_application = a.id_application AND i.status = 'Scheduled') as has_interview
            FROM application a
            JOIN user_all u ON a.nik = u.nik
            JOIN pelamar p ON p.nik = u.nik
            JOIN job j ON a.id_job = j.id_job
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            WHERE 1=1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (u.nama LIKE ? OR u.nik LIKE ? OR u.email LIKE ? OR j.nama_job LIKE ?)';
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($jobFilter) {
            $sql .= ' AND a.id_job = ?';
            $params[] = $jobFilter;
        }

        if ($statusFilter !== '') {
            $sql .= ' AND a.current_status = ?';
            $params[] = $statusFilter;
        }

        if ($onlyMine) {
            $sql .= ' AND j.pic_nik = ?';
            $params[] = $myNik;
        }

        $sql .= ' ORDER BY a.applied_at DESC';

        $applications = DB::select($sql, $params);

        // Jobs list for filter
        $jobsList = DB::select('SELECT id_job, nama_job FROM job ORDER BY nama_job ASC');

        return view('admin.applications', [
            'pageTitle' => 'Manajemen Lamaran Masuk - SIREKA Admin',
            'activeSidebar' => 'applications',
            'search' => $search,
            'jobFilter' => $jobFilter,
            'statusFilter' => $statusFilter,
            'onlyMine' => $onlyMine,
            'myNik' => $myNik,
            'applications' => $applications,
            'jobsList' => $jobsList,
        ]);
    }

    /** admin/application_detail.php */
    public function detail(Request $request)
    {
        $adminUser = currentUser();
        $adminNik = $adminUser['nik'];

        $appId = (int) $request->query('id', 0);
        $detailUrl = route('admin.application_detail').'?id='.$appId;

        // Fetch application dossier
        $app = DB::selectOne("
            SELECT a.*,
                   u.nama as nama_kandidat, u.nik, u.email as email_kandidat, u.no_telepon, p.tanggal_lahir,
                   CONCAT(p.jenjang_pendidikan, ' ', p.jurusan) AS pendidikan_terakhir, p.institusi, p.status_pendidikan, p.tahun_lulus, p.alamat as alamat_kandidat,
                   j.nama_job, j.job_type, j.sistem_kerja, j.salary_min, j.salary_max, j.id_job, j.id_division, j.pic_nik,
                   (SELECT nama FROM user_all WHERE nik = j.pic_nik) AS nama_pic,
                   c.id_company, c.nama_company, c.alamat as alamat_company, c.email_corporate,
                   d.nama_divisi,
                   (SELECT id_loa FROM loa l WHERE l.id_application = a.id_application) as loa_id
            FROM application a
            JOIN user_all u ON a.nik = u.nik
            JOIN pelamar p ON p.nik = u.nik
            JOIN job j ON a.id_job = j.id_job
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            WHERE a.id_application = ?
        ", [$appId]);

        if (! $app) {
            setFlash('danger', 'Data berkas lamaran tidak ditemukan.');

            return redirect()->route('admin.applications');
        }

        $isPost = $request->isMethod('post');

        // Hanya PIC lowongan ini atau kepala HR yang boleh memproses lamaran.
        // HR lain tetap bisa melihat, tapi semua form di bawah ditolak.
        $canProcess = canProcessJob($app['id_job']);
        if ($isPost && ! $canProcess) {
            setFlash('danger', 'Lowongan ini dipegang oleh '.($app['nama_pic'] ?? 'HR lain').'. Hanya PIC atau kepala HR yang boleh memproses lamaran ini.');

            return redirect($detailUrl);
        }

        // Handle Update Recruitment Status
        if ($isPost && $request->request->has('action_update_status')) {
            $newStatus = trim($request->post('new_status', ''));
            $notes = trim($request->post('notes', ''));

            $allowedStatuses = [
                'Applied', 'HR Review', 'Document Screening',
                'Interview Scheduling', 'Interview', 'Final Decision',
                'Accepted', 'Rejected',
            ];

            if (in_array($newStatus, $allowedStatuses)) {
                // 1. Update status in APPLICATION table
                DB::update('UPDATE application SET current_status = ?, updated_at = NOW() WHERE id_application = ?', [$newStatus, $appId]);

                // 2. Record stage history in relational table CANDIDATE_STAGE_HISTORY
                $addToPool = $newStatus === 'Rejected' && $request->request->has('add_talent_pool');
                // Catatan riwayat terlihat oleh pelamar, jadi info talent pool (data internal HR) tidak ditulis di sini
                recordStageHistory($appId, $newStatus, 'Completed', $notes, $adminNik);

                // 3. Jika ditolak dan dicentang "Masukkan ke Talent Pool": simpan / perbarui baris talent_pool.
                //    Satu pelamar hanya punya satu baris (nik UNIQUE), jadi jika sudah ada, barisnya diperbarui.
                if ($addToPool) {
                    $reason = trim($request->post('talent_reason', '')) ?: 'Kandidat potensial untuk lowongan berikutnya.';
                    DB::insert("
                        INSERT INTO talent_pool (nik, source_application, reason, status, added_by, added_at)
                        VALUES (?, ?, ?, 'Available', ?, NOW())
                        ON DUPLICATE KEY UPDATE
                            source_application = VALUES(source_application),
                            reason = VALUES(reason),
                            status = 'Available',
                            added_by = VALUES(added_by),
                            added_at = NOW()
                    ", [$app['nik'], $appId, $reason, $adminNik]);
                }

                // 4. Automated action if Accepted => generate LOA if not exists
                if ($newStatus === 'Accepted') {
                    if (! DB::selectOne('SELECT id_loa FROM loa WHERE id_application = ?', [$appId])) {
                        $compInit = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $app['nama_company']), 0, 3));
                        $loaNumber = "LOA/{$compInit}-HC/".date('Y').'/'.strtoupper(date('M')).'/'.str_pad($appId, 4, '0', STR_PAD_LEFT);
                        $joinDate = date('Y-m-d', strtotime('+14 days'));
                        $signer = getSystemSetting('loa_authorized_signer', 'Dr. Hendra Gunawan, S.E., M.M. (VP Human Capital)');

                        DB::insert("
                            INSERT INTO loa (id_application, loa_number, issue_date, join_date, position, division, status, authorized_by, notes)
                            VALUES (?, ?, CURDATE(), ?, ?, ?, 'Issued', ?, ?)
                        ", [
                            $appId, $loaNumber, $joinDate, $app['nama_job'], $app['nama_divisi'], $signer,
                            'Selamat bergabung di perusahaan kami. Mohon membawa dokumen kelengkapan asli pada hari pertama bergabung.',
                        ]);
                    }
                }

                setFlash('success', "Status lamaran berhasil diperbarui menjadi {$newStatus}. Riwayat tahapan tersimpan di database.");

                return redirect($detailUrl);
            }
        }

        // Handle Schedule Interview from this page
        if ($isPost && $request->request->has('action_schedule_interview')) {
            $interviewerNik = trim($request->post('interviewer_nik', ''));   // NIK akun HR yang mewawancarai
            $tanggal = trim($request->post('tanggal', ''));
            $waktu = trim($request->post('waktu', ''));
            $type = trim($request->post('type', 'Online'));
            $location = trim($request->post('location', ''));
            $meetingLink = trim($request->post('meeting_link', ''));
            $itwNotes = trim($request->post('notes', ''));

            if (! empty($interviewerNik) && ! empty($tanggal) && ! empty($waktu)) {
                // Insert interview
                DB::insert("
                    INSERT INTO interview (id_application, interviewer_nik, tanggal, waktu, type, location, meeting_link, notes, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Scheduled', NOW())
                ", [$appId, $interviewerNik, $tanggal, $waktu, $type, $location, $meetingLink, $itwNotes]);

                // Auto update application status to 'Interview'
                DB::update("UPDATE application SET current_status = 'Interview', updated_at = NOW() WHERE id_application = ?", [$appId]);
                recordStageHistory($appId, 'Interview', 'Current', "Jadwal wawancara ditetapkan pada {$tanggal} pukul {$waktu}. {$itwNotes}", $adminNik);

                setFlash('success', 'Sesi interview berhasil dijadwalkan dan status lamaran otomatis disinkronkan ke Interview.');

                return redirect($detailUrl);
            } else {
                setFlash('danger', 'Pewawancara, tanggal, dan waktu wajib diisi.');
            }
        }

        // Handle hasil wawancara (nilai 1-100 dan rekomendasi pewawancara)
        if ($isPost && $request->request->has('action_interview_result')) {
            $idInterview = (int) $request->post('id_interview', 0);
            $nilai = (int) $request->post('nilai', 0);
            $rekomendasi = trim($request->post('rekomendasi', ''));

            if ($nilai >= 1 && $nilai <= 100 && in_array($rekomendasi, ['Lanjut', 'Tidak Lanjut', 'Talent Pool'])) {
                DB::update("
                    UPDATE interview SET nilai = ?, rekomendasi = ?, status = 'Completed'
                    WHERE id_interview = ? AND id_application = ?
                ", [$nilai, $rekomendasi, $idInterview, $appId]);
                setFlash('success', 'Hasil wawancara berhasil disimpan.');
            } else {
                setFlash('danger', 'Nilai harus 1-100 dan rekomendasi wajib dipilih.');
            }

            return redirect($detailUrl);
        }

        // Skills match calculation details
        $skillsDetail = getJobSkillsDetail($app['nik'], $app['id_job']);

        // Fetch history
        $histories = DB::select('
            SELECT h.*, u.nama as nama_petugas
            FROM candidate_stage_history h
            LEFT JOIN user_all u ON h.changed_by = u.nik
            WHERE h.id_application = ?
            ORDER BY h.changed_at DESC, h.id_history DESC
        ', [$appId]);

        // Fetch scheduled interview
        $existingInterview = DB::selectOne("
            SELECT i.*, COALESCE(itw.nama, 'HR (akun sudah dihapus)') as nama_interviewer
            FROM interview i
            LEFT JOIN user_all itw ON itw.nik = i.interviewer_nik   -- pewawancara = akun HR
            LEFT JOIN staff itws ON itws.nik = i.interviewer_nik
            WHERE i.id_application = ?
            ORDER BY i.created_at DESC
            LIMIT 1
        ", [$appId]);

        // Daftar pewawancara = semua akun HR (data diambil dari user_all + staff lewat NIK)
        $interviewersList = DB::select("
            SELECT u.nik, u.nama, s.jabatan
            FROM user_all u
            JOIN staff s ON s.nik = u.nik
            WHERE u.role = 'hr'
            ORDER BY u.nama ASC
        ");

        // Apakah pelamar ini sudah ada di talent pool?
        $poolStatus = DB::scalar('SELECT status FROM talent_pool WHERE nik = ?', [$app['nik']]);

        return view('admin.application_detail', [
            'pageTitle' => 'Dossier Seleksi: '.htmlspecialchars($app['nama_kandidat']).' - SIREKA Admin',
            'activeSidebar' => 'applications',
            'appId' => $appId,
            'app' => $app,
            'canProcess' => $canProcess,
            'skillsDetail' => $skillsDetail,
            'histories' => $histories,
            'existingInterview' => $existingInterview,
            'interviewersList' => $interviewersList,
            'poolStatus' => $poolStatus,
        ]);
    }
}
