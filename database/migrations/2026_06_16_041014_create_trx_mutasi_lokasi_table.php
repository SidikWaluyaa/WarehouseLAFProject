<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_mutasi_lokasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('mst_produk')->onDelete('cascade');
            $table->foreignId('lokasi_asal_id')->nullable()->constrained('mst_lokasi')->onDelete('set null');
            $table->foreignId('lokasi_tujuan_id')->nullable()->constrained('mst_lokasi')->onDelete('set null');
            $table->integer('qty');
            $table->string('tipe_mutasi', 50); // MASUK, KELUAR, TRANSFER, OPNAME_ADJUST
            $table->string('referensi_tipe', 100)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('data_dimasukan_oleh', 100);
            $table->timestamps();

            $table->index(['produk_id', 'lokasi_asal_id']);
            $table->index(['produk_id', 'lokasi_tujuan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_mutasi_lokasi');
    }
};
