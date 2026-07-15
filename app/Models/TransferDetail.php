<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
