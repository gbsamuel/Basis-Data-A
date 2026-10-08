<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `feedback` - Ulasan pelamar.
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Feedback extends Model
{
    protected $table = 'feedback';

    protected $primaryKey = 'id_feedback';

    public $timestamps = false;

    protected $guarded = [];

    public function pelamar()
    {
        return $this->belongsTo(Pelamar::class, 'nik', 'nik');
    }
}
