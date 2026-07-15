<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_transfer_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_header_id')->constrained('trx_transfer_header')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('mst_produk')->onDelete('cascade');
            $table->integer('qty_requested')->default(0);
            $table->integer('qty_sent')->default(0);
            $table->integer('qty_received')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_transfer_detail');
    }
};
