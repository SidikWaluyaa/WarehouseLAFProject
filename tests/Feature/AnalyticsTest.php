<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\AnalyticsProduk;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Analytics\Index as AnalyticsIndex;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Produk $produk;
    private AnalyticsProduk $analytics;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first(); // Super Admin

        $this->produk = Produk::first();
        $this->analytics = AnalyticsProduk::where(['produk_id' => $this->produk->id])->first();
    }

    public function test_analytics_page_requires_authentication(): void
    {
        $response = $this->get('/analytics');
        $response->assertRedirect('/login');
    }

    public function test_can_render_analytics_page_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get('/analytics');
        $response->assertStatus(200);
        $response->assertSee('Analisis & Peramalan Stok', false);
    }

    public function test_can_save_manual_sales_inputs(): void
    {
        Livewire::actingAs($this->user)
            ->test(AnalyticsIndex::class)
            ->call('edit', $this->analytics->id)
            ->set('sales.jan', 100)
            ->set('sales.feb', 120)
            ->set('sales.mar', 80)
            ->set('sales.apr', 150)
            ->set('sales.mei', 200)
            ->set('sales.jun', 90)
            ->set('sales.jul', 110)
            ->set('sales.ags', 130)
            ->set('sales.sep', 100)
            ->set('sales.okt', 140)
            ->set('sales.nov', 110)
            ->set('sales.des', 170)
            ->set('leadtime_produksi', 20)
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('isOpen', false);

        $this->analytics->refresh();
        $this->assertEquals(20, $this->analytics->leadtime_produksi);
        $this->assertEquals(100, $this->analytics->penjualan_jan);
        $this->assertEquals(170, $this->analytics->penjualan_des);

        // Sum of all = 100+120+80+150+200+90+110+130+100+140+110+170 = 1500
        // Rata-rata harian = 1500 / 365 = 4.11
        $this->assertEquals(round(1500 / 365, 2), round($this->analytics->rata_rata_harian, 2));

        // Min stock = ceil(4.11 * 20) = ceil(82.2) = 83
        $this->assertEquals(83, $this->analytics->minimal_stock);

        // Safety stock = ceil(83 * 1.5) = 125
        $this->assertEquals(125, $this->analytics->safety_stock);

        // Max stock = ceil(125 * 1.5) = 188
        $this->assertEquals(188, $this->analytics->maximum_stock);
    }

    public function test_sync_sales_from_database_transactions(): void
    {
        // Let's create actual sales transaction in database for this product
        $headerJan = BarangKeluar::create([
            'tanggal' => '2026-01-15',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => 1,
            'nomor_nota' => 'BK/20260115/0001',
            'dikeluarkan_oleh' => 'Test',
        ]);
        
        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $headerJan->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 45,
            'satuan' => 'pasang',
            'hpp' => 20000,
        ]);

        $headerDec = BarangKeluar::create([
            'tanggal' => '2026-12-10',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => 1,
            'nomor_nota' => 'BK/20261210/0001',
            'dikeluarkan_oleh' => 'Test',
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $headerDec->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 55,
            'satuan' => 'pasang',
            'hpp' => 20000,
        ]);

        Livewire::actingAs($this->user)
            ->test(AnalyticsIndex::class)
            ->call('edit', $this->analytics->id)
            ->call('syncFromDatabase')
            ->assertSet('sales.jan', 45)
            ->assertSet('sales.des', 55)
            ->assertSet('sales.feb', 0);
    }

    public function test_recalculate_all_syncs_and_recalculates_for_all_products(): void
    {
        // 1. Create actual sales transaction in database for this product (100 units in January)
        $headerJan = BarangKeluar::create([
            'tanggal' => '2026-01-20',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => 1,
            'nomor_nota' => 'BK/20260120/0001',
            'dikeluarkan_oleh' => 'Test',
        ]);
        
        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $headerJan->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 100,
            'satuan' => 'pasang',
            'hpp' => 20000,
        ]);

        // 2. Call the recalculateAll method through Livewire Component
        Livewire::actingAs($this->user)
            ->test(AnalyticsIndex::class)
            ->call('recalculateAll')
            ->assertHasNoErrors();

        // 3. Verify that the database record updated its monthly sales and recalculated metrics
        $this->analytics->refresh();
        $this->assertEquals(100, $this->analytics->penjualan_jan);
        $this->assertEquals(0, $this->analytics->penjualan_feb);
        $this->assertEquals(round(100 / 365, 2), round($this->analytics->rata_rata_harian, 2));
    }

    public function test_can_save_and_preserve_manual_stock_levels(): void
    {
        // 1. Save manual stock levels via Livewire component
        Livewire::actingAs($this->user)
            ->test(AnalyticsIndex::class)
            ->call('edit', $this->analytics->id)
            ->set('is_manual_stock', true)
            ->set('minimal_stock', 50)
            ->set('safety_stock', 75)
            ->set('maximum_stock', 110)
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('isOpen', false);

        // Verify they are saved in the database
        $this->analytics->refresh();
        $this->assertTrue((bool)$this->analytics->is_manual_stock);
        $this->assertEquals(50, $this->analytics->minimal_stock);
        $this->assertEquals(75, $this->analytics->safety_stock);
        $this->assertEquals(110, $this->analytics->maximum_stock);

        // 2. Perform recalculateAll and make sure they are NOT overwritten by the formula
        Livewire::actingAs($this->user)
            ->test(AnalyticsIndex::class)
            ->call('recalculateAll')
            ->assertHasNoErrors();

        $this->analytics->refresh();
        // Still preserved!
        $this->assertTrue((bool)$this->analytics->is_manual_stock);
        $this->assertEquals(50, $this->analytics->minimal_stock);
        $this->assertEquals(75, $this->analytics->safety_stock);
        $this->assertEquals(110, $this->analytics->maximum_stock);
    }

    public function test_tambah_barang_keluar_updates_analytics_reactively(): void
    {
        // 1. Initially sales are 0
        $this->assertEquals(0, $this->analytics->penjualan_jun);

        // 2. Perform a tambahBarangKeluar transaction for this product in June (current month is June)
        $data = [
            'tanggal' => '2026-06-24',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => 1,
            'nomor_nota' => 'BK/20260624/0002',
            'dikeluarkan_oleh' => 'Test Operator',
            'items' => [
                [
                    'produk_id' => $this->produk->id,
                    'qty_keluar' => 15,
                    'lokasi_pengambilan' => 'GUDANG',
                ]
            ]
        ];

        // Seed initial stock so the transaction doesn't fail validation
        $lokasi = \App\Models\Lokasi::create([
            'kode_lokasi' => 'GUDANG',
            'nama_lokasi' => 'Gudang Utama',
            'tipe_lokasi' => 'GUDANG',
            'is_active' => true,
        ]);
        
        \App\Models\StokLokasi::create([
            'produk_id' => $this->produk->id,
            'lokasi_id' => $lokasi->id,
            'qty' => 100,
        ]);

        $this->produk->total_stok = 100;
        $this->produk->save();

        \App\Services\StockEngineService::tambahBarangKeluar($data);

        // 3. Verify that the analytics record updated its June sales reactively
        $this->analytics->refresh();
        $this->assertEquals(15, $this->analytics->penjualan_jun);
        
        // Rata-rata harian = 15 / 365 = 0.041
        // Min stock = ceil(0.041 * 14) = ceil(0.575) = 1
        $this->assertEquals(1, $this->analytics->minimal_stock);
    }

    public function test_abc_classification_calculation_and_caching(): void
    {
        // Clear existing database records to isolate ABC classification math
        \App\Models\AnalyticsProduk::query()->delete();
        Produk::query()->delete();

        // 1. Create three products with different sales levels
        $productA = Produk::create([
            'kode_barang' => 'PROD-ABC-A',
            'nama_produk' => 'Product ABC A',
            'nama_barang' => 'Product ABC A Display',
            'kategori' => 'Sandal Dewasa',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-A01',
            'hpp' => 10000,
        ]);

        $productB = Produk::create([
            'kode_barang' => 'PROD-ABC-B',
            'nama_produk' => 'Product ABC B',
            'nama_barang' => 'Product ABC B Display',
            'kategori' => 'Sandal Dewasa',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-A01',
            'hpp' => 10000,
        ]);

        $productC = Produk::create([
            'kode_barang' => 'PROD-ABC-C',
            'nama_produk' => 'Product ABC C',
            'nama_barang' => 'Product ABC C Display',
            'kategori' => 'Sandal Dewasa',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-A01',
            'hpp' => 10000,
        ]);

        $productD = Produk::create([
            'kode_barang' => 'PROD-ABC-D',
            'nama_produk' => 'Product ABC D',
            'nama_barang' => 'Product ABC D Display',
            'kategori' => 'Sandal Dewasa',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-A01',
            'hpp' => 10000,
        ]);

        // Create analytics records
        $analyticsA = AnalyticsProduk::create(['produk_id' => $productA->id, 'kategori' => 'Sandal Dewasa', 'penjualan_jan' => 900]);
        $analyticsB = AnalyticsProduk::create(['produk_id' => $productB->id, 'kategori' => 'Sandal Dewasa', 'penjualan_jan' => 180]);
        $analyticsC = AnalyticsProduk::create(['produk_id' => $productC->id, 'kategori' => 'Sandal Dewasa', 'penjualan_jan' => 120]);
        $analyticsD = AnalyticsProduk::create(['produk_id' => $productD->id, 'kategori' => 'Sandal Dewasa', 'penjualan_jan' => 0]);

        // Run ABC classifications recalculation
        \App\Services\AnalyticsService::recalculateAbcClassifications();

        // Refresh and assert
        $analyticsA->refresh();
        $analyticsB->refresh();
        $analyticsC->refresh();
        $analyticsD->refresh();

        $this->assertEquals('A', $analyticsA->abc_class);
        $this->assertEquals('B', $analyticsB->abc_class);
        $this->assertEquals('C', $analyticsC->abc_class);
        $this->assertEquals('C', $analyticsD->abc_class);
    }
}
