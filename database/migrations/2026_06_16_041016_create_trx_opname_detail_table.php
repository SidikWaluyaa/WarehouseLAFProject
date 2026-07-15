<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_opname_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opname_header_id')->constrained('trx_opname_header')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('mst_produk')->onDelete('cascade');
            $table->integer('qty_sistem')->default(0);
            $table->integer('qty_fisik')->default(0);
            $table->integer('selisih')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_opname_detail');
    }
};
