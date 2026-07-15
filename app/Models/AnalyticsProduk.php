<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsProduk extends Model
{
    use HasFactory;

    protected $table = 'analytics_produk';

    protected $fillable = [
        'produk_id',
        'kategori',
        'penjualan_jan',
        'penjualan_feb',
        'penjualan_mar',
        'penjualan_apr',
        'penjualan_mei',
        'penjualan_jun',
        'penjualan_jul',
        'penjualan_ags',
        'penjualan_sep',
        'penjualan_okt',
        'penjualan_nov',
        'penjualan_des',
        'rata_rata_harian',
        'abc_class',
        'leadtime_produksi',
        'minimal_stock',
        'safety_stock',
        'maximum_stock',
        'is_manual_stock',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
