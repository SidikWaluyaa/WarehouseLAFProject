<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_picking_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picking_list_id')->constrained('trx_picking_list')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('mst_produk')->onDelete('cascade');
            $table->foreignId('lokasi_rak_id')->nullable()->constrained('mst_lokasi')->onDelete('set null');
            $table->integer('qty_to_pick')->default(0);
            $table->integer('qty_picked')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_picking_detail');
    }
};
