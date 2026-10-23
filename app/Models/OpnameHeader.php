<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nomor_opname
 * @property \Illuminate\Support\Carbon $tanggal
 * @property int $lokasi_id
 * @property string $status
 * @property string|null $keterangan
 * @property string $operator_name
 * @property string|null $adjusted_by
 * @property \Illuminate\Support\Carbon|null $adjusted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lokasi|null $lokasi
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OpnameDetail> $details
 * @property-read int|null $details_count
 */
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
