<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\AturanKode;
use App\Services\SkuGeneratorService;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SkuGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Re-cache and set up Spatie permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::create(['name' => 'manage master']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(['manage master']);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('admin');

        // Seed the default global rule (migration does it, but refresh database truncates it)
        AturanKode::create([
            'kategori' => null,
            'prefix' => 'LAF',
            'include_category_prefix' => true,
            'include_product_initials' => true,
            'include_variant_code' => true,
            'increment_digits' => 3,
            'is_active' => true,
        ]);
    }

    /**
     * Test SKU generation in isolation using global fallback rule
     */
    public function test_can_generate_sku_with_global_fallback(): void
    {
        $sku = SkuGeneratorService::generate('Sandal Gunung Keren', 'SANDAL', 'Hitam / 40');
        
        // Expected layout: LAF (Prefix) - SAN (Category) - SGK (Initials) - H40 (Variant) - 001 (Increment)
        $this->assertEquals('LAF-SAN-SGK-H40-001', $sku);
    }

    /**
     * Test category specific rule overrides global fallback
     */
    public function test_category_rule_overrides_global_fallback(): void
    {
        // Add custom rule for SEPATU
        AturanKode::create([
            'kategori' => 'SEPATU',
            'prefix' => 'SPT',
            'include_category_prefix' => false,
            'include_product_initials' => true,
            'include_variant_code' => false,
            'increment_digits' => 2,
            'is_active' => true,
        ]);

        $sku = SkuGeneratorService::generate('Sepatu Lari Nike', 'SEPATU', 'Biru / 42');

        // Expected layout: SPT (Prefix) - SLN (Initials) - 01 (Increment, 2 digits, category and variant omitted)
        $this->assertEquals('SPT-SLN-01', $sku);
    }

    /**
     * Test increment logic increments when similar prefix exists
     */
    public function test_sku_increments_uniquely(): void
    {
        // Create an existing product with SKU ending in 001
        Produk::create([
            'kode_barang' => 'LAF-SAN-SGK-H40-001',
            'nama_produk' => 'Sandal Gunung Keren',
            'nama_barang' => 'Sandal Gunung Keren',
            'kategori' => 'SANDAL',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'hpp' => 10000,
            'stok_masuk' => 0,
            'stok_keluar' => 0,
            'total_stok' => 0,
            'lokasi' => 'GDG-A01',
        ]);

        $sku = SkuGeneratorService::generate('Sandal Gunung Keren', 'SANDAL', 'Hitam / 40');
        $this->assertEquals('LAF-SAN-SGK-H40-002', $sku);
    }

    /**
     * Test Livewire form integration in Stock Master
     */
    public function test_stock_master_auto_generates_sku_reactively(): void
    {
        // Mock Livewire component for Stock Master
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\StockMaster\Index::class)
            ->set('nama_produk', 'Sandal Gunung')
            ->set('kategori', 'SANDAL')
            ->set('variasi_barang', 'Merah / 39')
            // Assert that the SKU generated is set on the model
            ->assertSet('kode_barang', 'LAF-SAN-SG-M39-001')
            
            // Toggle auto-generate to false, and change it manually
            ->set('autoGenerateSku', false)
            ->set('kode_barang', 'MANUAL-SKU')
            ->set('nama_barang', 'Sandal Gunung Merah')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mst_produk', [
            'kode_barang' => 'MANUAL-SKU',
            'nama_barang' => 'Sandal Gunung Merah',
        ]);
    }

    /**
     * Test Coding Rules CRUD Livewire page
     */
    public function test_can_manage_coding_rules_via_crud(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Pengkodean\Index::class)
            ->call('create')
            ->set('kategori', 'AKSESORIS')
            ->set('prefix', 'ACC')
            ->set('include_category_prefix', true)
            ->set('include_product_initials', false)
            ->set('include_variant_code', true)
            ->set('increment_digits', 4)
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('toast:success');

        $this->assertDatabaseHas('mst_aturan_kode', [
            'kategori' => 'AKSESORIS',
            'prefix' => 'ACC',
            'increment_digits' => 4,
        ]);
    }

    /**
     * Test SKU generation using PRD_STYLE format
     */
    public function test_can_generate_sku_with_prd_style(): void
    {
        // Add custom rule for SANDAL using PRD_STYLE
        AturanKode::create([
            'kategori' => 'SANDAL',
            'prefix' => 'SandalSlide',
            'include_category_prefix' => false,
            'include_product_initials' => false,
            'include_variant_code' => false,
            'increment_digits' => 3,
            'format_tipe' => 'PRD_STYLE',
            'is_active' => true,
        ]);

        $sku = SkuGeneratorService::generate('Gorka Hitam 36', 'SANDAL', 'Hitam / 36');
        $this->assertEquals('SandalSlide-Gorka-FullBlack-36', $sku);

        $sku2 = SkuGeneratorService::generate('Gorka Abu 39', 'SANDAL', 'Abu-abu / 39');
        $this->assertEquals('SandalSlide-Gorka-Grey-39', $sku2);

        $sku3 = SkuGeneratorService::generate('Gorka Army 40', 'SANDAL', 'Army / 40');
        $this->assertEquals('SandalSlide-Gorka-Army-40', $sku3);
    }
}
