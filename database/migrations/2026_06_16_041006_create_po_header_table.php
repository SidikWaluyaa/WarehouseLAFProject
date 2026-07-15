<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_header', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('supplier_id')->constrained('mst_supplier')->onDelete('cascade');
            $table->string('artikel', 200)->nullable();
            $table->integer('jumlah')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_header');
    }
};
