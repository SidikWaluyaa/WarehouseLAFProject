<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nomor_transfer
 * @property \Illuminate\Support\Carbon $tanggal
 * @property int $lokasi_asal_id
 * @property int $lokasi_tujuan_id
 * @property string $status
 * @property string|null $keterangan
 * @property string $created_by
 * @property string|null $received_by
 * @property \Illuminate\Support\Carbon|null $received_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lokasi|null $lokasiAsal
 * @property-read \App\Models\Lokasi|null $lokasiTujuan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TransferDetail> $details
 * @property-read int|null $details_count
 */
class TransferHeader extends Model
{
    protected $table = 'trx_transfer_header';
    protected $fillable = [
        'nomor_transfer',
        'tanggal',
        'lokasi_asal_id',
        'lokasi_tujuan_id',
        'status',
        'keterangan',
        'created_by',
        'received_by',
        'received_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'received_at' => 'datetime',
    ];

    public function lokasiAsal(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_asal_id');
    }

    public function lokasiTujuan(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_tujuan_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransferDetail::class, 'transfer_header_id');
    }
}
