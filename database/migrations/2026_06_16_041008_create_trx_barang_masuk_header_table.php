<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_barang_masuk_header', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('tipe_nota', ['Stock Awal', 'Pembelian', 'Retur Masuk', 'Penyesuaian']);
            $table->foreignId('supplier_id')->nullable()->constrained('mst_supplier')->onDelete('restrict');
            $table->string('nomor_nota', 100)->nullable()->unique();
            $table->text('keterangan_tambahan')->nullable();
            $table->string('data_dimasukan_oleh', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_barang_masuk_header');
    }
};
