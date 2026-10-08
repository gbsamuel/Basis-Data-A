<?php

namespace App\Providers;

use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Hasil query dikembalikan sebagai array asosiatif (PDO::FETCH_ASSOC),
        // sama seperti versi native, sehingga view tetap memakai $row['nama_kolom'].
        Event::listen(StatementPrepared::class, function (StatementPrepared $event) {
            $event->statement->setFetchMode(PDO::FETCH_ASSOC);
        });
    }
}
