<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $tanggal
 * @property string $tipe_nota
 * @property int $supplier_id
 * @property string $nomor_nota
 * @property int|null $po_header_id
 * @property string|null $keterangan_tambahan
 * @property string|null $data_dimasukan_oleh
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'trx_barang_masuk_header';
    protected $fillable = [
        'tanggal',
        'tipe_nota',
        'supplier_id',
        'nomor_nota',
        'po_header_id',
        'keterangan_tambahan',
        'data_dimasukan_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function poHeader(): BelongsTo
    {
        return $this->belongsTo(PoHeader::class, 'po_header_id');
    }

    public function scopeFromPo(Builder $query): Builder
    {
        return $query->whereNotNull('po_header_id')->orWhere('tipe_nota', 'Purchase Order');
    }

    public function scopeNonPo(Builder $query): Builder
    {
        return $query->whereNull('po_header_id')->where('tipe_nota', '!=', 'Purchase Order');
    }

    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->orderBy('created_at', 'desc');
    }

    public function details(): HasMany
    {
        return $this->hasMany(BarangMasukDetail::class, 'barang_masuk_header_id');
    }
}
