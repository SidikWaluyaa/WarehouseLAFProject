<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $kategori
 * @property string|null $prefix
 * @property bool $include_category_prefix
 * @property bool $include_product_initials
 * @property bool $include_variant_code
 * @property int $increment_digits
 * @property string $format_tipe
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AturanKode extends Model
{
    use HasFactory;

    protected $table = 'mst_aturan_kode';
    protected $fillable = [
        'kategori',
        'prefix',
        'include_category_prefix',
        'include_product_initials',
        'include_variant_code',
        'increment_digits',
        'format_tipe',
        'is_active',
    ];

    protected $casts = [
        'include_category_prefix' => 'boolean',
        'include_product_initials' => 'boolean',
        'include_variant_code' => 'boolean',
        'is_active' => 'boolean',
        'increment_digits' => 'integer',
    ];
}
