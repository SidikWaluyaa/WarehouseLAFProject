<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('mst_produk')->onDelete('cascade');
            $table->string('kategori', 100);
            $table->integer('penjualan_jan')->default(0);
            $table->integer('penjualan_feb')->default(0);
            $table->integer('penjualan_mar')->default(0);
            $table->integer('penjualan_apr')->default(0);
            $table->integer('penjualan_mei')->default(0);
            $table->integer('penjualan_jun')->default(0);
            $table->integer('penjualan_jul')->default(0);
            $table->integer('penjualan_ags')->default(0);
            $table->integer('penjualan_sep')->default(0);
            $table->integer('penjualan_okt')->default(0);
            $table->integer('penjualan_nov')->default(0);
            $table->integer('penjualan_des')->default(0);
            $table->decimal('rata_rata_harian', 10, 2)->default(0.00);
            $table->char('abc_class', 1)->nullable()->index();
            $table->integer('leadtime_produksi')->default(14);
            $table->integer('minimal_stock')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->integer('maximum_stock')->default(0);
            $table->boolean('is_manual_stock')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_produk');
    }
};
