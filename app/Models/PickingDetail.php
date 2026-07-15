<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickingDetail extends Model
{
    protected $table = 'trx_picking_detail';
    protected $fillable = [
        'picking_list_id',
        'produk_id',
        'lokasi_rak_id',
        'qty_to_pick',
        'qty_picked',
        'is_verified',
    ];

    public function pickingList(): BelongsTo
    {
        return $this->belongsTo(PickingList::class, 'picking_list_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function lokasiRak(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_rak_id');
    }
}
