<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\PoDetail;
use App\Models\PoHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Exports\ProductsTemplateExport;

class ProductsExcelTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed database which sets up roles, permissions, and demo users
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        
        // Grab the seeded Super Admin user (which has 'manage master' permission)
        $this->user = User::first();
    }

    public function test_template_download(): void
    {
        $this->actingAs($this->user);

        Excel::fake();

        $response = $this->get('/stock-master');
        $response->assertOk();

        // Trigger template download via livewire component
        $component = Livewire::actingAs($this->user)->test(\App\Livewire\StockMaster\Index::class);
        $component->call('downloadTemplate');

        Excel::assertDownloaded('template_produk_wms.xlsx', function (ProductsTemplateExport $export) {
            $array = $export->array();
            return isset($array[0][7]) && $array[0][7] === 'GDG-A01';
        });
    }

    public function test_excel_export(): void
    {
        $this->actingAs($this->user);

        \Illuminate\Support\Carbon::setTestNow('2026-06-24 15:40:00');

        Excel::fake();

        $component = Livewire::actingAs($this->user)->test(\App\Livewire\StockMaster\Index::class);
        $component->call('exportExcel');

        Excel::assertDownloaded('stock_master_' . now()->format('Ymd_His') . '.xlsx', function (ProductsExport $export) {
            return true;
        });

        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_excel_import_valid_data(): void
    {
        $this->actingAs($this->user);

        // Ensure category rules exist for seeding/mapping
        \App\Models\AturanKode::create([
            'kategori' => 'TEST-CAT',
            'prefix' => 'SandalSlide',
            'format_tipe' => 'PRD_STYLE',
            'is_active' => true,
        ]);

        $import = new \App\Imports\ProductsImport();
        $rows = collect([
            [
                'kode_barang' => '', // Empty: should auto-generate
                'nama_produk' => 'Gorka Hitam 36',
                'nama_display' => 'Gorka Hitam 36',
                'kategori' => 'TEST-CAT',
                'warna' => 'Hitam',
                'size' => '36',
                'satuan' => 'pcs',
                'lokasi_default' => 'GUDANG',
                'hpp_per_pcs' => 10000,
                'harga_jual' => 15000,
                'stok_awal' => 50,
            ],
            [
                'kode_barang' => 'TEST-SKU-CUSTOM', // Custom provided
                'nama_produk' => 'Test Product 2',
                'nama_display' => 'Test Display Name 2',
                'kategori' => 'TEST-CAT',
                'warna' => 'Biru',
                'size' => '42',
                'satuan' => 'pcs',
                'lokasi_default' => 'TOKO',
                'hpp_per_pcs' => 20000,
                'harga_jual' => 30000,
                'stok_awal' => 0,
            ]
        ]);

        $import->collection($rows);

        $this->assertEquals(2, $import->importedCount);
        $this->assertDatabaseHas('mst_produk', [
            'kode_barang' => 'SandalSlide-Gorka-FullBlack-36', // Auto-generated
            'total_stok' => 50,
            'lokasi' => 'GDG-A01',
            'harga_jual' => 15000,
        ]);
        $this->assertDatabaseHas('mst_produk', [
            'kode_barang' => 'TEST-SKU-CUSTOM', // Custom SKU preserved
            'total_stok' => 0,
            'lokasi' => 'TKO-R01',
            'harga_jual' => 30000,
        ]);

        // Assert transaction was logged for stok_awal > 0
        $this->assertDatabaseHas('trx_barang_masuk_header', [
            'tipe_nota' => 'Stock Awal',
            'keterangan_tambahan' => 'Import Awal via Excel',
        ]);
    }

    public function test_excel_import_validation_fails(): void
    {
        $this->actingAs($this->user);

        $import = new \App\Imports\ProductsImport();
        
        // Invalid data: missing fields, invalid lokasi, negative HPP
        $rows = collect([
            [
                'kode_barang' => '',
                'nama_produk' => 'Test Product',
                'nama_display' => 'Test Display Name',
                'kategori' => 'TEST-CAT',
                'warna' => '', // Required field missing
                'size' => '39',
                'satuan' => 'pcs',
                'lokasi_default' => 'WARUNG',
                'hpp_per_pcs' => -5000,
                'harga_jual' => 15000,
                'stok_awal' => 50,
            ]
        ]);

        try {
            $import->collection($rows);
            $this->fail("Expected Exception not thrown");
        } catch (\Exception $e) {
            $this->assertCount(3, $import->errorsList);
            $this->assertStringContainsString('Warna', $import->errorsList[0]);
            $this->assertStringContainsString('Lokasi Default', $import->errorsList[1]);
            $this->assertStringContainsString('HPP per Pcs', $import->errorsList[2]);
        }
    }

    public function test_excel_import_duplicate_skus_in_file_fails(): void
    {
        $this->actingAs($this->user);

        $import = new \App\Imports\ProductsImport();
        
        // Two products with duplicate SKU in the uploaded Excel itself
        $rows = collect([
            [
                'kode_barang' => 'TEST-SKU-DUP',
                'nama_produk' => 'Test Product 1',
                'nama_display' => 'Test Display Name 1',
                'kategori' => 'TEST-CAT',
                'warna' => 'Hitam',
                'size' => '40',
                'satuan' => 'pcs',
                'lokasi_default' => 'GUDANG',
                'hpp_per_pcs' => 10000,
                'harga_jual' => 15000,
                'stok_awal' => 10,
            ],
            [
                'kode_barang' => 'TEST-SKU-DUP', // Duplicate SKU
                'nama_produk' => 'Test Product 2',
                'nama_display' => 'Test Display Name 2',
                'kategori' => 'TEST-CAT',
                'warna' => 'Putih',
                'size' => '41',
                'satuan' => 'pcs',
                'lokasi_default' => 'GUDANG',
                'hpp_per_pcs' => 12000,
                'harga_jual' => 18000,
                'stok_awal' => 5,
            ]
        ]);

        try {
            $import->collection($rows);
            $this->fail("Expected Exception not thrown");
        } catch (\Exception $e) {
            $this->assertGreaterThanOrEqual(1, count($import->errorsList));
            $this->assertStringContainsString('Terdapat Kode Barang duplikat', $import->errorsList[0]);
        }
    }

    public function test_excel_import_no_supplier_with_initial_stock_succeeds(): void
    {
        $this->actingAs($this->user);

        // Delete all suppliers to simulate the scenario (clear dependencies first)
        BarangMasukDetail::query()->delete();
        BarangMasuk::query()->delete();
        PoDetail::query()->delete();
        PoHeader::query()->delete();
        Supplier::query()->delete();

        $import = new \App\Imports\ProductsImport();
        $rows = collect([
            [
                'kode_barang' => 'TEST-SKU-NO-SUP',
                'nama_produk' => 'Test Product',
                'nama_display' => 'Test Display Name',
                'kategori' => 'TEST-CAT',
                'warna' => 'Hitam',
                'size' => '39',
                'satuan' => 'pcs',
                'lokasi_default' => 'GUDANG',
                'hpp_per_pcs' => 10000,
                'harga_jual' => 15000,
                'stok_awal' => 50, // Initial stock > 0
            ]
        ]);

        $import->collection($rows);

        $this->assertEquals(1, $import->importedCount);
        $this->assertDatabaseHas('mst_produk', [
            'kode_barang' => 'TEST-SKU-NO-SUP',
            'total_stok' => 50,
        ]);
        $this->assertDatabaseHas('trx_barang_masuk_header', [
            'tipe_nota' => 'Stock Awal',
            'supplier_id' => null,
        ]);
    }

    public function test_unauthorized_user_cannot_trigger_sensitive_actions(): void
    {
        // Create a basic user without the 'manage master' permission
        $normalUser = User::create([
            'name' => 'Normal User',
            'email' => 'normal@wmslaf.com',
            'password' => bcrypt('password'),
        ]);

        // downloadTemplate
        Livewire::actingAs($normalUser)->test(\App\Livewire\StockMaster\Index::class)
            ->call('downloadTemplate')
            ->assertForbidden();

        // importExcel
        Livewire::actingAs($normalUser)->test(\App\Livewire\StockMaster\Index::class)
            ->call('importExcel')
            ->assertForbidden();

        // create
        Livewire::actingAs($normalUser)->test(\App\Livewire\StockMaster\Index::class)
            ->call('create')
            ->assertForbidden();

        // store
        Livewire::actingAs($normalUser)->test(\App\Livewire\StockMaster\Index::class)
            ->call('store')
            ->assertForbidden();

        // edit
        Livewire::actingAs($normalUser)->test(\App\Livewire\StockMaster\Index::class)
            ->call('edit', 1)
            ->assertForbidden();

        // destroy
        Livewire::actingAs($normalUser)->test(\App\Livewire\StockMaster\Index::class)
            ->call('destroy', 1)
            ->assertForbidden();

        // confirmedDelete
        Livewire::actingAs($normalUser)->test(\App\Livewire\StockMaster\Index::class)
            ->call('confirmedDelete', ['id' => 1])
            ->assertForbidden();
    }

    public function test_admin_can_trigger_delete_confirmation(): void
    {
        $this->actingAs($this->user);

        // Create a temporary product without transactions
        $product = Produk::create([
            'kode_barang' => 'TEMP-CONF-DEL',
            'nama_produk' => 'Temp Product',
            'nama_barang' => 'Temp Display Name',
            'kategori' => 'TEMP',
            'variasi_barang' => 'None',
            'satuan' => 'pcs',
            'lokasi' => 'GUDANG',
            'hpp' => 5000,
        ]);

        Livewire::actingAs($this->user)->test(\App\Livewire\StockMaster\Index::class)
            ->call('destroy', $product->id)
            ->assertDispatched('alert:confirm', function ($name, $args) use ($product) {
                $data = $args[0] ?? [];
                return ($data['action'] ?? null) === 'confirmed-delete' 
                    && ($data['params']['id'] ?? null) === $product->id;
            });
    }

    public function test_admin_can_confirm_delete(): void
    {
        $this->actingAs($this->user);

        // Create a temporary product to delete (so it has no transactions)
        $product = Produk::create([
            'kode_barang' => 'TEMP-DEL',
            'nama_produk' => 'Temporary Product',
            'nama_barang' => 'Temp Display Name',
            'kategori' => 'TEMP',
            'variasi_barang' => 'None',
            'satuan' => 'pcs',
            'lokasi' => 'GUDANG',
            'hpp' => 5000,
        ]);

        Livewire::actingAs($this->user)->test(\App\Livewire\StockMaster\Index::class)
            ->call('confirmedDelete', ['id' => $product->id])
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('mst_produk', [
            'id' => $product->id,
        ]);
    }

    public function test_can_search_products_in_stock_master(): void
    {
        $this->actingAs($this->user);

        // 1. Create two products
        $product1 = Produk::create([
            'kode_barang' => 'SPT-ADI-BLU',
            'nama_produk' => 'Adidas Blue',
            'nama_barang' => 'Adidas Blue Display',
            'kategori' => 'SEPATU',
            'variasi_barang' => 'Biru / 40',
            'satuan' => 'pasang',
            'lokasi' => 'GUDANG',
            'hpp' => 150000,
        ]);

        $product2 = Produk::create([
            'kode_barang' => 'SPT-NIK-RED',
            'nama_produk' => 'Nike Red',
            'nama_barang' => 'Nike Red Display',
            'kategori' => 'SEPATU',
            'variasi_barang' => 'Merah / 41',
            'satuan' => 'pasang',
            'lokasi' => 'GUDANG',
            'hpp' => 160000,
        ]);

        // 2. Test search matches
        Livewire::test(\App\Livewire\StockMaster\Index::class)
            ->set('search', 'Adidas')
            ->assertSee('Adidas Blue Display')
            ->assertDontSee('Nike Red Display');

        Livewire::test(\App\Livewire\StockMaster\Index::class)
            ->set('search', 'SPT-NIK-RED')
            ->assertSee('Nike Red Display')
            ->assertDontSee('Adidas Blue Display');
    }
}
