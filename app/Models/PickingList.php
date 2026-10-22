<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\PickingList
 *
 * @property int $id
 * @property string $nomor_picking
 * @property string $referensi_tipe
 * @property int $referensi_id
 * @property int $lokasi_id
 * @property string $status
 * @property string|null $picker_name
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property-read \App\Models\Lokasi $lokasi
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PickingDetail> $details
 */
class PickingList extends Model
{
    protected $table = 'trx_picking_list';
    protected $fillable = [
        'nomor_picking',
        'referensi_tipe',
        'referensi_id',
        'lokasi_id',
        'status',
        'picker_name',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PickingDetail::class, 'picking_list_id');
    }
}
