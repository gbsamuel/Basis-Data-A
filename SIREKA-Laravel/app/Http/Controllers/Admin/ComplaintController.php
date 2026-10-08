<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** admin/complaints.php (khusus admin: keluhan dan feedback pelamar digabung di sini) */
class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        // Handle response submit
        if ($request->isMethod('post') && $request->request->has('action_respond_complaint')) {
            $idComplaint = (int) $request->post('id_complaint', 0);
            $response = trim($request->post('admin_response', ''));
            $status = trim($request->post('status', 'Resolved'));

            if ($idComplaint > 0 && ! empty($response)) {
                DB::update('
                    UPDATE complaint
                    SET admin_response = ?, status = ?, resolved_at = NOW(), responded_by = ?
                    WHERE id_complaint = ?
                ', [$response, $status, currentUser()['nik'], $idComplaint]);   // responded_by = admin yang membalas
                setFlash('success', 'Tanggapan resmi berhasil dikirimkan ke pelamar dan status tiket diperbarui.');
            } else {
                setFlash('danger', 'Tanggapan wajib diisi.');
            }

            return redirect()->route('admin.complaints');
        }

        $complaints = DB::select('
            SELECT c.*, u.nama as nama_kandidat, u.email, u.no_telepon
            FROM complaint c
            JOIN user_all u ON c.nik = u.nik
            ORDER BY c.created_at DESC
        ');

        // Data feedback (ulasan bintang) untuk tab kedua
        $avgRating = DB::scalar('SELECT AVG(rating) FROM feedback');
        $totalFeedback = DB::scalar('SELECT COUNT(*) FROM feedback');
        $feedbacks = DB::select('
            SELECT f.*, u.nama as nama_kandidat, u.email
            FROM feedback f
            JOIN user_all u ON f.nik = u.nik
            ORDER BY f.created_at DESC
        ');

        // Tab yang dibuka: 'keluhan' (default) atau 'feedback'
        $activeTab = $request->query('tab', '') === 'feedback' ? 'feedback' : 'keluhan';

        return view('admin.complaints', [
            'pageTitle' => 'Keluhan & Feedback - SIREKA Admin',
            'activeSidebar' => 'complaints',
            'complaints' => $complaints,
            'avgRating' => $avgRating,
            'totalFeedback' => $totalFeedback,
            'feedbacks' => $feedbacks,
            'activeTab' => $activeTab,
        ]);
    }
}
