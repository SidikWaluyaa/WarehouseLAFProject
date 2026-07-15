<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
