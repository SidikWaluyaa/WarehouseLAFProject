<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nama_supplier
 * @property string|null $kontak
 * @property string|null $alamat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
