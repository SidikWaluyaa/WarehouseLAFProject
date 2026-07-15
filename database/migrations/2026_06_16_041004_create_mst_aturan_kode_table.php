<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mst_aturan_kode', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 100)->nullable()->unique();
            $table->string('prefix', 50)->nullable();
            $table->boolean('include_category_prefix')->default(true);
            $table->boolean('include_product_initials')->default(true);
            $table->boolean('include_variant_code')->default(true);
            $table->integer('increment_digits')->default(3);
            $table->string('format_tipe', 50)->default('INCREMENT');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed a default global fallback rule
        DB::table('mst_aturan_kode')->insert([
            'kategori' => null, // null represents global fallback
            'prefix' => 'LAF',
            'include_category_prefix' => true,
            'include_product_initials' => true,
            'include_variant_code' => true,
            'increment_digits' => 3,
            'format_tipe' => 'INCREMENT',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_aturan_kode');
    }
};
