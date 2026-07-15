<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangMasukDetail extends Model
{
    use HasFactory;

    protected $table = 'trx_barang_masuk_detail';
    protected $fillable = [
        'barang_masuk_header_id',
        'produk_id',
        'qty_masuk',
        'satuan',
        'lokasi_simpan',
        'hpp',
        'po_detail_id',
    ];

    public function barangMasukHeader(): BelongsTo
    {
        return $this->belongsTo(BarangMasuk::class, 'barang_masuk_header_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function poDetail(): BelongsTo
    {
        return $this->belongsTo(PoDetail::class, 'po_detail_id');
    }
}
