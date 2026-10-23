<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $produk_id
 * @property int|null $lokasi_asal_id
 * @property int|null $lokasi_tujuan_id
 * @property int $qty
 * @property int|null $total_out
 * @property string $tipe_mutasi
 * @property string|null $referensi_tipe
 * @property int|null $referensi_id
 * @property string|null $keterangan
 * @property string|null $data_dimasukan_oleh
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Produk|null $produk
 * @property-read \App\Models\Lokasi|null $lokasiAsal
 * @property-read \App\Models\Lokasi|null $lokasiTujuan
 * @property-read Model|null $referensi
 */
class MutasiLokasi extends Model
{
    use HasFactory;

    protected $table = 'trx_mutasi_lokasi';
    protected $fillable = [
        'produk_id',
        'lokasi_asal_id',
        'lokasi_tujuan_id',
        'qty',
        'tipe_mutasi',
        'referensi_tipe',
        'referensi_id',
        'keterangan',
        'data_dimasukan_oleh',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function lokasiAsal(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_asal_id');
    }

    public function lokasiTujuan(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_tujuan_id');
    }

    public function referensi(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'referensi_tipe', 'referensi_id');
    }
}
