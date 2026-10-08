<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `application` - Lamaran (satu pelamar hanya sekali per lowongan).
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Application extends Model
{
    protected $table = 'application';

    protected $primaryKey = 'id_application';

    protected $guarded = [];

    public function pelamar()
    {
        return $this->belongsTo(Pelamar::class, 'nik', 'nik');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'id_job', 'id_job');
    }

    public function stageHistory()
    {
        return $this->hasMany(CandidateStageHistory::class, 'id_application', 'id_application');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'id_application', 'id_application');
    }

    public function loa()
    {
        return $this->hasOne(Loa::class, 'id_application', 'id_application');
    }
}
