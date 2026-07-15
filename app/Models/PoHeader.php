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
}
