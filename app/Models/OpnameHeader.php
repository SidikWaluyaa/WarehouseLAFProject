<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpnameHeader extends Model
{
    use HasFactory;

    protected $table = 'trx_opname_header';
    protected $fillable = [
        'nomor_opname',
        'tanggal',
        'lokasi_id',
        'status',
        'keterangan',
        'operator_name',
        'adjusted_by',
        'adjusted_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'adjusted_at' => 'datetime',
    ];

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(OpnameDetail::class, 'opname_header_id');
    }
}
