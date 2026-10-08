<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `talent_pool` - Penanda kandidat talent pool (satu baris per pelamar).
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class TalentPool extends Model
{
    protected $table = 'talent_pool';

    protected $primaryKey = 'id_talent_pool';

    public $timestamps = false;

    protected $guarded = [];

    public function pelamar()
    {
        return $this->belongsTo(Pelamar::class, 'nik', 'nik');
    }

    public function sourceApplication()
    {
        return $this->belongsTo(Application::class, 'source_application', 'id_application');
    }

    public function addedBy()
    {
        return $this->belongsTo(Staff::class, 'added_by', 'nik');
    }
}
