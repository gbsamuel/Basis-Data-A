<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `loa` - Letter of Acceptance.
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class Loa extends Model
{
    protected $table = 'loa';

    protected $primaryKey = 'id_loa';

    public $timestamps = false;

    protected $guarded = [];

    public function application()
    {
        return $this->belongsTo(Application::class, 'id_application', 'id_application');
    }
}
