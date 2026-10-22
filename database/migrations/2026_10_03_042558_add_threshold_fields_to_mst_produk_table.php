<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_produk', function (Blueprint $table) {
            $table->integer('min_stok')->nullable()->after('total_stok');
            $table->boolean('is_threshold_active')->default(false)->after('min_stok');
            $table->string('notif_phone', 30)->nullable()->after('is_threshold_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_produk', function (Blueprint $table) {
            $table->dropColumn(['min_stok', 'is_threshold_active', 'notif_phone']);
        });
    }
};
