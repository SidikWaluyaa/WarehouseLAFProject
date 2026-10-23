<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $barang_keluar_header_id
 * @property int $produk_id
 * @property int $qty_keluar
 * @property string $satuan
 * @property string $lokasi_pengambilan
 * @property float $hpp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BarangKeluar|null $barangKeluarHeader
 * @property-read \App\Models\Produk|null $produk
 */
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
