<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `system_settings` - Pengaturan aplikasi (key - value).
 * Struktur tabel mengikuti database/sireka_db.sql (tidak diubah).
 */
class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $primaryKey = 'setting_key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];
}
