<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `candidate_stage_history` - Riwayat tahap seleksi.
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class CandidateStageHistory extends Model
{
    protected $table = 'candidate_stage_history';

    protected $primaryKey = 'id_history';

    public $timestamps = false;

    protected $guarded = [];

    public function application()
    {
        return $this->belongsTo(Application::class, 'id_application', 'id_application');
    }

    public function changedBy()
    {
        return $this->belongsTo(Staff::class, 'changed_by', 'nik');
    }
}
