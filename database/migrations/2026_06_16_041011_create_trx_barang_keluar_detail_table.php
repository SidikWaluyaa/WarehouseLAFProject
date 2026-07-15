<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_barang_keluar_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_keluar_header_id')->constrained('trx_barang_keluar_header')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('mst_produk')->onDelete('cascade');
            $table->integer('qty_keluar');
            $table->string('satuan', 20);
            $table->string('lokasi_pengambilan', 100)->default('GDG-A01');
            $table->decimal('hpp', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_barang_keluar_detail');
    }
};
