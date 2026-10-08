<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `company` - Profil perusahaan (1 baris).
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Company extends Model
{
    protected $table = 'company';

    protected $primaryKey = 'id_company';

    protected $guarded = [];

    public function divisions()
    {
        return $this->hasMany(Division::class, 'id_company', 'id_company');
    }
}
