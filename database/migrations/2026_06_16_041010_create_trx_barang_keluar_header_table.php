<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_barang_keluar_header', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('tipe_nota', ['Penjualan', 'Retur Keluar', 'Penyesuaian', 'Sample']);
            $table->foreignId('pelanggan_id')->constrained('mst_pelanggan')->onDelete('restrict');
            $table->string('nomor_nota', 100)->nullable()->unique();
            $table->text('keterangan_tambahan')->nullable();
            $table->string('resi_ekspedisi', 200)->nullable();
            $table->string('kurir', 100)->nullable();
            $table->boolean('is_qc_passed')->default(false);
            $table->string('dikeluarkan_oleh', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_barang_keluar_header');
    }
};
