<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangKeluarDetail extends Model
{
    use HasFactory;

    protected $table = 'trx_barang_keluar_detail';
    protected $fillable = [
        'barang_keluar_header_id',
        'produk_id',
        'qty_keluar',
        'satuan',
        'lokasi_pengambilan',
        'hpp',
    ];

    public function barangKeluarHeader(): BelongsTo
    {
        return $this->belongsTo(BarangKeluar::class, 'barang_keluar_header_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
