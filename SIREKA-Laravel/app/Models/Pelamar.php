<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `pelamar` - Spesialisasi user_all untuk role 'user' (pelamar).
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Pelamar extends Model
{
    protected $table = 'pelamar';

    protected $primaryKey = 'nik';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function akun()
    {
        return $this->belongsTo(UserAll::class, 'nik', 'nik');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skill', 'nik', 'id_skill')->withPivot('level');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'nik', 'nik');
    }

    public function talentPool()
    {
        return $this->hasOne(TalentPool::class, 'nik', 'nik');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'nik', 'nik');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'nik', 'nik');
    }
}
