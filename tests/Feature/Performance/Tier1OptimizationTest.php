<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\Lokasi;
use App\Models\StokLokasi;
use App\Models\AnalyticsProduk;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\Supplier;
use App\Services\ReplenishmentService;
use App\Livewire\Reports\Index as ReportsIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Carbon\Carbon;

class Tier1OptimizationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Lokasi $gudang;
    private Lokasi $toko;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->gudang = Lokasi::where('kode_lokasi', 'GDG-A01')->first();
        $this->toko = Lokasi::where('kode_lokasi', 'TKO-R01')->first();

        // Nonaktifkan lokasi lain agar tes deterministik
        Lokasi::whereNotIn('id', [$this->gudang->id, $this->toko->id])->update(['is_active' => false]);

        $this->supplier = Supplier::create([
            'nama_supplier' => 'Supplier Tier 1',
            'kontak' => '08123456789',
        ]);
    }

    private function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    /**
     * 1. CHARACTERIZATION TEST - ReplenishmentService
     * Menguji dan mengunci output deterministik pada kode LAMA dan BARU.
     */
    public function test_characterization_replenishment_service(): void
    {
        // Produk 1: Ada di toko, butuh replenishment (urgency TINGGI)
        $p1 = Produk::create([
            'nama_produk' => 'Sepatu P1',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SKU-T1-1',
            'nama_barang' => 'Sepatu P1',
            'variasi_barang' => '40',
            'satuan' => 'pasang',
            'lokasi' => $this->gudang->kode_lokasi,
            'hpp' => 50000,
            'total_stok' => 32,
        ]);
        StokLokasi::updateOrCreate(['produk_id' => $p1->id, 'lokasi_id' => $this->gudang->id], ['qty' => 30]);
        StokLokasi::updateOrCreate(['produk_id' => $p1->id, 'lokasi_id' => $this->toko->id], ['qty' => 2]);
        AnalyticsProduk::create([
            'produk_id' => $p1->id,
            'kategori' => 'SEPATU',
            'minimal_stock' => 10,
            'maximum_stock' => 25,
            'safety_stock' => 5,
        ]);

        // Produk 2: Tidak ada stok di toko (qty 0), butuh replenishment (urgency KRITIS)
        $p2 = Produk::create([
            'nama_produk' => 'Baju P2',
            'kategori' => 'BAJU',
            'kode_barang' => 'SKU-T1-2',
            'nama_barang' => 'Baju P2',
            'variasi_barang' => 'L',
            'satuan' => 'pcs',
            'lokasi' => $this->gudang->kode_lokasi,
            'hpp' => 30000,
            'total_stok' => 10,
        ]);
        StokLokasi::updateOrCreate(['produk_id' => $p2->id, 'lokasi_id' => $this->gudang->id], ['qty' => 10]);
        // Tidak ada record stok di toko untuk p2
        AnalyticsProduk::create([
            'produk_id' => $p2->id,
            'kategori' => 'BAJU',
            'minimal_stock' => 15,
            'maximum_stock' => 30,
            'safety_stock' => 5,
        ]);

        // Produk 3: Stok cukup, tidak perlu replenishment
        $p3 = Produk::create([
            'nama_produk' => 'Topi P3',
            'kategori' => 'TOPI',
            'kode_barang' => 'SKU-T1-3',
            'nama_barang' => 'Topi P3',
            'variasi_barang' => 'All Size',
            'satuan' => 'pcs',
            'lokasi' => $this->gudang->kode_lokasi,
            'hpp' => 20000,
            'total_stok' => 25,
        ]);
        StokLokasi::updateOrCreate(['produk_id' => $p3->id, 'lokasi_id' => $this->gudang->id], ['qty' => 20]);
        StokLokasi::updateOrCreate(['produk_id' => $p3->id, 'lokasi_id' => $this->toko->id], ['qty' => 12]);
        AnalyticsProduk::create([
            'produk_id' => $p3->id,
            'kategori' => 'TOPI',
            'minimal_stock' => 10,
            'maximum_stock' => 20,
            'safety_stock' => 5,
        ]);

        $results = ReplenishmentService::getReplenishmentRecommendations();

        // Verifikasi ketat
        $this->assertCount(2, $results);

        // Item 0: Urgency KRITIS (p2)
        $item0 = $results[0];
        $this->assertEquals($p2->id, $item0->produk_id);
        $this->assertEquals('KRITIS', $item0->urgency);
        $this->assertEquals(0, $item0->current_stock);
        $this->assertEquals(15, $item0->minimal_stock);
        $this->assertEquals(30, $item0->maximum_stock);
        $this->assertEquals(30, $item0->replenishment_qty);
        $this->assertEquals(10, $item0->gudang_stock_available);
        $this->assertEquals(10, $item0->feasible_qty);

        // Item 1: Urgency TINGGI (p1)
        $item1 = $results[1];
        $this->assertEquals($p1->id, $item1->produk_id);
        $this->assertEquals('TINGGI', $item1->urgency);
        $this->assertEquals(2, $item1->current_stock);
        $this->assertEquals(10, $item1->minimal_stock);
        $this->assertEquals(25, $item1->maximum_stock);
        $this->assertEquals(23, $item1->replenishment_qty);
        $this->assertEquals(30, $item1->gudang_stock_available);
        $this->assertEquals(23, $item1->feasible_qty);
    }

    /**
     * 2. CHARACTERIZATION TEST - Reports Aging Stock
     * Menguji dan mengunci output deterministik pada kode LAMA dan BARU.
     */
    public function test_characterization_reports_aging_stock(): void
    {
        Carbon::setTestNow('2026-10-23 00:00:00');

        // P1: Barang masuk 10 hari lalu -> Fresh Stock
        $p1 = Produk::create([
            'nama_produk' => 'Produk Fresh',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SKU-REP-1',
            'nama_barang' => 'Produk Fresh',
            'variasi_barang' => 'Default',
            'satuan' => 'pcs',
            'lokasi' => $this->gudang->kode_lokasi,
            'hpp' => 50000,
            'total_stok' => 10,
            'created_at' => Carbon::now()->subDays(100),
        ]);
        StokLokasi::updateOrCreate(['produk_id' => $p1->id, 'lokasi_id' => $this->gudang->id], ['qty' => 10]);

        $bm1 = BarangMasuk::create([
            'nomor_masuk' => 'BM-T1-1',
            'tanggal' => Carbon::now()->subDays(10)->toDateString(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'lokasi_id' => $this->gudang->id,
            'status' => 'COMPLETED',
            'data_dimasukan_oleh' => 'TestRunner',
        ]);
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bm1->id,
            'produk_id' => $p1->id,
            'qty_masuk' => 10,
            'satuan' => 'pcs',
            'hpp' => 50000,
            'subtotal' => 500000,
        ]);

        // P2: Barang masuk 45 hari lalu -> Slow Moving
        $p2 = Produk::create([
            'nama_produk' => 'Produk Slow',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SKU-REP-2',
            'nama_barang' => 'Produk Slow',
            'variasi_barang' => 'Default',
            'satuan' => 'pcs',
            'lokasi' => $this->gudang->kode_lokasi,
            'hpp' => 60000,
            'total_stok' => 5,
            'created_at' => Carbon::now()->subDays(80),
        ]);
        StokLokasi::updateOrCreate(['produk_id' => $p2->id, 'lokasi_id' => $this->gudang->id], ['qty' => 5]);

        $bm2 = BarangMasuk::create([
            'nomor_masuk' => 'BM-T1-2',
            'tanggal' => Carbon::now()->subDays(45)->toDateString(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'lokasi_id' => $this->gudang->id,
            'status' => 'COMPLETED',
            'data_dimasukan_oleh' => 'TestRunner',
        ]);
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bm2->id,
            'produk_id' => $p2->id,
            'qty_masuk' => 5,
            'satuan' => 'pcs',
            'hpp' => 60000,
            'subtotal' => 300000,
        ]);

        // P3: Tidak ada barang masuk, created_at 120 hari lalu -> Dead Stock
        $p3 = Produk::create([
            'nama_produk' => 'Produk Dead',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SKU-REP-3',
            'nama_barang' => 'Produk Dead',
            'variasi_barang' => 'Default',
            'satuan' => 'pcs',
            'lokasi' => $this->gudang->kode_lokasi,
            'hpp' => 40000,
            'total_stok' => 8,
        ]);
        $p3->timestamps = false;
        $p3->created_at = Carbon::now()->subDays(120);
        $p3->saveQuietly();
        StokLokasi::updateOrCreate(['produk_id' => $p3->id, 'lokasi_id' => $this->gudang->id], ['qty' => 8]);

        $component = Livewire::actingAs($this->user)->test(ReportsIndex::class);
        $component->call('generateAgingStock');
        $agingData = $component->get('agingData');

        $this->assertCount(3, $agingData);

        // Cari item berdasarkan id
        $item1 = collect($agingData)->firstWhere('id', $p1->id);
        $this->assertNotNull($item1);
        $this->assertEquals('SKU-REP-1', $item1['kode_barang']);
        $this->assertEquals(10, $item1['total_qty']);
        $this->assertEquals(50000, $item1['hpp']);
        $this->assertEquals(500000, $item1['total_nilai']);
        $this->assertEquals(Carbon::now()->subDays(10)->format('d/m/Y'), $item1['tanggal_terakhir']);
        $this->assertEquals(10, $item1['usia_hari']);
        $this->assertEquals('Fresh Stock', $item1['kategori_usia']);

        $item2 = collect($agingData)->firstWhere('id', $p2->id);
        $this->assertNotNull($item2);
        $this->assertEquals('SKU-REP-2', $item2['kode_barang']);
        $this->assertEquals(5, $item2['total_qty']);
        $this->assertEquals(45, $item2['usia_hari']);
        $this->assertEquals('Slow Moving', $item2['kategori_usia']);

        $item3 = collect($agingData)->firstWhere('id', $p3->id);
        $this->assertNotNull($item3);
        $this->assertEquals('SKU-REP-3', $item3['kode_barang']);
        $this->assertEquals(8, $item3['total_qty']);
        $this->assertEquals(120, $item3['usia_hari']);
        $this->assertEquals('Dead Stock', $item3['kategori_usia']);

        Carbon::setTestNow();
    }

    /**
     * 3. UKUR QUERY: Replenishment (Warming + 2, 10, 40 baris)
     */
    public function test_query_count_replenishment(): void
    {
        // 1 render pemanasan
        $this->seedReplenishmentProducts(1);
        ReplenishmentService::getReplenishmentRecommendations();

        // 2 baris data
        $this->seedReplenishmentProducts(2);
        $q2 = $this->countQueries(function () {
            ReplenishmentService::getReplenishmentRecommendations();
        });

        // 10 baris data
        $this->seedReplenishmentProducts(10);
        $q10 = $this->countQueries(function () {
            ReplenishmentService::getReplenishmentRecommendations();
        });

        // 40 baris data
        $this->seedReplenishmentProducts(40);
        $q40 = $this->countQueries(function () {
            ReplenishmentService::getReplenishmentRecommendations();
        });

        echo "\n[MEASURE-REPLENISHMENT] 2 rows = $q2, 10 rows = $q10, 40 rows = $q40\n";
        $this->assertLessThanOrEqual(1, abs($q40 - $q10), "Query count replenishment untuk 10 baris ($q10) dan 40 baris ($q40) harus konstan.");
        $this->assertLessThanOrEqual(10, $q40, "Query count replenishment untuk 40 baris ($q40) harus di bawah ambang batas konstan.");
    }

    /**
     * 4. UKUR QUERY: Reports Aging Stock (Warming + 2, 10, 40 baris)
     */
    public function test_query_count_reports_aging(): void
    {
        // 1 render pemanasan
        $this->seedAgingProducts(1);
        $comp = Livewire::actingAs($this->user)->test(ReportsIndex::class);
        $comp->call('generateAgingStock');

        // 2 baris data
        $this->seedAgingProducts(2);
        $comp = Livewire::actingAs($this->user)->test(ReportsIndex::class);
        $q2 = $this->countQueries(function () use ($comp) {
            $comp->call('generateAgingStock');
        });

        // 10 baris data
        $this->seedAgingProducts(10);
        $comp = Livewire::actingAs($this->user)->test(ReportsIndex::class);
        $q10 = $this->countQueries(function () use ($comp) {
            $comp->call('generateAgingStock');
        });

        // 40 baris data
        $this->seedAgingProducts(40);
        $comp = Livewire::actingAs($this->user)->test(ReportsIndex::class);
        $q40 = $this->countQueries(function () use ($comp) {
            $comp->call('generateAgingStock');
        });

        echo "\n[MEASURE-REPORTS-AGING] 2 rows = $q2, 10 rows = $q10, 40 rows = $q40\n";
        $this->assertLessThanOrEqual(1, abs($q40 - $q10), "Query count reports aging untuk 10 baris ($q10) dan 40 baris ($q40) harus konstan.");
        $this->assertLessThanOrEqual(10, $q40, "Query count reports aging untuk 40 baris ($q40) harus di bawah ambang batas konstan.");
    }

    private function seedReplenishmentProducts(int $targetTotal): void
    {
        $currentCount = Produk::count();
        for ($i = $currentCount + 1; $i <= $targetTotal; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk QC $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-QC-$i",
                'nama_barang' => "Barang QC $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => $this->gudang->kode_lokasi,
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
    }

    private function seedAgingProducts(int $targetTotal): void
    {
        $currentCount = Produk::count();
        for ($i = $currentCount + 1; $i <= $targetTotal; $i++) {
            $p = Produk::create([
                'nama_produk' => "Produk AG $i",
                'kategori' => 'SEPATU',
                'kode_barang' => "SKU-AG-$i",
                'nama_barang' => "Barang AG $i",
                'variasi_barang' => 'Default',
                'satuan' => 'pcs',
                'lokasi' => $this->gudang->kode_lokasi,
                'hpp' => 50000,
                'total_stok' => 10,
            ]);
            StokLokasi::updateOrCreate(['produk_id' => $p->id, 'lokasi_id' => $this->gudang->id], ['qty' => 10]);

            $bm = BarangMasuk::create([
                'nomor_masuk' => "BM-AG-$i",
                'tanggal' => now()->subDays($i)->toDateString(),
                'tipe_nota' => 'Pembelian',
                'supplier_id' => $this->supplier->id,
                'lokasi_id' => $this->gudang->id,
                'status' => 'COMPLETED',
                'data_dimasukan_oleh' => 'TestRunner',
            ]);
            BarangMasukDetail::create([
                'barang_masuk_header_id' => $bm->id,
                'produk_id' => $p->id,
                'qty_masuk' => 10,
                'satuan' => 'pcs',
                'hpp' => 50000,
                'subtotal' => 500000,
            ]);
        }
    }
}
