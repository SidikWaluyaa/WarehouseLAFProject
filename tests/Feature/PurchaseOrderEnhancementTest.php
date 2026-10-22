<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\PoHeader;
use App\Models\PoDetail;
use App\Models\BarangMasuk;
use App\Models\AnalyticsProduk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\PurchaseOrder\Create as PoCreate;
use App\Livewire\PurchaseOrder\Manager as PoManager;
use App\Livewire\Pembelian\Create as PembelianCreate;
use App\Livewire\Pembelian\Index as PembelianIndex;
use Tests\TestCase;

class PurchaseOrderEnhancementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private Produk $produkA;
    private Produk $produkB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->user = User::factory()->create([
            'name' => 'Admin Gudang Test'
        ]);
        $this->user->assignRole('admin');

        $this->supplier = Supplier::create([
            'nama_supplier' => 'PT Mitra Sejati',
            'kontak' => '08123456789',
            'alamat' => 'Bandung',
        ]);

        $this->produkA = Produk::create([
            'nama_produk' => 'Sepatu Sneaker A',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SNK-001',
            'nama_barang' => 'Sneaker Velocity',
            'variasi_barang' => 'Hitam / 42',
            'satuan' => 'pasang',
            'hpp' => 50000.00,
            'stok_masuk' => 0,
            'stok_keluar' => 0,
            'total_stok' => 0,
            'lokasi' => 'GUDANG',
        ]);

        $this->produkB = Produk::create([
            'nama_produk' => 'Sepatu Casual B',
            'kategori' => 'SEPATU',
            'kode_barang' => 'CSL-002',
            'nama_barang' => 'Casual Loafer',
            'variasi_barang' => 'Coklat / 41',
            'satuan' => 'pasang',
            'hpp' => 75000.00,
            'stok_masuk' => 0,
            'stok_keluar' => 0,
            'total_stok' => 0,
            'lokasi' => 'GUDANG',
        ]);

        AnalyticsProduk::create([
            'produk_id' => $this->produkA->id,
            'kategori' => $this->produkA->kategori,
            'penjualan_jan' => 0,
            'penjualan_apr' => 0,
            'penjualan_mei' => 0,
            'rata_rata_harian' => 0.00,
            'leadtime_produksi' => 7,
            'minimal_stock' => 5,
            'safety_stock' => 5,
            'maximum_stock' => 50,
        ]);

        AnalyticsProduk::create([
            'produk_id' => $this->produkB->id,
            'kategori' => $this->produkB->kategori,
            'penjualan_jan' => 0,
            'penjualan_apr' => 0,
            'penjualan_mei' => 0,
            'rata_rata_harian' => 0.00,
            'leadtime_produksi' => 7,
            'minimal_stock' => 5,
            'safety_stock' => 5,
            'maximum_stock' => 50,
        ]);
    }

    public function test_po_create_page_requires_authentication(): void
    {
        $response = $this->get('/purchase-order/create');
        $response->assertRedirect('/login');
    }

    public function test_can_render_po_create_page_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get('/purchase-order/create');
        $response->assertStatus(200);
        $response->assertSee('Terbitkan Purchase Order Baru');
        $response->assertSee('Scan Barcode');
    }

    public function test_can_add_item_by_scanning_barcode_in_po_create(): void
    {
        Livewire::actingAs($this->user)
            ->test(PoCreate::class)
            ->set('barcodeScan', 'SNK-001')
            ->call('addBarcodeItem')
            ->assertSet('items.0.produk_id', $this->produkA->id)
            ->assertSet('items.0.jumlah', 1)
            ->assertSet('items.0.hpp', 50000.00)
            ->assertHasNoErrors();
    }

    public function test_scanning_same_barcode_multiple_times_increments_quantity_in_po_create(): void
    {
        Livewire::actingAs($this->user)
            ->test(PoCreate::class)
            ->set('barcodeScan', 'SNK-001')
            ->call('addBarcodeItem')
            ->set('barcodeScan', 'SNK-001')
            ->call('addBarcodeItem')
            ->assertSet('items.0.produk_id', $this->produkA->id)
            ->assertSet('items.0.jumlah', 2)
            ->assertHasNoErrors();
    }

    public function test_can_create_po_with_auto_generated_nomor_po(): void
    {
        Livewire::actingAs($this->user)
            ->test(PoCreate::class)
            ->set('tanggal', '2026-10-03')
            ->set('supplier_id', $this->supplier->id)
            ->set('artikel', 'ARTIKEL-TEST-001')
            ->set('items', [
                [
                    'produk_id' => $this->produkA->id,
                    'kode_barang' => $this->produkA->kode_barang,
                    'nama_barang' => $this->produkA->nama_barang,
                    'satuan' => 'pasang',
                    'jumlah' => 10,
                    'hpp' => 50000.00,
                ],
                [
                    'produk_id' => $this->produkB->id,
                    'kode_barang' => $this->produkB->kode_barang,
                    'nama_barang' => $this->produkB->nama_barang,
                    'satuan' => 'pasang',
                    'jumlah' => 20,
                    'hpp' => 75000.00,
                ],
            ])
            ->call('store')
            ->assertRedirect(route('purchase-order', ['highlight' => PoHeader::where('artikel', 'ARTIKEL-TEST-001')->value('id')]));

        $this->assertDatabaseHas('po_header', [
            'supplier_id' => $this->supplier->id,
            'artikel' => 'ARTIKEL-TEST-001',
            'jumlah' => 30,
        ]);

        $po = PoHeader::where('artikel', 'ARTIKEL-TEST-001')->first();
        $this->assertNotNull($po);
        $this->assertNotNull($po->nomor_po);
        $this->assertStringStartsWith('PO/20261003/', $po->nomor_po);

        $this->assertDatabaseHas('po_detail', [
            'po_header_id' => $po->id,
            'produk_id' => $this->produkA->id,
            'jumlah' => 10,
            'barang_masuk' => 0,
            'sisa' => 10,
            'hpp' => 50000.00,
        ]);

        $this->assertDatabaseHas('po_detail', [
            'po_header_id' => $po->id,
            'produk_id' => $this->produkB->id,
            'jumlah' => 20,
            'barang_masuk' => 0,
            'sisa' => 20,
            'hpp' => 75000.00,
        ]);

        $this->assertEquals(30, $po->totalSisa());
        $this->assertFalse($po->isCompleted());
    }

    public function test_can_prefill_barang_masuk_from_po_and_perform_partial_receipt(): void
    {
        // 1. Create a PO first
        $po = PoHeader::create([
            'nomor_po' => 'PO/20261003/0001',
            'tanggal' => '2026-10-03',
            'supplier_id' => $this->supplier->id,
            'artikel' => 'BATCH-SPRING-2026',
            'jumlah' => 30,
        ]);

        $detailA = PoDetail::create([
            'po_header_id' => $po->id,
            'produk_id' => $this->produkA->id,
            'jumlah' => 10,
            'barang_masuk' => 0,
            'sisa' => 10,
            'hpp' => 50000.00,
        ]);

        $detailB = PoDetail::create([
            'po_header_id' => $po->id,
            'produk_id' => $this->produkB->id,
            'jumlah' => 20,
            'barang_masuk' => 0,
            'sisa' => 20,
            'hpp' => 75000.00,
        ]);

        // 2. Open Pembelian/Create with ?po_id=X
        $component = Livewire::actingAs($this->user)
            ->withQueryParams(['po_id' => $po->id])
            ->test(PembelianCreate::class)
            ->assertSet('po_header_id', $po->id)
            ->assertSet('tipe_nota', 'Purchase Order')
            ->assertSet('supplier_id', $this->supplier->id)
            ->assertCount('items', 2);

        // 3. Partial receipt: User receives only 6 units of Product A with adjusted HPP of 52,000,
        // and removes Product B row because Product B has not arrived yet.
        $component->set('items.0.qty_masuk', 6)
            ->set('items.0.hpp', 52000.00)
            ->call('removeItem', 1) // Remove Product B
            ->assertCount('items', 1)
            ->set('nomor_nota', 'DO-VENDOR-8899')
            ->call('store')
            // Qty 6 != sisa 10 -> alasan selisih wajib
            ->assertHasErrors(['items.0.alasan_selisih'])
            ->set('items.0.alasan_selisih', 'Kurang kirim')
            ->call('store')
            ->assertHasNoErrors()
            ->assertRedirect(route('purchase-order.show', $po->id));

        $this->assertDatabaseHas('trx_barang_masuk_detail', [
            'po_detail_id' => $detailA->id,
            'qty_masuk' => 6,
            'sisa_po_sebelum' => 10,
            'selisih_po' => -4,
            'alasan_selisih' => 'Kurang kirim',
        ]);

        // 4. Verify Barang Masuk Header has po_header_id and tipe_nota = 'Purchase Order'
        $this->assertDatabaseHas('trx_barang_masuk_header', [
            'nomor_nota' => 'DO-VENDOR-8899',
            'tipe_nota' => 'Purchase Order',
            'po_header_id' => $po->id,
            'supplier_id' => $this->supplier->id,
        ]);

        $bmHeader = BarangMasuk::where('nomor_nota', 'DO-VENDOR-8899')->first();
        $this->assertNotNull($bmHeader);
        $this->assertEquals($po->id, $bmHeader->po_header_id);

        // 5. Verify PO Details tracking
        $detailA->refresh();
        $detailB->refresh();

        $this->assertEquals(6, $detailA->barang_masuk);
        $this->assertEquals(4, $detailA->sisa);

        $this->assertEquals(0, $detailB->barang_masuk);
        $this->assertEquals(20, $detailB->sisa);

        $po->refresh();
        $this->assertEquals(24, $po->totalSisa());
        $this->assertFalse($po->isCompleted());

        // 6. Test Barang Masuk Index displays PO badge & filter works
        Livewire::actingAs($this->user)
            ->test(PembelianIndex::class)
            ->set('tipeFilter', 'Purchase Order')
            ->assertSee('DO-VENDOR-8899')
            ->assertSee('PO/20261003/0001');

        // 7. Receive remaining items in second delivery
        Livewire::actingAs($this->user)
            ->withQueryParams(['po_id' => $po->id])
            ->test(PembelianCreate::class)
            ->assertSet('po_header_id', $po->id)
            ->assertSet('tipe_nota', 'Purchase Order')
            // Items should now prefill with remaining sisa: 4 of Product A, 20 of Product B
            ->assertSet('items.0.qty_masuk', 4)
            ->assertSet('items.1.qty_masuk', 20)
            ->set('nomor_nota', 'DO-VENDOR-9000')
            ->call('store')
            ->assertRedirect(route('purchase-order.show', $po->id));

        $detailA->refresh();
        $detailB->refresh();
        $po->refresh();

        $this->assertEquals(10, $detailA->barang_masuk);
        $this->assertEquals(0, $detailA->sisa);

        $this->assertEquals(20, $detailB->barang_masuk);
        $this->assertEquals(0, $detailB->sisa);

        $this->assertEquals(0, $po->totalSisa());
        $this->assertTrue($po->isCompleted());
    }

    public function test_can_add_item_row_manually_and_select_product(): void
    {
        Livewire::actingAs($this->user)
            ->test(PoCreate::class)
            ->assertCount('items', 0)
            ->call('addItemRow')
            ->assertCount('items', 1)
            ->assertSet('items.0.produk_id', null)
            ->call('selectProductForRow', 0, $this->produkA->id)
            ->assertSet('items.0.produk_id', $this->produkA->id)
            ->assertSet('items.0.kode_barang', 'SNK-001')
            ->assertSet('items.0.nama_barang', 'Sneaker Velocity')
            ->assertSet('items.0.hpp', 50000.00);
    }

    public function test_can_select_multiple_products_from_catalog_modal(): void
    {
        Livewire::actingAs($this->user)
            ->test(PoCreate::class)
            ->assertSet('isCatalogModalOpen', false)
            ->call('openCatalogModal')
            ->assertSet('isCatalogModalOpen', true)
            ->set('selectedCatalogProducts', [$this->produkA->id, $this->produkB->id])
            ->call('addSelectedCatalogProducts')
            ->assertSet('isCatalogModalOpen', false)
            ->assertCount('items', 2)
            ->assertSet('items.0.produk_id', $this->produkA->id)
            ->assertSet('items.1.produk_id', $this->produkB->id);
    }
}
