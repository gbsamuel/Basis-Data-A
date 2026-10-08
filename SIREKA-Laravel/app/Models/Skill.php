<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `skill` - Katalog keahlian.
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Skill extends Model
{
    protected $table = 'skill';

    protected $primaryKey = 'id_skill';

    public $timestamps = false;

    protected $guarded = [];

    public function jobs()
    {
        return $this->belongsToMany(Job::class, 'job_skill', 'id_skill', 'id_job')->withPivot('is_required');
    }

    public function pelamar()
    {
        return $this->belongsToMany(Pelamar::class, 'user_skill', 'id_skill', 'nik')->withPivot('level');
    }
}
