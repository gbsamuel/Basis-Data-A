<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/** admin/loa_manage.php (khusus HR) */
class LoaController extends Controller
{
    public function index()
    {
        // Fetch all LoAs
        $loas = DB::select('
            SELECT l.*, a.id_application, u.nama as nama_kandidat, u.nik, u.email,
                   c.nama_company
            FROM loa l
            JOIN application a ON l.id_application = a.id_application
            JOIN user_all u ON a.nik = u.nik
            JOIN job j ON a.id_job = j.id_job
            JOIN division d ON j.id_division = d.id_division
            JOIN company c ON d.id_company = c.id_company
            ORDER BY l.issue_date DESC
        ');

        return view('admin.loa_manage', [
            'pageTitle' => 'Manajemen Letter of Acceptance (LoA) - SIREKA Admin',
            'activeSidebar' => 'loa',
            'loas' => $loas,
        ]);
    }
}
