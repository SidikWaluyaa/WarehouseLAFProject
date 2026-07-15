<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Lokasi;
use App\Models\Supplier;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $unauthorizedUser;
    private Produk $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Spatie Roles & Permissions if needed
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com'
        ]);
        $this->adminUser->assignRole($adminRole);

        $this->unauthorizedUser = User::factory()->create([
            'email' => 'guest@test.com'
        ]);

        // Create initial locations
        $lokasiGudang = Lokasi::firstOrCreate(
            ['kode_lokasi' => 'GDG-A01'],
            ['nama_lokasi' => 'Gudang Utama A1', 'tipe_lokasi' => 'GUDANG', 'is_active' => true]
        );

        // Create product
        $this->product = Produk::create([
            'kode_barang' => 'SDL-SLI-BLK',
            'nama_produk' => 'Sandal Slide Black',
            'nama_barang' => 'Sandal Slide Hitam Kasual',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'hpp' => 25000.00,
            'total_stok' => 50,
            'lokasi' => 'GDG-A01',
            'kategori' => 'SANDAL',
            'kategori_2' => 'Produk Berjalan',
            'is_active' => true,
        ]);

        // Create a seeder supplier
        $supplier = Supplier::create([
            'nama_supplier' => 'PT Sandal Indo Jaya',
            'kontak' => '08123456789',
            'alamat' => 'Semarang'
        ]);

        // Add a goods receipt note (barang masuk)
        $bm = BarangMasuk::create([
            'tanggal' => now()->subDays(10)->format('Y-m-d'),
            'tipe_nota' => 'Stock Awal',
            'supplier_id' => $supplier->id,
            'nomor_nota' => 'SA-1001',
            'keterangan_tambahan' => 'Stock Awal',
            'data_dimasukan_oleh' => 'Super Admin',
        ]);
        $bm->details()->create([
            'produk_id' => $this->product->id,
            'qty_masuk' => 50,
            'satuan' => 'pasang',
            'lokasi_simpan' => 'GDG-A01',
            'hpp' => 25000.00,
        ]);

        \App\Models\MutasiLokasi::create([
            'produk_id' => $this->product->id,
            'lokasi_asal_id' => null,
            'lokasi_tujuan_id' => $lokasiGudang->id,
            'qty' => 50,
            'tipe_mutasi' => 'MASUK',
            'referensi_tipe' => 'BarangMasuk',
            'referensi_id' => $bm->id,
            'keterangan' => 'Stock Awal',
            'data_dimasukan_oleh' => 'Super Admin',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);
    }

    public function test_reports_page_requires_authentication()
    {
        $response = $this->get(route('reports'));
        $response->assertRedirect('/login');
    }

    public function test_authorized_user_can_render_reports_page()
    {
        $response = $this->actingAs($this->adminUser)->get(route('reports'));
        $response->assertStatus(200);
        $response->assertSeeLivewire(\App\Livewire\Reports\Index::class);
    }

    public function test_reports_livewire_can_switch_tabs_and_run_queries()
    {
        Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\Reports\Index::class)
            ->assertSet('reportType', 'stock-card')
            
            // Test stock card generation
            ->set('sc_produk_id', $this->product->id)
            ->call('generateStockCard')
            ->assertSet('stockCardData.saldo_akhir', 50)
            
            // Switch tab to stock movement
            ->set('reportType', 'stock-movement')
            ->set('sm_date_from', now()->subDays(15)->format('Y-m-d'))
            ->call('generateMovement')
            ->assertCount('movementData', 1)

            // Switch to aging
            ->set('reportType', 'aging-stock')
            ->call('generateAgingStock')
            ->assertCount('agingData', 1)

            // Switch to sales support
            ->set('reportType', 'sales-support')
            ->call('generateSalesSupport')
            ->assertSet('transferFillRate', 100.0)

            // Switch to warehouse performance
            ->set('reportType', 'warehouse-performance')
            ->call('generateWarehousePerformance')
            ->assertSet('receivingStats.total_qty', 50);
    }

    public function test_reports_livewire_csv_exports()
    {
        // 1. Stock Card Export
        $response = Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\Reports\Index::class)
            ->set('sc_produk_id', $this->product->id)
            ->call('exportCsv', 'stock-card');
        
        $response->assertFileDownloaded('stock_card_' . $this->product->kode_barang . '.csv');

        // 2. Aging Stock Export
        $response = Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\Reports\Index::class)
            ->call('exportCsv', 'aging-stock');
        
        $response->assertFileDownloaded('aging_stock_' . now()->format('Ymd') . '.csv');

        // 3. Sales Support Export
        $response = Livewire::actingAs($this->adminUser)
            ->test(\App\Livewire\Reports\Index::class)
            ->call('exportCsv', 'sales-support');
        
        $response->assertFileDownloaded('sales_support_' . now()->format('Ymd') . '.csv');
    }
}
