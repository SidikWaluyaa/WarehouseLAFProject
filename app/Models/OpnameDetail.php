<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $opname_header_id
 * @property int $produk_id
 * @property int $qty_sistem
 * @property int $qty_fisik
 * @property int $selisih
 * @property string|null $catatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OpnameHeader|null $header
 * @property-read \App\Models\Produk|null $produk
 */
class OpnameDetail extends Model
{
    use HasFactory;

    protected $table = 'trx_opname_detail';
    protected $fillable = [
        'opname_header_id',
        'produk_id',
        'qty_sistem',
        'qty_fisik',
        'selisih',
        'catatan',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(OpnameHeader::class, 'opname_header_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
