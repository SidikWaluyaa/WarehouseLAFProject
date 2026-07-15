<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mst_lokasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_lokasi', 100)->unique();
            $table->string('nama_lokasi', 200);
            $table->enum('tipe_lokasi', ['GUDANG', 'TOKO'])->default('GUDANG');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default locations
        DB::table('mst_lokasi')->insert([
            [
                'kode_lokasi' => 'GDG-A01',
                'nama_lokasi' => 'Gudang Utama - Rak A1',
                'tipe_lokasi' => 'GUDANG',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_lokasi' => 'TKO-R01',
                'nama_lokasi' => 'Toko Depan - Rak R1',
                'tipe_lokasi' => 'TOKO',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_lokasi');
    }
};
