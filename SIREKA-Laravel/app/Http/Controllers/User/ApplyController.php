<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** user/apply.php */
class ApplyController extends Controller
{
    public function index(Request $request)
    {
        $nik = currentUser()['nik'];
        $user = loadUserProfile($nik);   // akun + data pelamar (pendidikan, alamat, dll.)

        $jobId = (int) $request->query('job_id', 0);

        // Fetch job details (lowongan yang sudah lewat deadline tidak bisa dilamar)
        $job = DB::selectOne("
            SELECT j.*, d.nama_divisi, c.nama_company, c.id_company
            FROM job j
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            WHERE j.id_job = ? AND j.status = 'Open' AND j.deadline >= CURDATE()
        ", [$jobId]);

        if (! $job) {
            setFlash('danger', 'Lowongan tidak ditemukan, sudah ditutup, atau sudah melewati batas pendaftaran.');

            return redirect()->route('user.jobs');
        }

        // Check duplicate application
        $existingApp = DB::selectOne('SELECT id_application FROM application WHERE nik = ? AND id_job = ?', [$nik, $jobId]);

        if ($existingApp) {
            setFlash('info', 'Anda sudah pernah melamar untuk posisi ini.');

            return redirect(route('user.tracking').'?id='.$existingApp['id_application']);
        }

        // Calculate match score
        $matchScore = calculateMatchScore($nik, $jobId);
        $skillsDetail = getJobSkillsDetail($nik, $jobId);

        $errors = [];
        if ($request->isMethod('post')) {
            $coverLetter = trim($request->post('cover_letter', ''));
            $portfolioUrl = trim($request->post('portfolio_url', ''));
            $agreement = $request->request->has('agreement');

            if (! $agreement) {
                $errors[] = 'Anda harus menyetujui pernyataan kebenaran data sebelum mengirimkan lamaran.';
            }

            $file = $request->file('cv_file');
            if (! $file) {
                $errors[] = 'Dokumen CV wajib diunggah.';
            } else {
                $ext = strtolower($file->getClientOriginalExtension());
                $allowed = ['pdf', 'doc', 'docx'];

                if (! in_array($ext, $allowed)) {
                    $errors[] = 'Format CV hanya boleh berupa file PDF, DOC, atau DOCX.';
                }

                if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
                    $errors[] = 'Ukuran berkas CV maksimal 5 MB.';
                }
            }

            if (empty($errors)) {
                // Upload CV ke public/uploads/cv (alamat file tetap /uploads/cv/...)
                $uploadDir = public_path('uploads/cv');
                if (! is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = 'cv_'.$nik.'_'.time().'.'.$ext;

                try {
                    $file->move($uploadDir, $fileName);
                    $uploaded = true;
                } catch (\Throwable $e) {
                    $uploaded = false;
                }

                if ($uploaded) {
                    // Insert into APPLICATION
                    DB::insert("
                        INSERT INTO application (nik, id_job, cv_file, cover_letter, portfolio_url, match_score, applied_at, current_status)
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Applied')
                    ", [
                        $nik,
                        $jobId,
                        $fileName,
                        $coverLetter,
                        $portfolioUrl,
                        $matchScore,
                    ]);
                    $newAppId = DB::getPdo()->lastInsertId();

                    // Record initial history
                    recordStageHistory(
                        $newAppId,
                        'Applied',
                        'Completed',
                        'Berkas lamaran berhasil disubmit oleh kandidat ke sistem SIREKA.',
                        null
                    );

                    setFlash('success', 'Lamaran Anda berhasil dikirimkan! Pantau progres proses rekrutmen melalui timeline di bawah ini.');

                    return redirect(route('user.tracking').'?id='.$newAppId);
                } else {
                    $errors[] = 'Gagal menyimpan file CV ke server. Silakan coba kembali.';
                }
            }
        }

        return view('user.apply', [
            'pageTitle' => 'Formulir Lamaran Pekerjaan - SIREKA',
            'activeSidebar' => 'jobs',
            'user' => $user,
            'jobId' => $jobId,
            'job' => $job,
            'matchScore' => $matchScore,
            'skillsDetail' => $skillsDetail,
            'errors' => $errors,
        ]);
    }
}
