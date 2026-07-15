<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mst_produk', function (Blueprint $table) {
            $table->id();
            $table->decimal('hpp', 15, 2)->default(0.00);
            $table->decimal('harga_jual', 15, 2)->default(0.00);
            $table->string('nama_produk', 200);
            $table->string('kategori', 100);
            $table->string('kategori_2', 100)->nullable();
            $table->string('kode_barang', 100)->unique();
            $table->string('nama_barang', 200)->index();
            $table->string('variasi_barang', 100);
            $table->string('warna', 100)->nullable();
            $table->string('size', 50)->nullable();
            $table->string('satuan', 20);
            $table->integer('stok_masuk')->default(0);
            $table->integer('stok_keluar')->default(0);
            $table->integer('total_stok')->default(0);
            $table->string('lokasi', 100)->default('GDG-A01');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_produk');
    }
};
