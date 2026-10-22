<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PickingDetail
 *
 * @property int $id
 * @property int $picking_list_id
 * @property int $produk_id
 * @property int $lokasi_rak_id
 * @property int $qty_to_pick
 * @property int $qty_picked
 * @property bool $is_verified
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PickingList $pickingList
 * @property-read \App\Models\Produk $produk
 * @property-read \App\Models\Lokasi $lokasiRak
 */
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
