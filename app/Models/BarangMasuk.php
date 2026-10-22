<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function scopeFromPo($query)
    {
        return $query->whereNotNull('po_header_id')->orWhere('tipe_nota', 'Purchase Order');
    }

    public function scopeNonPo($query)
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
