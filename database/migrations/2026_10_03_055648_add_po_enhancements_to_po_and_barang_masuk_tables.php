<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add nomor_po to po_header
        Schema::table('po_header', function (Blueprint $table) {
            if (!Schema::hasColumn('po_header', 'nomor_po')) {
                $table->string('nomor_po', 100)->nullable()->after('id');
            }
        });

        // 2. Add po_header_id to trx_barang_masuk_header & allow Purchase Order in tipe_nota
        Schema::table('trx_barang_masuk_header', function (Blueprint $table) {
            if (!Schema::hasColumn('trx_barang_masuk_header', 'po_header_id')) {
                $table->foreignId('po_header_id')
                    ->nullable()
                    ->after('nomor_nota')
                    ->constrained('po_header')
                    ->nullOnDelete();
            }
        });

        // Modify tipe_nota to string(50) for flexibility (supporting Purchase Order, Pembelian, etc.)
        // For MySQL:
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE trx_barang_masuk_header MODIFY COLUMN tipe_nota VARCHAR(50) NOT NULL DEFAULT 'Pembelian'");
        }
    }

    public function down(): void
    {
        Schema::table('trx_barang_masuk_header', function (Blueprint $table) {
            if (Schema::hasColumn('trx_barang_masuk_header', 'po_header_id')) {
                $table->dropForeign(['po_header_id']);
                $table->dropColumn('po_header_id');
            }
        });

        Schema::table('po_header', function (Blueprint $table) {
            if (Schema::hasColumn('po_header', 'nomor_po')) {
                $table->dropColumn('nomor_po');
            }
        });
    }
};
