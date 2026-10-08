<?php

use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| php artisan sireka:import-db
|--------------------------------------------------------------------------
| Reset & import ulang database sireka_db dari file database/sireka_db.sql
| (sama seperti "php artisan migrate:fresh" pada artisan buatan versi native).
| File SQL tidak diubah sama sekali, jadi struktur tabel dan data demo identik.
*/
Artisan::command('sireka:import-db {--force : Jalankan tanpa konfirmasi}', function () {
    $file = database_path('sireka_db.sql');
    $cfg = config('database.connections.mysql');

    $this->newLine();
    $this->line("  Mengimpor database <info>{$cfg['database']}</info> dari database/sireka_db.sql ...");
    $this->line("  Server: {$cfg['host']}:{$cfg['port']}");

    if (! $this->option('force') && ! $this->confirm('Semua data di database sireka_db akan dihapus lalu diisi ulang dengan data demo. Lanjutkan?', true)) {
        $this->warn('  Dibatalkan.');

        return 1;
    }

    try {
        // Koneksi tanpa memilih database, karena file SQL berisi DROP/CREATE DATABASE
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        // PHP 8.4+ memakai Pdo\Mysql::ATTR_SSL_CA (PDO::MYSQL_ATTR_SSL_CA sudah deprecated di PHP 8.5)
        $sslCa = PHP_VERSION_ID >= 80400 ? \Pdo\Mysql::ATTR_SSL_CA : \PDO::MYSQL_ATTR_SSL_CA;
        if (! empty($cfg['options'][$sslCa] ?? null)) {
            $options[$sslCa] = $cfg['options'][$sslCa];
        }
        $pdo = new PDO("mysql:host={$cfg['host']};port={$cfg['port']};charset=utf8mb4", $cfg['username'], $cfg['password'], $options);
        $pdo->exec(file_get_contents($file));
    } catch (Throwable $e) {
        $this->error('  Gagal mengimpor database. Pastikan MySQL Laragon sudah aktif (klik Start All).');
        $this->line('  Detail: '.$e->getMessage());

        return 1;
    }

    $this->info("  SUCCESS Database {$cfg['database']} berhasil diperbarui!");
    $this->newLine();

    return 0;
})->purpose('Reset & import ulang database sireka_db dari database/sireka_db.sql');
