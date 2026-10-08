<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** admin/jobs.php (khusus HR) */
class JobController extends Controller
{
    public function index(Request $request)
    {
        $action = $request->query('action', 'list');
        $editId = (int) $request->query('id', 0);

        // Handle Save (Create / Update)
        if ($request->isMethod('post') && $request->request->has('action_save_job')) {
            $idJob = (int) $request->post('id_job', 0);
            $idDivision = (int) $request->post('id_division', 0);
            $namaJob = trim($request->post('nama_job', ''));
            $jobType = trim($request->post('job_type', 'Kerja'));
            // Durasi hanya untuk Magang dan PKL
            $durasi = in_array($jobType, ['Magang', 'PKL']) && ! empty($request->post('durasi_bulan')) ? (int) $request->post('durasi_bulan') : null;
            $deskripsi = trim($request->post('deskripsi', ''));
            $requirements = trim($request->post('requirements', ''));
            $responsibilities = trim($request->post('responsibilities', ''));
            $eduReq = trim($request->post('min_pendidikan', 'S1'));
            $expReq = trim($request->post('experience_requirement', '1-2 Tahun'));
            $salaryMin = ! empty($request->post('salary_min')) ? (float) $request->post('salary_min') : null;
            $salaryMax = ! empty($request->post('salary_max')) ? (float) $request->post('salary_max') : null;
            // Sistem kerja hanya boleh salah satu dari 3 pilihan (sama dengan ENUM di database)
            $sistemKerja = in_array($request->post('sistem_kerja', ''), ['Onsite', 'Hybrid', 'Remote']) ? $request->post('sistem_kerja') : 'Onsite';
            $deadline = trim($request->post('deadline', date('Y-m-d', strtotime('+30 days'))));
            $status = trim($request->post('status', 'Open'));
            $selectedSkills = $request->post('skills', []); // Array of skill IDs

            // PIC: kepala HR boleh memilih PIC; HR biasa otomatis menjadi PIC lowongan yang ia buat
            $myNik = currentUser()['nik'];
            $picNik = isKepalaHr() ? (trim($request->post('pic_nik', '')) ?: null) : $myNik;

            if ($idJob > 0 && ! canProcessJob($idJob)) {
                setFlash('danger', 'Hanya PIC lowongan ini atau kepala HR yang boleh mengubahnya.');

                return redirect()->route('admin.jobs');
            }

            if ($idDivision > 0 && ! empty($namaJob)) {
                if ($idJob > 0) {
                    DB::update('
                        UPDATE job
                        SET id_division = ?, nama_job = ?, job_type = ?, durasi_bulan = ?, deskripsi = ?, requirements = ?, responsibilities = ?,
                            min_pendidikan = ?, experience_requirement = ?, salary_min = ?, salary_max = ?,
                            sistem_kerja = ?, deadline = ?, status = ?
                        WHERE id_job = ?
                    ', [
                        $idDivision, $namaJob, $jobType, $durasi, $deskripsi, $requirements, $responsibilities,
                        $eduReq, $expReq, $salaryMin, $salaryMax, $sistemKerja, $deadline, $status, $idJob,
                    ]);

                    // Hanya kepala HR yang boleh mengganti PIC
                    if (isKepalaHr()) {
                        DB::update('UPDATE job SET pic_nik = ? WHERE id_job = ?', [$picNik, $idJob]);
                    }

                    // Sync skills in junction table: job_skill
                    DB::delete('DELETE FROM job_skill WHERE id_job = ?', [$idJob]);
                    foreach ($selectedSkills as $sId) {
                        DB::insert('INSERT INTO job_skill (id_job, id_skill, is_required) VALUES (?, ?, 1)', [$idJob, (int) $sId]);
                    }

                    setFlash('success', 'Lowongan pekerjaan berhasil diperbarui.');
                } else {
                    DB::insert('
                        INSERT INTO job (id_division, pic_nik, nama_job, job_type, durasi_bulan, deskripsi, requirements, responsibilities,
                                         min_pendidikan, experience_requirement, salary_min, salary_max, sistem_kerja, deadline, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ', [
                        $idDivision, $picNik, $namaJob, $jobType, $durasi, $deskripsi, $requirements, $responsibilities,
                        $eduReq, $expReq, $salaryMin, $salaryMax, $sistemKerja, $deadline, $status,
                    ]);
                    $newJobId = DB::getPdo()->lastInsertId();

                    // Insert skills into junction table
                    foreach ($selectedSkills as $sId) {
                        DB::insert('INSERT INTO job_skill (id_job, id_skill, is_required) VALUES (?, ?, 1)', [$newJobId, (int) $sId]);
                    }

                    setFlash('success', 'Lowongan pekerjaan baru berhasil diterbitkan.');
                }

                return redirect()->route('admin.jobs');
            } else {
                setFlash('danger', 'Divisi dan judul pekerjaan wajib diisi.');
            }
        }

