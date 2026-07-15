<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
