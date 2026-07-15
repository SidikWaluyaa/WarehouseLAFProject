<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangKeluar extends Model
{
    use HasFactory;

    protected $table = 'trx_barang_keluar_header';
    protected $fillable = [
        'tanggal',
        'tipe_nota',
        'pelanggan_id',
        'nomor_nota',
        'keterangan_tambahan',
        'resi_ekspedisi',
        'kurir',
        'is_qc_passed',
        'dikeluarkan_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(BarangKeluarDetail::class, 'barang_keluar_header_id');
    }
}
