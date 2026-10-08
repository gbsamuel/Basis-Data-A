<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `staff` - Spesialisasi user_all untuk role 'hr' dan 'admin' (is_kepala_hr = kepala HR).
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Staff extends Model
{
    protected $table = 'staff';

    protected $primaryKey = 'nik';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function akun()
    {
        return $this->belongsTo(UserAll::class, 'nik', 'nik');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'pic_nik', 'nik');
    }
}
