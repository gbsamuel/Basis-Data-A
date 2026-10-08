<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `complaint` - Keluhan pelamar.
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Complaint extends Model
{
    protected $table = 'complaint';

    protected $primaryKey = 'id_complaint';

    public $timestamps = false;

    protected $guarded = [];

    public function pelamar()
    {
        return $this->belongsTo(Pelamar::class, 'nik', 'nik');
    }

    public function respondedBy()
    {
        return $this->belongsTo(Staff::class, 'responded_by', 'nik');
    }
}
