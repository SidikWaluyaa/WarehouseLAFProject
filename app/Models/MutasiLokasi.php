<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
