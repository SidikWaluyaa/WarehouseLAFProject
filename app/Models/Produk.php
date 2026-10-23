<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Produk
 *
 * @property int $id
 * @property float $hpp
 * @property float $harga_jual
 * @property string $nama_produk
 * @property string $kategori
 * @property string|null $kategori_2
 * @property string $kode_barang
 * @property string $nama_barang
 * @property string|null $variasi_barang
 * @property string|null $warna
 * @property string|null $size
 * @property string $satuan
 * @property int $stok_masuk
 * @property int $stok_keluar
 * @property int $total_stok
 * @property int|null $min_stok
 * @property bool $is_threshold_active
 * @property string|null $notif_phone
 * @property string|null $lokasi
 * @property mixed $lokasi_keluar
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AnalyticsProduk|null $analyticsProduk
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StokLokasi> $stokLokasi
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MutasiLokasi> $mutasiLokasi
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TransferDetail> $transferDetails
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PickingDetail> $pickingDetails
 * @property-read int|null $stok_lokasi_count
 * @property-read int|null $mutasi_lokasi_count
 * @property-read int|null $transfer_details_count
 * @property-read int|null $picking_details_count
 */
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
        'min_stok',
        'is_threshold_active',
        'notif_phone',
        'lokasi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_threshold_active' => 'boolean',
        'min_stok' => 'integer',
        'hpp' => 'float',
        'harga_jual' => 'float',
    ];

    protected static function booted()
    {
        static::created(function (self $produk) {
            $lokasiCode = $produk->lokasi ?: 'GDG-A01';

            /** @var \App\Models\Lokasi|null $lokasi */
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

    /**
     * Scope: only products with active threshold whose stock has reached or dropped below minimal threshold
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where('is_threshold_active', true)
                     ->where('min_stok', '>', 0)
                     ->whereColumn('total_stok', '<=', 'min_stok');
    }

    /**
     * Check if product is in low stock condition
     */
    public function isLowStock(): bool
    {
        return (bool)$this->is_threshold_active && (int)$this->min_stok > 0 && (int)$this->total_stok <= (int)$this->min_stok;
    }
}
