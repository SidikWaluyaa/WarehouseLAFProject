<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'mst_produk';
    protected $fillable = [
        'hpp',
        'harga_jual',
        'nama_produk',
        'kategori',
        'kategori_2',
        'kode_barang',
        'nama_barang',
        'variasi_barang',
        'warna',
        'size',
        'satuan',
        'stok_masuk',
        'stok_keluar',
        'total_stok',
        'lokasi',
        'is_active',
    ];

    protected static function booted()
    {
        static::created(function ($produk) {
            $lokasiCode = $produk->lokasi ?: 'GDG-A01';

            $lokasi = \App\Models\Lokasi::where(['kode_lokasi' => $lokasiCode])->first();
            if (!$lokasi) {
                $tipe = in_array(strtoupper($lokasiCode), ['TOKO', 'GUDANG']) ? strtoupper($lokasiCode) : 'GUDANG';
                $lokasi = \App\Models\Lokasi::firstOrCreate(
                    ['kode_lokasi' => $lokasiCode],
                    ['nama_lokasi' => 'Lokasi ' . $lokasiCode, 'tipe_lokasi' => $tipe, 'is_active' => true]
                );
            }

            \App\Models\StokLokasi::updateOrCreate(
                ['produk_id' => $produk->id, 'lokasi_id' => $lokasi->id],
                ['qty' => $produk->total_stok ?? 0]
            );
        });
    }

    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasukDetail::class, 'produk_id');
    }

    public function barangKeluar(): HasMany
    {
        return $this->hasMany(BarangKeluarDetail::class, 'produk_id');
    }

    public function poDetails(): HasMany
    {
        return $this->hasMany(PoDetail::class, 'produk_id');
    }

    public function analyticsProduk(): HasOne
    {
        return $this->hasOne(AnalyticsProduk::class, 'produk_id');
    }

    public function stokLokasi(): HasMany
    {
        return $this->hasMany(StokLokasi::class, 'produk_id');
    }

    public function mutasiLokasi(): HasMany
    {
        return $this->hasMany(MutasiLokasi::class, 'produk_id');
    }

    public function transferDetails(): HasMany
    {
        return $this->hasMany(TransferDetail::class, 'produk_id');
    }

    public function pickingDetails(): HasMany
    {
        return $this->hasMany(PickingDetail::class, 'produk_id');
    }

    /**
     * Scope: only active products
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
