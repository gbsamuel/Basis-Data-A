<?php

/*
|--------------------------------------------------------------------------
| SIREKA - Routes
|--------------------------------------------------------------------------
| Setiap file .php di versi native sekarang menjadi satu route.
| Alamatnya dibuat sama, hanya tanpa akhiran ".php":
|   /jobs.php                    -> /jobs
|   /user/tracking.php?id=3      -> /user/tracking?id=3
|   /admin/application_detail.php?id=1 -> /admin/application_detail?id=1
| Link lama yang masih memakai ".php" otomatis diarahkan ke alamat baru (lihat paling bawah).
|
| Halaman yang di versi native memproses form (POST) di file yang sama
| memakai Route::match(['get', 'post']) supaya alurnya tetap sama.
*/

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* ---------- Halaman publik ---------- */
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/jobs', [PublicController::class, 'jobs'])->name('jobs');
Route::get('/job_detail', [PublicController::class, 'jobDetail'])->name('job_detail');
Route::get('/helpdesk', [PublicController::class, 'helpdesk'])->name('helpdesk');

/* ---------- Autentikasi ---------- */
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::match(['get', 'post'], '/process_login', [AuthController::class, 'processLogin'])->name('process_login');
    Route::match(['get', 'post'], '/register', [AuthController::class, 'register'])->name('register');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

/* ---------- Pelamar (role: user) ---------- */
Route::prefix('user')->name('user.')->group(function () {
    Route::middleware('role:user')->group(function () {
        Route::get('/dashboard', [User\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/jobs', [User\JobController::class, 'index'])->name('jobs');
        Route::match(['get', 'post'], '/apply', [User\ApplyController::class, 'index'])->name('apply');
        Route::get('/applications', [User\ApplicationController::class, 'index'])->name('applications');
        Route::get('/tracking', [User\TrackingController::class, 'index'])->name('tracking');
        Route::get('/interview', [User\InterviewController::class, 'index'])->name('interview');
        Route::match(['get', 'post'], '/complaint', [User\ComplaintController::class, 'index'])->name('complaint');
        Route::match(['get', 'post'], '/feedback', [User\FeedbackController::class, 'index'])->name('feedback');
        Route::match(['get', 'post'], '/profile', [User\ProfileController::class, 'index'])->name('profile');
        // Talent pool adalah data internal HR, jadi tidak ditampilkan ke pelamar.
        Route::get('/talent_pool', fn () => redirect()->route('user.dashboard'))->name('talent_pool');
    });

    // Pelamar melihat LoA miliknya, HR boleh mencetak LoA semua kandidat
    Route::match(['get', 'post'], '/loa', [User\LoaController::class, 'index'])
        ->middleware('role:user,hr')->name('loa');
});

/* ---------- HR & Admin ---------- */
Route::prefix('admin')->name('admin.')->group(function () {
    // Khusus HR (proses rekrutmen)
    Route::middleware('role:hr')->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::match(['get', 'post'], '/jobs', [Admin\JobController::class, 'index'])->name('jobs');
        Route::get('/candidates', [Admin\CandidateController::class, 'index'])->name('candidates');
        Route::get('/applications', [Admin\ApplicationController::class, 'index'])->name('applications');
        Route::match(['get', 'post'], '/application_detail', [Admin\ApplicationController::class, 'detail'])->name('application_detail');
        Route::get('/interviews', [Admin\InterviewController::class, 'index'])->name('interviews');
        Route::get('/loa_manage', [Admin\LoaController::class, 'index'])->name('loa_manage');
        Route::get('/talent_pool', [Admin\TalentPoolController::class, 'index'])->name('talent_pool');
    });

    // Khusus admin (data perusahaan, sistem, dan akun)
    Route::middleware('role:admin')->group(function () {
        Route::match(['get', 'post'], '/settings', [Admin\SettingsController::class, 'index'])->name('settings');
        Route::match(['get', 'post'], '/users', [Admin\UserController::class, 'index'])->name('users');
        Route::match(['get', 'post'], '/divisions', [Admin\DivisionController::class, 'index'])->name('divisions');
        Route::match(['get', 'post'], '/complaints', [Admin\ComplaintController::class, 'index'])->name('complaints');
        // Profil Perusahaan sudah digabung ke halaman Pengaturan Sistem
        Route::get('/companies', fn () => redirect()->route('admin.settings'))->name('companies');
        // Halaman feedback sudah digabung ke halaman Keluhan & Feedback (tab "Ulasan & Feedback")
        Route::get('/feedback', fn () => redirect(route('admin.complaints').'?tab=feedback'))->name('feedback');
    });

    Route::match(['get', 'post'], '/profile', [Admin\ProfileController::class, 'index'])
        ->middleware('role:hr,admin')->name('profile');
    Route::get('/reports', [Admin\ReportController::class, 'index'])
        ->middleware('role:admin,interviewer')->name('reports');
});

/* ---------- Kompatibilitas link lama (*.php) ---------- */
// Contoh: /user/tracking.php?id=3 -> /user/tracking?id=3, /index.php -> /
Route::get('/{legacy}.php', function (Request $request, string $legacy) {
    $path = $legacy === 'index' ? '/' : '/'.$legacy;
    $query = $request->getQueryString();

    return redirect(url($path).($query ? '?'.$query : ''), 301);
})->where('legacy', '[A-Za-z0-9_/]+');
