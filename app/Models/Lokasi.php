<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Lokasi
 *
 * @property int $id
 * @property string $kode_lokasi
 * @property string $nama_lokasi
 * @property string $tipe_lokasi
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'mst_lokasi';
    protected $fillable = [
        'kode_lokasi',
        'nama_lokasi',
        'tipe_lokasi',
        'is_active',
    ];

    public function stokLokasi(): HasMany
    {
        return $this->hasMany(StokLokasi::class, 'lokasi_id');
    }

    public function mutasiAsal(): HasMany
    {
        return $this->hasMany(MutasiLokasi::class, 'lokasi_asal_id');
    }

    public function mutasiTujuan(): HasMany
    {
        return $this->hasMany(MutasiLokasi::class, 'lokasi_tujuan_id');
    }

    public function opnameHeaders(): HasMany
    {
        return $this->hasMany(OpnameHeader::class, 'lokasi_id');
    }
}
