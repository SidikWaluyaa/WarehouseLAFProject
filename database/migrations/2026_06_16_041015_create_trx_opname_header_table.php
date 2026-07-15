<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_opname_header', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_opname', 100)->unique();
            $table->date('tanggal');
            $table->foreignId('lokasi_id')->constrained('mst_lokasi')->onDelete('restrict');
            $table->enum('status', ['DRAFT', 'COMPLETED', 'ADJUSTED', 'CANCELLED'])->default('DRAFT');
            $table->text('keterangan')->nullable();
            $table->string('operator_name', 100);
            $table->string('adjusted_by', 100)->nullable();
            $table->timestamp('adjusted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_opname_header');
    }
};
