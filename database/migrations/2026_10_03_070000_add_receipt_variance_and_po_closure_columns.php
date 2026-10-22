<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat selisih penerimaan per baris barang masuk yang terhubung ke PO
        Schema::table('trx_barang_masuk_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('trx_barang_masuk_detail', 'sisa_po_sebelum')) {
                $table->integer('sisa_po_sebelum')->nullable()->after('po_detail_id');
            }
            if (!Schema::hasColumn('trx_barang_masuk_detail', 'selisih_po')) {
                // qty_masuk - sisa_po_sebelum (negatif = kurang kirim, positif = lebih kirim)
                $table->integer('selisih_po')->nullable()->after('sisa_po_sebelum');
            }
            if (!Schema::hasColumn('trx_barang_masuk_detail', 'alasan_selisih')) {
                $table->string('alasan_selisih', 255)->nullable()->after('selisih_po');
            }
        });

        // Penutupan sisa PO (supplier tidak akan mengirim sisa)
        Schema::table('po_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('po_detail', 'qty_ditutup')) {
                $table->integer('qty_ditutup')->default(0)->after('sisa');
            }
            if (!Schema::hasColumn('po_detail', 'alasan_tutup')) {
                $table->string('alasan_tutup', 255)->nullable()->after('qty_ditutup');
            }
            if (!Schema::hasColumn('po_detail', 'ditutup_oleh')) {
                $table->string('ditutup_oleh', 100)->nullable()->after('alasan_tutup');
            }
            if (!Schema::hasColumn('po_detail', 'ditutup_pada')) {
                $table->timestamp('ditutup_pada')->nullable()->after('ditutup_oleh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trx_barang_masuk_detail', function (Blueprint $table) {
            foreach (['alasan_selisih', 'selisih_po', 'sisa_po_sebelum'] as $col) {
                if (Schema::hasColumn('trx_barang_masuk_detail', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('po_detail', function (Blueprint $table) {
            foreach (['ditutup_pada', 'ditutup_oleh', 'alasan_tutup', 'qty_ditutup'] as $col) {
                if (Schema::hasColumn('po_detail', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
