<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `job` - Lowongan: Kerja, Magang, Management Trainee, PKL. pic_nik = HR penanggung jawab.
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Job extends Model
{
    protected $table = 'job';

    protected $primaryKey = 'id_job';

    protected $guarded = [];

    public function division()
    {
        return $this->belongsTo(Division::class, 'id_division', 'id_division');
    }

    public function pic()
    {
        return $this->belongsTo(Staff::class, 'pic_nik', 'nik');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'job_skill', 'id_job', 'id_skill')->withPivot('is_required');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'id_job', 'id_job');
    }
}