        // Handle Delete
        if ($request->query('delete') !== null) {
            $delId = (int) $request->query('delete');
            if (canProcessJob($delId)) {
                DB::delete('DELETE FROM job WHERE id_job = ?', [$delId]);
                setFlash('info', 'Lowongan pekerjaan berhasil dihapus.');
            } else {
                setFlash('danger', 'Hanya PIC lowongan ini atau kepala HR yang boleh menghapusnya.');
            }

            return redirect()->route('admin.jobs');
        }

        // Handle Toggle Status
        if ($request->query('toggle_status') !== null && $request->query('id') !== null) {
            $tId = (int) $request->query('id');
            $newSt = $request->query('toggle_status') === 'Open' ? 'Closed' : 'Open';
            if (canProcessJob($tId)) {
                DB::update('UPDATE job SET status = ? WHERE id_job = ?', [$newSt, $tId]);
                setFlash('success', "Status lowongan berhasil diubah menjadi {$newSt}.");
            } else {
                setFlash('danger', 'Hanya PIC lowongan ini atau kepala HR yang boleh mengubah statusnya.');
            }

            return redirect()->route('admin.jobs');
        }

        // Get lists
        $divisions = DB::select('
            SELECT id_division, nama_divisi
            FROM division
            WHERE id_company = 1
            ORDER BY nama_divisi ASC
        ');

        $allSkills = DB::select('SELECT * FROM skill ORDER BY nama_skill ASC');

        // Daftar akun HR untuk pilihan PIC (dipakai kepala HR)
        $hrList = DB::select("
            SELECT u.nik, u.nama, s.jabatan
            FROM user_all u
            JOIN staff s ON s.nik = u.nik
            WHERE u.role = 'hr'
            ORDER BY u.nama ASC
        ");

        $myNik = currentUser()['nik'];
        $onlyMine = $request->query('mine', '') === '1';

        // If editing, fetch job and assigned skill IDs
        $jobEdit = null;
        $jobSkillIds = [];
        if ($editId > 0 && ($action === 'edit' || $action === 'create')) {
            $jobEdit = DB::selectOne('SELECT * FROM job WHERE id_job = ?', [$editId]);
            $jobSkillIds = DB::table('job_skill')->where('id_job', $editId)->pluck('id_skill')->all();
        }

        // Query all jobs for list view
        $sqlJobs = '
            SELECT j.*, d.nama_divisi, c.nama_company, pic.nama AS nama_pic,
                   (SELECT COUNT(*) FROM application a WHERE a.id_job = j.id_job) as total_applicants,
                   (SELECT COUNT(*) FROM job_skill js WHERE js.id_job = j.id_job) as total_skills
            FROM job j
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            LEFT JOIN user_all pic ON pic.nik = j.pic_nik
        ';
        $paramsJobs = [];
        if ($onlyMine) {
            $sqlJobs .= ' WHERE j.pic_nik = ?';
            $paramsJobs[] = $myNik;
        }
        $sqlJobs .= ' ORDER BY j.created_at DESC';
        $jobs = DB::select($sqlJobs, $paramsJobs);

        return view('admin.jobs', [
            'pageTitle' => 'Manajemen Lowongan Pekerjaan - SIREKA Admin',
            'activeSidebar' => 'jobs',
            'action' => $action,
            'editId' => $editId,
            'divisions' => $divisions,
            'allSkills' => $allSkills,
            'hrList' => $hrList,
            'myNik' => $myNik,
            'onlyMine' => $onlyMine,
            'jobEdit' => $jobEdit,
            'jobSkillIds' => $jobSkillIds,
            'jobs' => $jobs,
        ]);
    }
}
