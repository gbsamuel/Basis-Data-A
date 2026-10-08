<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `division` - Divisi IT perusahaan.
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Division extends Model
{
    protected $table = 'division';

    protected $primaryKey = 'id_division';

    public $timestamps = false;

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class, 'id_company', 'id_company');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'id_division', 'id_division');
    }
}
