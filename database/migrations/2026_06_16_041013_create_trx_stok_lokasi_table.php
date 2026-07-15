<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_stok_lokasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('mst_produk')->onDelete('cascade');
            $table->foreignId('lokasi_id')->constrained('mst_lokasi')->onDelete('cascade');
            $table->integer('qty')->default(0);
            $table->timestamps();

            $table->unique(['produk_id', 'lokasi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_stok_lokasi');
    }
};
