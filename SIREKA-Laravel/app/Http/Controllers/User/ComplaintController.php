<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** user/complaint.php */
class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $user = currentUser();
        $nik = $user['nik'];

        // Handle submit complaint
        if ($request->isMethod('post')) {
            $subject = trim($request->post('subject', ''));
            $category = trim($request->post('category', 'Umum'));
            $description = trim($request->post('description', ''));

            if (! empty($subject) && ! empty($description)) {
                DB::insert("
                    INSERT INTO complaint (nik, subject, category, description, status, created_at)
                    VALUES (?, ?, ?, ?, 'Submitted', NOW())
                ", [$nik, $subject, $category, $description]);
                setFlash('success', 'Tiket keluhan Anda berhasil dikirimkan. Tim support akan segera meninjau.');

                return redirect()->route('user.complaint');
            } else {
                setFlash('danger', 'Subjek dan deskripsi keluhan wajib diisi.');
            }
        }

        // Get user complaints
        $complaints = DB::select('SELECT * FROM complaint WHERE nik = ? ORDER BY created_at DESC', [$nik]);

        return view('user.complaint', [
            'pageTitle' => 'Layanan Keluhan & Tiket Bantuan - SIREKA',
            'activeSidebar' => 'complaint',
            'complaints' => $complaints,
        ]);
    }
}
