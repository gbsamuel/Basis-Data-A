<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Halaman publik: index.php, about.php, jobs.php, job_detail.php, helpdesk.php
 */
class PublicController extends Controller
{
    /** index.php */
    public function home()
    {
        $company = DB::selectOne('SELECT * FROM company WHERE id_company = 1 LIMIT 1');
        $companyName = $company['nama_company'] ?? 'PT Solusi Teknologi Nusantara';

        // Get featured open jobs
        $featuredJobs = DB::select("
            SELECT j.*, d.nama_divisi, c.nama_company,
                   (SELECT COUNT(*) FROM application a WHERE a.id_job = j.id_job) as total_applicants
            FROM job j
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            WHERE j.status = 'Open'
            ORDER BY j.created_at DESC
            LIMIT 6
        ");

        $user = currentUser();
        $userNik = $user['nik'] ?? null;

        return view('public.index', [
            'pageTitle' => 'SIREKA - Portal Karir '.$companyName,
            'activePage' => 'home',
            'company' => $company,
            'companyName' => $companyName,
            'featuredJobs' => $featuredJobs,
            'user' => $user,
            'userNik' => $userNik,
        ]);
    }

    /** about.php */
    public function about()
    {
        // Get company profile
        $company = DB::selectOne('SELECT * FROM company WHERE id_company = 1 LIMIT 1');

        // Semua divisi di perusahaan (bukan hanya yang sedang membuka lowongan)
        $divisions = DB::select('
            SELECT * FROM division
            WHERE id_company = 1
            ORDER BY nama_divisi ASC
        ');

        // Angka untuk section "SIREKA by the Numbers", diambil langsung dari database
        $totalOpenJobs = DB::scalar("SELECT COUNT(*) FROM job WHERE status = 'Open'");
        $totalDivisions = DB::scalar('SELECT COUNT(*) FROM division WHERE id_company = 1');
        $totalApplicants = DB::scalar("SELECT COUNT(*) FROM user_all WHERE role = 'user'");

        // Data tim dan galeri (placeholder, ubah di config/about.php)
        return view('public.about', [
            'pageTitle' => 'Tentang Kami - PT Solusi Teknologi Nusantara',
            'activePage' => 'about',
            'company' => $company,
            'divisions' => $divisions,
            'totalOpenJobs' => $totalOpenJobs,
            'totalDivisions' => $totalDivisions,
            'totalApplicants' => $totalApplicants,
            'teamMembers' => config('about.team_members'),
            'galleryItems' => config('about.gallery_items'),
            'aboutCollage' => config('about.about_collage'),
            'aboutPhotos' => config('about.about_photos'),
        ]);
    }

    /** jobs.php */
    public function jobs(Request $request)
    {
        $company = DB::selectOne('SELECT * FROM company WHERE id_company = 1 LIMIT 1');
        $companyName = $company['nama_company'] ?? 'PT Solusi Teknologi Nusantara';

        $user = currentUser();
        $userNik = $user['nik'] ?? null;

        // Filter params
        $search = trim($request->query('search', ''));
        $jobType = trim($request->query('type', ''));
        $divisionId = ! empty($request->query('division')) ? (int) $request->query('division') : null;

        // Build query
        $sql = '
            SELECT j.*, d.nama_divisi, c.nama_company, c.id_company,
                   (SELECT COUNT(*) FROM application a WHERE a.id_job = j.id_job) AS total_applicants
            FROM job j
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            WHERE 1=1
        ';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (j.nama_job LIKE ? OR j.deskripsi LIKE ? OR j.sistem_kerja LIKE ?)';
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($jobType !== '') {
            $sql .= ' AND j.job_type = ?';
            $params[] = $jobType;
        }

        if ($divisionId) {
            $sql .= ' AND d.id_division = ?';
            $params[] = $divisionId;
        }

        $sql .= ' ORDER BY j.created_at DESC';

        $jobs = DB::select($sql, $params);

        // Get divisions list for filter
        $divisions = DB::select('SELECT id_division, nama_divisi FROM division WHERE id_company = 1 ORDER BY nama_divisi ASC');

        return view('public.jobs', [
            'pageTitle' => 'Eksplorasi Lowongan IT - '.$companyName,
            'activePage' => 'jobs',
            'companyName' => $companyName,
            'userNik' => $userNik,
            'search' => $search,
            'jobType' => $jobType,
            'divisionId' => $divisionId,
            'jobs' => $jobs,
            'divisions' => $divisions,
        ]);
    }

    /** job_detail.php */
    public function jobDetail(Request $request)
    {
        $jobId = (int) $request->query('id', 0);

        $job = DB::selectOne('
            SELECT j.*, d.nama_divisi, c.nama_company, c.alamat as alamat_company, c.industri, c.email_corporate, c.no_telepon as telp_company
            FROM job j
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            WHERE j.id_job = ?
        ', [$jobId]);

        // Lowongan dianggap ditutup jika statusnya bukan Open atau sudah lewat deadline
        $isClosed = $job && ($job['status'] !== 'Open' || $job['deadline'] < date('Y-m-d'));

        if (! $job) {
            setFlash('danger', 'Lowongan pekerjaan tidak ditemukan.');

            return redirect()->route('jobs');
        }

        $user = currentUser();
        $userNik = $user['nik'] ?? null;
        $alreadyApplied = false;
        $applicationId = null;

        if ($userNik) {
            $app = DB::selectOne('SELECT id_application, current_status FROM application WHERE nik = ? AND id_job = ?', [$userNik, $jobId]);
            if ($app) {
                $alreadyApplied = true;
                $applicationId = $app['id_application'];
            }
        }

        // Skills calculation
        $skillsDetail = getJobSkillsDetail($userNik, $jobId);

        return view('public.job_detail', [
            'pageTitle' => htmlspecialchars($job['nama_job']).' - '.htmlspecialchars($job['nama_company']),
            'activePage' => 'jobs',
            'jobId' => $jobId,
            'job' => $job,
            'isClosed' => $isClosed,
            'user' => $user,
            'userNik' => $userNik,
            'alreadyApplied' => $alreadyApplied,
            'applicationId' => $applicationId,
            'skillsDetail' => $skillsDetail,
        ]);
    }

    /** helpdesk.php */
    public function helpdesk()
    {
        return view('public.helpdesk', [
            'pageTitle' => 'Helpdesk Rekrutmen - SIREKA',
            'activePage' => 'helpdesk',
            'waNumber' => getSystemSetting('helpdesk_whatsapp', '6281234567890'),
            'helpdeskEmail' => getSystemSetting('helpdesk_email', 'recruitment.support@sireka.id'),
        ]);
    }
}
