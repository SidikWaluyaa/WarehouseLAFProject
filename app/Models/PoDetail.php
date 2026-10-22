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
        'qty_ditutup',
        'alasan_tutup',
        'ditutup_oleh',
        'ditutup_pada',
        'hpp',
        'total_hpp',
    ];

    protected $casts = [
        'ditutup_pada' => 'datetime',
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

    /**
     * Sisa yang masih harus dikirim supplier = dipesan - diterima - ditutup (min 0).
     */
    public function hitungSisa(): int
    {
        return max(0, (int) $this->jumlah - (int) $this->barang_masuk - (int) ($this->qty_ditutup ?? 0));
    }

    /**
     * Recalculate sisa & total_hpp sisa (does not save).
     */
    public function recalculateSisa(): void
    {
        $this->sisa = $this->hitungSisa();
        if ($this->hpp) {
            $this->total_hpp = $this->sisa * $this->hpp;
        }
    }

    /**
     * Status penerimaan per item: belum | sebagian | selesai | lebih | ditutup
     */
    public function statusPenerimaan(): string
    {
        $diterima = (int) $this->barang_masuk;
        $jumlah = (int) $this->jumlah;

        if ($diterima > $jumlah) {
            return 'lebih';
        }
        if ((int) $this->sisa === 0) {
            return ((int) ($this->qty_ditutup ?? 0) > 0) ? 'ditutup' : 'selesai';
        }
        return $diterima > 0 ? 'sebagian' : 'belum';
    }
}
