<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** user/feedback.php */
class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $user = currentUser();
        $nik = $user['nik'];

        if ($request->isMethod('post')) {
            $rating = (int) $request->post('rating', 5);
            $message = trim($request->post('message', ''));

            if ($rating >= 1 && $rating <= 5 && ! empty($message)) {
                DB::insert('INSERT INTO feedback (nik, rating, message, created_at) VALUES (?, ?, ?, NOW())', [$nik, $rating, $message]);
                setFlash('success', 'Terima kasih atas ulasan dan penilaian berharga Anda untuk pengembangan SIREKA!');

                return redirect()->route('user.feedback');
            } else {
                setFlash('danger', 'Harap berikan rating bintang dan pesan ulasan.');
            }
        }

        // Get user past feedback
        $myFeedbacks = DB::select('SELECT * FROM feedback WHERE nik = ? ORDER BY created_at DESC', [$nik]);

        return view('user.feedback', [
            'pageTitle' => 'Penilaian & Feedback Pengalaman - SIREKA',
            'activeSidebar' => 'feedback',
            'myFeedbacks' => $myFeedbacks,
        ]);
    }
}
