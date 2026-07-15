<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_header_id')->constrained('po_header')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('mst_produk')->onDelete('cascade');
            $table->integer('jumlah');
            $table->integer('barang_masuk')->default(0);
            $table->integer('sisa')->default(0);
            $table->decimal('hpp', 15, 2)->nullable();
            $table->decimal('total_hpp', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_detail');
    }
};
