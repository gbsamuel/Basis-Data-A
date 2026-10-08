<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `user_all` - Semua akun yang bisa login (role: user = pelamar, hr, admin).
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class UserAll extends Model
{
    protected $table = 'user_all';

    protected $primaryKey = 'nik';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $hidden = ['password'];

    protected $guarded = [];

    public function pelamar()
    {
        return $this->hasOne(Pelamar::class, 'nik', 'nik');
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'nik', 'nik');
    }
}
