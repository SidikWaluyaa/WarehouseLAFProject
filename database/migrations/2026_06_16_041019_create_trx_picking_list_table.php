<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_picking_list', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_picking', 100)->unique();
            $table->string('referensi_tipe', 100);
            $table->unsignedBigInteger('referensi_id');
            $table->foreignId('lokasi_id')->constrained('mst_lokasi')->onDelete('cascade');
            $table->enum('status', ['PENDING', 'PICKING', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            $table->string('picker_name', 100)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_picking_list');
    }
};
