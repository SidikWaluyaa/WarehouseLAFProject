<?php

namespace Tests\Feature;

use App\Livewire\StockMaster\Index;
use App\Models\Lokasi;
use App\Models\Produk;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SelectiveThresholdAndWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Lokasi $lokasi;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'manage master']);
        $role = Role::create(['name' => 'super_admin']);
        $role->givePermissionTo('manage master');

        $this->user = User::factory()->create();
        $this->user->assignRole('super_admin');

        $this->lokasi = Lokasi::create([
            'kode_lokasi' => 'GUDANG',
            'nama_lokasi' => 'Gudang Utama',
            'tipe_lokasi' => 'GUDANG',
            'is_active' => true,
        ]);
    }

    protected function createTestProduct(array $overrides = []): Produk
    {
        return Produk::create(array_merge([
            'kode_barang' => 'SKU-' . uniqid(),
            'nama_barang' => 'Produk Test',
            'nama_produk' => 'Produk Test',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'kategori' => 'Sandal Dewasa',
            'lokasi' => 'GUDANG',
            'total_stok' => 10,
            'is_active' => true,
            'is_threshold_active' => false,
            'min_stok' => null,
            'hpp' => 50000,
            'harga_jual' => 80000,
        ], $overrides));
    }

    public function test_product_selective_threshold_scope_and_helper(): void
    {
        // 1. Product without threshold active - should not trigger low stock even if stock is 2
        $p1 = $this->createTestProduct([
            'total_stok' => 2,
            'is_threshold_active' => false,
            'min_stok' => null,
        ]);

        // 2. Product with threshold active and stock <= min_stok
        $p2 = $this->createTestProduct([
            'kategori_2' => 'TOP 20',
            'total_stok' => 5,
            'is_threshold_active' => true,
            'min_stok' => 20,
        ]);

        // 3. Product with threshold active and stock > min_stok
        $p3 = $this->createTestProduct([
            'total_stok' => 50,
            'is_threshold_active' => true,
            'min_stok' => 10,
        ]);

        $this->assertFalse($p1->isLowStock());
        $this->assertTrue($p2->isLowStock());
        $this->assertFalse($p3->isLowStock());

        $lowStockList = Produk::lowStock()->get();
        $this->assertCount(1, $lowStockList);
        $this->assertEquals($p2->id, $lowStockList->first()->id);
    }

    public function test_stock_master_kpi_counts_only_products_with_active_threshold_low_stock(): void
    {
        $this->actingAs($this->user);

        // Product 1: low stock but threshold inactive
        $this->createTestProduct([
            'kode_barang' => 'SKU-01',
            'total_stok' => 1,
            'is_threshold_active' => false,
        ]);

        // Product 2: low stock and threshold active
        $this->createTestProduct([
            'kode_barang' => 'SKU-02',
            'kategori_2' => 'TOP 20',
            'total_stok' => 4,
            'is_threshold_active' => true,
            'min_stok' => 15,
        ]);

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSee('Stok Menipis')
            ->assertViewHas('stokMenipis', 1)
            ->assertViewHas('lowStockCount', 1);
    }

    public function test_stock_master_filter_menipis_and_threshold_only(): void
    {
        $this->actingAs($this->user);

        $this->createTestProduct([
            'kode_barang' => 'SKU-FILTER-1',
            'total_stok' => 100,
            'is_threshold_active' => false,
        ]);

        $this->createTestProduct([
            'kode_barang' => 'SKU-FILTER-2',
            'total_stok' => 50,
            'is_threshold_active' => true,
            'min_stok' => 10,
        ]);

        $this->createTestProduct([
            'kode_barang' => 'SKU-FILTER-3',
            'total_stok' => 3,
            'is_threshold_active' => true,
            'min_stok' => 10,
        ]);

        // Test filter 'menipis'
        Livewire::test(Index::class)
            ->set('stockFilter', 'menipis')
            ->assertSee('SKU-FILTER-3')
            ->assertDontSee('SKU-FILTER-1')
            ->assertDontSee('SKU-FILTER-2');

        // Test filter 'threshold_only'
        Livewire::test(Index::class)
            ->set('stockFilter', 'threshold_only')
            ->assertSee('SKU-FILTER-2')
            ->assertSee('SKU-FILTER-3')
            ->assertDontSee('SKU-FILTER-1');
    }

    public function test_whatsapp_notification_service_formats_message_and_link(): void
    {
        $p = $this->createTestProduct([
            'kode_barang' => 'SKU-TOP-01',
            'nama_barang' => 'Sandal Black Casual',
            'kategori_2' => 'TOP 20',
            'total_stok' => 2,
            'is_threshold_active' => true,
            'min_stok' => 20,
            'satuan' => 'pasang',
        ]);

        $service = new WhatsAppNotificationService();
        $message = $service->formatLowStockMessage([$p]);

        $this->assertStringContainsString('PERINGATAN STOK MENIPIS', $message);
        $this->assertStringContainsString('Sandal Black Casual', $message);
        $this->assertStringContainsString('SKU-TOP-01', $message);
        $this->assertStringContainsString('Sisa Stok: *2 pasang*', $message);
        $this->assertStringContainsString('(Batas Min: 20 pasang)', $message);

        $url = $service->generateWhatsAppUrl('081234567890', $message);
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
    }

    public function test_whatsapp_modal_interactions_and_fonnte_send(): void
    {
        $this->actingAs($this->user);

        $this->createTestProduct([
            'kode_barang' => 'SKU-WA-TEST',
            'nama_barang' => 'Sandal Menipis WA',
            'total_stok' => 1,
            'is_threshold_active' => true,
            'min_stok' => 10,
            'satuan' => 'pasang',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true, 'id' => '12345'], 200),
        ]);

        config(['services.fonnte.token' => 'dummy_token']);

        Livewire::test(Index::class)
            ->call('openWaModal')
            ->assertSet('isWaModalOpen', true)
            ->assertSee('Peringatan Stok Menipis WhatsApp')
            ->set('waTargetPhone', '081298765432')
            ->call('sendWaFonnte')
            ->assertDispatched('toast:success')
            ->assertSet('isWaModalOpen', false);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request->data()['target'] === '6281298765432';
        });
    }

    public function test_selecting_kategori_2_top_20_auto_enables_threshold(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Index::class)
            ->call('create')
            ->assertSet('isOpen', true)
            ->assertSet('is_threshold_active', false)
            ->set('kategori_2', 'TOP 20')
            ->assertSet('is_threshold_active', true)
            ->assertSet('min_stok', 20);
    }

    public function test_update_threshold_inline_auto_saves_and_syncs_analytics(): void
    {
        $this->actingAs($this->user);

        $product = $this->createTestProduct([
            'kode_barang' => 'SKU-INLINE',
            'is_threshold_active' => false,
            'min_stok' => null,
            'notif_phone' => null,
        ]);

        Livewire::test(Index::class)
            ->call('updateThresholdInline', $product->id, 'is_threshold_active', true)
            ->assertDispatched('toast:success');

        $product->refresh();
        $this->assertTrue($product->is_threshold_active);
        $this->assertEquals(10, $product->min_stok);

        Livewire::test(Index::class)
            ->call('updateThresholdInline', $product->id, 'min_stok', 25)
            ->call('updateThresholdInline', $product->id, 'notif_phone', '08123456789')
            ->assertDispatched('toast:success');

        $product->refresh();
        $this->assertEquals(25, $product->min_stok);
        $this->assertEquals('08123456789', $product->notif_phone);

        // Check Analytics sync
        $this->assertDatabaseHas('analytics_produk', [
            'produk_id' => $product->id,
            'minimal_stock' => 25,
            'is_manual_stock' => 1,
        ]);
    }

    public function test_open_wa_modal_single_product_prefills_phone_and_single_item_message(): void
    {
        $this->actingAs($this->user);

        $product = $this->createTestProduct([
            'kode_barang' => 'SKU-SINGLE-WA',
            'nama_barang' => 'Sandal Premium Single',
            'is_threshold_active' => true,
            'min_stok' => 20,
            'total_stok' => 4,
            'notif_phone' => '085721606350',
            'satuan' => 'pasang',
        ]);

        Livewire::test(Index::class)
            ->call('openWaModalSingleProduct', $product->id)
            ->assertSet('isWaModalOpen', true)
            ->assertSet('waTargetPhone', '085721606350')
            ->assertSee('Sandal Premium Single')
            ->assertSee('SKU-SINGLE-WA');
    }
}
