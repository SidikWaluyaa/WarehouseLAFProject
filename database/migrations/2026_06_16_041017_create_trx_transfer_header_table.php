<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_transfer_header', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transfer', 100)->unique();
            $table->date('tanggal');
            $table->foreignId('lokasi_asal_id')->constrained('mst_lokasi')->onDelete('restrict');
            $table->foreignId('lokasi_tujuan_id')->constrained('mst_lokasi')->onDelete('restrict');
            $table->enum('status', ['DRAFT', 'PICKING', 'IN_TRANSIT', 'RECEIVED', 'CLOSED', 'CANCELLED'])->default('DRAFT');
            $table->text('keterangan')->nullable();
            $table->string('created_by', 100);
            $table->string('received_by', 100)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_transfer_header');
    }
};
