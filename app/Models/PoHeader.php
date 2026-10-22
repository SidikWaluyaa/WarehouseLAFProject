<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoHeader extends Model
{
    use HasFactory;

    protected $table = 'po_header';
    protected $fillable = [
        'nomor_po',
        'tanggal',
        'supplier_id',
        'artikel',
        'jumlah',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function poDetails(): HasMany
    {
        return $this->hasMany(PoDetail::class, 'po_header_id');
    }

    public function barangMasukHeaders(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'po_header_id');
    }

    public function totalSisa(): int
    {
        return (int) $this->poDetails()->sum('sisa');
    }

    public function totalBarangMasuk(): int
    {
        return (int) $this->poDetails()->sum('barang_masuk');
    }

    public function isCompleted(): bool
    {
        return $this->totalSisa() === 0;
    }
}
