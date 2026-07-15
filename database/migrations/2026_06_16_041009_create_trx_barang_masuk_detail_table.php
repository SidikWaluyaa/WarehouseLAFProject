<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_barang_masuk_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_masuk_header_id')->constrained('trx_barang_masuk_header')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('mst_produk')->onDelete('cascade');
            $table->integer('qty_masuk');
            $table->string('satuan', 20);
            $table->string('lokasi_simpan', 100)->default('GDG-A01');
            $table->decimal('hpp', 15, 2);
            $table->foreignId('po_detail_id')->nullable()->constrained('po_detail')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_barang_masuk_detail');
    }
};
