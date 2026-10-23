<?php

namespace Tests\Feature\Performance;

use App\Livewire\Analytics\Index as AnalyticsIndex;
use App\Livewire\Audit\Index as AuditIndex;
use App\Livewire\Outbound\PickingManager;
use App\Livewire\Pembelian\Index as PembelianIndex;
use App\Livewire\Pembelian\Riwayat as PembelianRiwayat;
use App\Livewire\Penjualan\Index as PenjualanIndex;
use App\Livewire\Penjualan\Riwayat as PenjualanRiwayat;
use App\Livewire\PurchaseOrder\Manager as PoManager;
use App\Livewire\Replenishment\Index as ReplenishmentIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\StockMaster\Index as StockMasterIndex;
use App\Livewire\Transfer\Index as TransferIndex;
use App\Models\AnalyticsProduk;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\Lokasi;
use App\Models\OpnameHeader;
use App\Models\Pelanggan;
use App\Models\PickingList;
use App\Models\PoDetail;
use App\Models\PoHeader;
use App\Models\Produk;
use App\Models\StokLokasi;
use App\Models\Supplier;
use App\Models\TransferHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class QueryCountAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Lokasi $gudang;
    private Lokasi $toko;
    private Supplier $supplier;
    private Pelanggan $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first();

        $this->gudang = Lokasi::firstOrCreate(
            ['kode_lokasi' => 'GDG-P01'],
            ['nama_lokasi' => 'Gudang Pusat Perf', 'tipe_lokasi' => 'GUDANG', 'is_active' => true]
        );

        $this->toko = Lokasi::firstOrCreate(
            ['kode_lokasi' => 'TKO-P01'],
            ['nama_lokasi' => 'Toko Cabang Perf', 'tipe_lokasi' => 'TOKO', 'is_active' => true]
        );

        $this->supplier = Supplier::create([
            'nama_supplier' => 'Supplier Perf',
            'kontak' => '0812345678',
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Perf',
            'kontak' => '0898765432',
        ]);
    }

    private function countQueries(callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        return [
            'count' => count($queries),
            'queries' => $queries,
        ];
    }

    /**
     * 1. Stock Master
     */
    public function test_measure_stock_master(): void
    {
        // 5 rows
        for ($i = 1; $i <= 5; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk Perf $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-P5-$i",
                'nama_barang' => "Sepatu Perf $i",
                'variasi_barang' => 'Hitam / 40',
                'satuan' => 'pasang',
                'lokasi' => 'GDG-P01',
                'hpp' => 50000,
                'total_stok' => 10,
            ]);
            StokLokasi::updateOrCreate(['produk_id' => $p->id, 'lokasi_id' => $this->gudang->id], ['qty' => 10]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(StockMasterIndex::class);
        });

        // Add 15 more (total 20 rows)
        for ($i = 6; $i <= 20; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk Perf $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-P20-$i",
                'nama_barang' => "Sepatu Perf $i",
                'variasi_barang' => 'Hitam / 40',
                'satuan' => 'pasang',
                'lokasi' => 'GDG-P01',
                'hpp' => 50000,
                'total_stok' => 10,
            ]);
            StokLokasi::updateOrCreate(['produk_id' => $p->id, 'lokasi_id' => $this->gudang->id], ['qty' => 10]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(StockMasterIndex::class);
        });

        echo "\n[RESULT] Stock Master: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        $this->assertIsInt($res5['count']);
    }

    /**
     * 2. Pembelian (Barang Masuk Index)
     */
    public function test_measure_barang_masuk(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk BM $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-BM-$i",
                'nama_barang' => "Barang BM $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 10000,
                'total_stok' => 10,
            ]);
            $bm = BarangMasuk::create([
                'tanggal' => now()->toDateString(),
                'tipe_nota' => 'Pembelian',
                'supplier_id' => $this->supplier->id,
                'nomor_nota' => "BM-5-$i",
                'data_dimasukan_oleh' => $this->user->name,
            ]);
            BarangMasukDetail::create([
                'barang_masuk_header_id' => $bm->id,
                'produk_id' => $p->id,
                'qty_masuk' => 5,
                'satuan' => 'pcs',
                'lokasi_simpan' => 'GDG-P01',
                'hpp' => 10000,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PembelianIndex::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk BM $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-BM-$i",
                'nama_barang' => "Barang BM $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 10000,
                'total_stok' => 10,
            ]);
            $bm = BarangMasuk::create([
                'tanggal' => now()->toDateString(),
                'tipe_nota' => 'Pembelian',
                'supplier_id' => $this->supplier->id,
                'nomor_nota' => "BM-20-$i",
                'data_dimasukan_oleh' => $this->user->name,
            ]);
            BarangMasukDetail::create([
                'barang_masuk_header_id' => $bm->id,
                'produk_id' => $p->id,
                'qty_masuk' => 5,
                'satuan' => 'pcs',
                'lokasi_simpan' => 'GDG-P01',
                'hpp' => 10000,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PembelianIndex::class);
        });

        echo "\n[RESULT] Barang Masuk Index: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        $this->assertIsInt($res5['count']);
    }

    /**
     * 3. Penjualan (Barang Keluar Index)
     */
    public function test_measure_barang_keluar(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk BK $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-BK-$i",
                'nama_barang' => "Barang BK $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 10000,
                'total_stok' => 10,
            ]);
            $bk = BarangKeluar::create([
                'tanggal' => now()->toDateString(),
                'tipe_nota' => 'Penjualan',
                'pelanggan_id' => $this->pelanggan->id,
                'nomor_nota' => "BK-5-$i",
                'dikeluarkan_oleh' => $this->user->name,
            ]);
            BarangKeluarDetail::create([
                'barang_keluar_header_id' => $bk->id,
                'produk_id' => $p->id,
                'qty_keluar' => 2,
                'satuan' => 'pcs',
                'lokasi_pengambilan' => 'GDG-P01',
                'hpp' => 10000,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PenjualanIndex::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk BK $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-BK-$i",
                'nama_barang' => "Barang BK $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 10000,
                'total_stok' => 10,
            ]);
            $bk = BarangKeluar::create([
                'tanggal' => now()->toDateString(),
                'tipe_nota' => 'Penjualan',
                'pelanggan_id' => $this->pelanggan->id,
                'nomor_nota' => "BK-20-$i",
                'dikeluarkan_oleh' => $this->user->name,
            ]);
            BarangKeluarDetail::create([
                'barang_keluar_header_id' => $bk->id,
                'produk_id' => $p->id,
                'qty_keluar' => 2,
                'satuan' => 'pcs',
                'lokasi_pengambilan' => 'GDG-P01',
                'hpp' => 10000,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PenjualanIndex::class);
        });

        echo "\n[RESULT] Barang Keluar Index: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        $this->assertIsInt($res5['count']);
    }

    /**
     * 4. Transfer Index
     */
    public function test_measure_transfer(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            TransferHeader::create([
                'nomor_transfer' => "TRF-5-$i",
                'tanggal' => now()->toDateString(),
                'lokasi_asal_id' => $this->gudang->id,
                'lokasi_tujuan_id' => $this->toko->id,
                'status' => 'DRAFT',
                'created_by' => $this->user->name,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(TransferIndex::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            TransferHeader::create([
                'nomor_transfer' => "TRF-20-$i",
                'tanggal' => now()->toDateString(),
                'lokasi_asal_id' => $this->gudang->id,
                'lokasi_tujuan_id' => $this->toko->id,
                'status' => 'DRAFT',
                'created_by' => $this->user->name,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(TransferIndex::class);
        });

        echo "\n[RESULT] Transfer Index: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        $this->assertIsInt($res5['count']);
    }

    /**
     * 5. Audit (Opname Index)
     */
    public function test_measure_audit(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            OpnameHeader::create([
                'nomor_opname' => "OPN-5-$i",
                'tanggal' => now()->toDateString(),
                'lokasi_id' => $this->gudang->id,
                'status' => 'DRAFT',
                'operator_name' => $this->user->name,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(AuditIndex::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            OpnameHeader::create([
                'nomor_opname' => "OPN-20-$i",
                'tanggal' => now()->toDateString(),
                'lokasi_id' => $this->gudang->id,
                'status' => 'DRAFT',
                'operator_name' => $this->user->name,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(AuditIndex::class);
        });

        echo "\n[RESULT] Audit Index: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        $this->assertIsInt($res5['count']);
    }

    /**
     * 6. Riwayat Transaksi Barang Masuk (Pembelian Riwayat)
     */
    public function test_measure_riwayat_barang_masuk(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk RBM $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-RBM-$i",
                'nama_barang' => "Barang RBM $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 10000,
                'total_stok' => 10,
            ]);
            $bm = BarangMasuk::create([
                'tanggal' => now()->toDateString(),
                'tipe_nota' => 'Pembelian',
                'supplier_id' => $this->supplier->id,
                'nomor_nota' => "RBM-5-$i",
                'data_dimasukan_oleh' => $this->user->name,
            ]);
            BarangMasukDetail::create([
                'barang_masuk_header_id' => $bm->id,
                'produk_id' => $p->id,
                'qty_masuk' => 5,
                'satuan' => 'pcs',
                'lokasi_simpan' => 'GDG-P01',
                'hpp' => 10000,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PembelianRiwayat::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk RBM $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-RBM-$i",
                'nama_barang' => "Barang RBM $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 10000,
                'total_stok' => 10,
            ]);
            $bm = BarangMasuk::create([
                'tanggal' => now()->toDateString(),
                'tipe_nota' => 'Pembelian',
                'supplier_id' => $this->supplier->id,
                'nomor_nota' => "RBM-20-$i",
                'data_dimasukan_oleh' => $this->user->name,
            ]);
            BarangMasukDetail::create([
                'barang_masuk_header_id' => $bm->id,
                'produk_id' => $p->id,
                'qty_masuk' => 5,
                'satuan' => 'pcs',
                'lokasi_simpan' => 'GDG-P01',
                'hpp' => 10000,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PembelianRiwayat::class);
        });

        echo "\n[RESULT] Riwayat Barang Masuk: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        if (!empty($res5['queries'])) {
            $sampleQueries = array_slice(array_map(fn($q) => $q['query'], $res5['queries']), -5);
            echo "[SAMPLE QUERIES RBM]: " . implode(' | ', $sampleQueries) . "\n";
        }
        $this->assertIsInt($res5['count']);
    }

    /**
     * 7. Riwayat Transaksi Barang Keluar (Penjualan Riwayat)
     */
    public function test_measure_riwayat_barang_keluar(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk RBK $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-RBK-$i",
                'nama_barang' => "Barang RBK $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 10000,
                'total_stok' => 10,
            ]);
            $bk = BarangKeluar::create([
                'tanggal' => now()->toDateString(),
                'tipe_nota' => 'Penjualan',
                'pelanggan_id' => $this->pelanggan->id,
                'nomor_nota' => "RBK-5-$i",
                'dikeluarkan_oleh' => $this->user->name,
            ]);
            BarangKeluarDetail::create([
                'barang_keluar_header_id' => $bk->id,
                'produk_id' => $p->id,
                'qty_keluar' => 2,
                'satuan' => 'pcs',
                'lokasi_pengambilan' => 'GDG-P01',
                'hpp' => 10000,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PenjualanRiwayat::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk RBK $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-RBK-$i",
                'nama_barang' => "Barang RBK $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 10000,
                'total_stok' => 10,
            ]);
            $bk = BarangKeluar::create([
                'tanggal' => now()->toDateString(),
                'tipe_nota' => 'Penjualan',
                'pelanggan_id' => $this->pelanggan->id,
                'nomor_nota' => "RBK-20-$i",
                'dikeluarkan_oleh' => $this->user->name,
            ]);
            BarangKeluarDetail::create([
                'barang_keluar_header_id' => $bk->id,
                'produk_id' => $p->id,
                'qty_keluar' => 2,
                'satuan' => 'pcs',
                'lokasi_pengambilan' => 'GDG-P01',
                'hpp' => 10000,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PenjualanRiwayat::class);
        });

        echo "\n[RESULT] Riwayat Barang Keluar: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        $this->assertIsInt($res5['count']);
    }

    /**
     * 8. Purchase Order Manager (Manager.php)
     */
    public function test_measure_purchase_order(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk PO $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-PO-$i",
                'nama_barang' => "Barang PO $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 20000,
                'total_stok' => 10,
            ]);
            $po = PoHeader::create([
                'tanggal' => now()->toDateString(),
                'nomor_po' => "PO-5-$i",
                'supplier_id' => $this->supplier->id,
                'artikel' => "ART-5-$i",
                'jumlah' => 10,
            ]);
            PoDetail::create([
                'po_header_id' => $po->id,
                'produk_id' => $p->id,
                'jumlah' => 10,
                'barang_masuk' => 0,
                'sisa' => 10,
                'hpp' => 20000,
                'total_hpp' => 200000,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PoManager::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk PO $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-PO-$i",
                'nama_barang' => "Barang PO $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 20000,
                'total_stok' => 10,
            ]);
            $po = PoHeader::create([
                'tanggal' => now()->toDateString(),
                'nomor_po' => "PO-20-$i",
                'supplier_id' => $this->supplier->id,
                'artikel' => "ART-20-$i",
                'jumlah' => 10,
            ]);
            PoDetail::create([
                'po_header_id' => $po->id,
                'produk_id' => $p->id,
                'jumlah' => 10,
                'barang_masuk' => 0,
                'sisa' => 10,
                'hpp' => 20000,
                'total_hpp' => 200000,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PoManager::class);
        });

        echo "\n[RESULT] Purchase Order Manager: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        $this->assertIsInt($res5['count']);
    }

    /**
     * 9. Analytics Index
     */
    public function test_measure_analytics(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk AN $i",
                'kategori' => 'KEMEJA',
                'kode_barang' => "SKU-AN-$i",
                'nama_barang' => "Barang AN $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 30000,
                'total_stok' => 10,
            ]);
            AnalyticsProduk::create([
                'produk_id' => $p->id,
                'kategori' => 'KEMEJA',
                'penjualan_jan' => 10,
                'leadtime_produksi' => 14,
                'minimal_stock' => 5,
                'safety_stock' => 8,
                'maximum_stock' => 15,
                'rata_rata_harian' => 0.5,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(AnalyticsIndex::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk AN $i",
                'kategori' => 'KEMEJA',
                'kode_barang' => "SKU-AN-$i",
                'nama_barang' => "Barang AN $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 30000,
                'total_stok' => 10,
            ]);
            AnalyticsProduk::create([
                'produk_id' => $p->id,
                'kategori' => 'KEMEJA',
                'penjualan_jan' => 10,
                'leadtime_produksi' => 14,
                'minimal_stock' => 5,
                'safety_stock' => 8,
                'maximum_stock' => 15,
                'rata_rata_harian' => 0.5,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(AnalyticsIndex::class);
        });

        echo "\n[RESULT] Analytics Index: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        $this->assertIsInt($res5['count']);
    }

    /**
     * 10. Replenishment Index
     */
    public function test_measure_replenishment(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk REP $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-REP-$i",
                'nama_barang' => "Barang REP $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 40000,
                'total_stok' => 20,
            ]);
            StokLokasi::updateOrCreate(['produk_id' => $p->id, 'lokasi_id' => $this->gudang->id], ['qty' => 20]);
            StokLokasi::updateOrCreate(['produk_id' => $p->id, 'lokasi_id' => $this->toko->id], ['qty' => 1]); // low stock in toko
            AnalyticsProduk::create([
                'produk_id' => $p->id,
                'kategori' => 'SEPATU',
                'minimal_stock' => 10,
                'safety_stock' => 15,
                'maximum_stock' => 30,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(ReplenishmentIndex::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk REP $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-REP-$i",
                'nama_barang' => "Barang REP $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 40000,
                'total_stok' => 20,
            ]);
            StokLokasi::updateOrCreate(['produk_id' => $p->id, 'lokasi_id' => $this->gudang->id], ['qty' => 20]);
            StokLokasi::updateOrCreate(['produk_id' => $p->id, 'lokasi_id' => $this->toko->id], ['qty' => 1]);
            AnalyticsProduk::create([
                'produk_id' => $p->id,
                'kategori' => 'SEPATU',
                'minimal_stock' => 10,
                'safety_stock' => 15,
                'maximum_stock' => 30,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(ReplenishmentIndex::class);
        });

        echo "\n[RESULT] Replenishment Index: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        if (!empty($res5['queries'])) {
            $sampleQueries = array_slice(array_map(fn($q) => $q['query'], $res5['queries']), -5);
            echo "[SAMPLE QUERIES REP]: " . implode(' | ', $sampleQueries) . "\n";
        }
        $this->assertIsInt($res5['count']);
    }

    /**
     * 11. Picking Manager
     */
    public function test_measure_picking_manager(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk PK $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-PK-$i",
                'nama_barang' => "Barang PK $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 10000,
                'total_stok' => 10,
            ]);
            PickingList::create([
                'nomor_picking' => "PCK-5-$i",
                'tanggal' => now()->toDateString(),
                'lokasi_id' => $this->gudang->id,
                'status' => 'PENDING',
                'referensi_tipe' => 'BarangKeluar',
                'referensi_id' => $i,
                'total_items' => 1,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PickingManager::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk PK $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-PK-$i",
                'nama_barang' => "Barang PK $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 10000,
                'total_stok' => 10,
            ]);
            PickingList::create([
                'nomor_picking' => "PCK-20-$i",
                'tanggal' => now()->toDateString(),
                'lokasi_id' => $this->gudang->id,
                'status' => 'PENDING',
                'referensi_tipe' => 'BarangKeluar',
                'referensi_id' => $i,
                'total_items' => 1,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(PickingManager::class);
        });

        echo "\n[RESULT] Picking Manager: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        $this->assertIsInt($res5['count']);
    }

    /**
     * 12. Reports Index
     */
    public function test_measure_reports(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Produk::create([
                'nama_produk' => "Produk REP $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-RPT-$i",
                'nama_barang' => "Barang RPT $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 15000,
                'total_stok' => 10,
            ]);
        }

        $res5 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(ReportsIndex::class);
        });

        for ($i = 6; $i <= 20; $i++) {
            Produk::create([
                'nama_produk' => "Produk REP $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-RPT-$i",
                'nama_barang' => "Barang RPT $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => 'GDG-P01',
                'hpp' => 15000,
                'total_stok' => 10,
            ]);
        }

        $res20 = $this->countQueries(function () {
            Livewire::actingAs($this->user)->test(ReportsIndex::class);
        });

        echo "\n[RESULT] Reports Index: 5 rows = {$res5['count']} queries, 20 rows = {$res20['count']} queries\n";
        if (!empty($res5['queries'])) {
            $sampleQueries = array_slice(array_map(fn($q) => $q['query'], $res5['queries']), -5);
            echo "[SAMPLE QUERIES REPORTS]: " . implode(' | ', $sampleQueries) . "\n";
        }
        $this->assertIsInt($res5['count']);
    }
}
