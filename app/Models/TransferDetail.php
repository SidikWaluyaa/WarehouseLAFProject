<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $transfer_header_id
 * @property int $produk_id
 * @property int $qty_requested
 * @property int $qty_sent
 * @property int $qty_received
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TransferHeader|null $transferHeader
 * @property-read \App\Models\Produk|null $produk
 */
class TransferDetail extends Model
{
    protected $table = 'trx_transfer_detail';
    protected $fillable = [
        'transfer_header_id',
        'produk_id',
        'qty_requested',
        'qty_sent',
        'qty_received',
    ];

    public function transferHeader(): BelongsTo
    {
        return $this->belongsTo(TransferHeader::class, 'transfer_header_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
