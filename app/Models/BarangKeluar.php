<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\BarangKeluar
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $tanggal
 * @property string $tipe_nota
 * @property int|null $pelanggan_id
 * @property string $nomor_nota
 * @property string|null $keterangan_tambahan
 * @property string|null $resi_ekspedisi
 * @property string|null $kurir
 * @property bool $is_qc_passed
 * @property string $dikeluarkan_oleh
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pelanggan|null $pelanggan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BarangKeluarDetail> $details
 */
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
