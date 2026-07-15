<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'mst_supplier';
    protected $fillable = [
        'nama_supplier',
        'kontak',
        'alamat',
    ];

    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'supplier_id');
    }

    public function poHeaders(): HasMany
    {
        return $this->hasMany(PoHeader::class, 'supplier_id');
    }
}
