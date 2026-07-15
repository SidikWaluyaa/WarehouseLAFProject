<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoDetail extends Model
{
    use HasFactory;

    protected $table = 'po_detail';
    protected $fillable = [
        'po_header_id',
        'produk_id',
        'jumlah',
        'barang_masuk',
        'sisa',
        'hpp',
        'total_hpp',
    ];

    public function poHeader(): BelongsTo
    {
        return $this->belongsTo(PoHeader::class, 'po_header_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasukDetail::class, 'po_detail_id');
    }
}
