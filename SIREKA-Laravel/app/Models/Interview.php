<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `interview` - Jadwal dan hasil wawancara (pewawancara = akun HR).
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Interview extends Model
{
    protected $table = 'interview';

    protected $primaryKey = 'id_interview';

    public $timestamps = false;

    protected $guarded = [];

    public function application()
    {
        return $this->belongsTo(Application::class, 'id_application', 'id_application');
    }

    public function interviewer()
    {
        return $this->belongsTo(Staff::class, 'interviewer_nik', 'nik');
    }
}
