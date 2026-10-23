<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nama_pelanggan
 * @property string|null $kontak
 * @property string|null $alamat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'mst_pelanggan';
    protected $fillable = [
        'nama_pelanggan',
        'kontak',
        'alamat',
    ];

    public function barangKeluar(): HasMany
    {
        return $this->hasMany(BarangKeluar::class, 'pelanggan_id');
    }
}
