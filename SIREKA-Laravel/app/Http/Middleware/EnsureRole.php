<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengganti requireLogin() + requireRole() pada versi PHP native.
 *
 * Contoh pemakaian di routes/web.php:
 *   ->middleware('role:user')        // hanya pelamar
 *   ->middleware('role:hr,admin')    // HR atau admin
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! isLoggedIn()) {
            setFlash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.');

            return redirect()->route('auth.login');
        }

        if (! hasRole($roles)) {
            setFlash('danger', 'Anda tidak memiliki hak akses ke halaman tersebut.');

            return redirect(homeUrl());
        }

        return $next($request);
    }
}
