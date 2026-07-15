<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
