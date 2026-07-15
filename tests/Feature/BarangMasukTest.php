<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\AnalyticsProduk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Pembelian\Create as PembelianCreate;
use Tests\TestCase;

class BarangMasukTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->user = User::factory()->create([
            'name' => 'Operator Test'
        ]);
        $this->user->assignRole('operator_gudang');

        $this->supplier = Supplier::create([
            'nama_supplier' => 'PT Test Supplier',
            'kontak' => '0812345',
            'alamat' => 'Alamat Test',
        ]);

        $this->produk = Produk::create([
            'nama_produk' => 'Sandal Jepit',
            'kategori' => 'SANDAL',
            'kode_barang' => 'SDL-JEP',
            'nama_barang' => 'Sandal Jepit Keren',
            'variasi_barang' => 'Biru / 40',
            'satuan' => 'pasang',
            'hpp' => 10000.00,
            'stok_masuk' => 10,
            'stok_keluar' => 0,
            'total_stok' => 10,
            'lokasi' => 'GUDANG',
        ]);

        AnalyticsProduk::create([
            'produk_id' => $this->produk->id,
            'kategori' => $this->produk->kategori,
            'penjualan_jan' => 0,
            'penjualan_apr' => 0,
            'penjualan_mei' => 0,
            'rata_rata_harian' => 0.00,
            'leadtime_produksi' => 10,
            'minimal_stock' => 0,
            'safety_stock' => 0,
            'maximum_stock' => 0,
        ]);
    }

    public function test_create_page_requires_authentication(): void
    {
        $response = $this->get('/barang-masuk/create');
        $response->assertRedirect('/login');
    }

    public function test_can_render_create_page_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get('/barang-masuk/create');
        $response->assertStatus(200);
        $response->assertSee('Pencatatan Barang Masuk');
    }

    public function test_can_add_item_by_scanning_barcode(): void
    {
        Livewire::actingAs($this->user)
            ->test(PembelianCreate::class)
            ->set('barcodeScan', 'SDL-JEP')
            ->call('addBarcodeItem')
            ->assertSet('items.0.produk_id', $this->produk->id)
            ->assertSet('items.0.qty_masuk', 1)
            ->assertSet('items.0.hpp', 10000.00)
            ->assertHasNoErrors();
    }

    public function test_scanning_same_barcode_multiple_times_increments_quantity(): void
    {
        Livewire::actingAs($this->user)
            ->test(PembelianCreate::class)
            ->set('barcodeScan', 'SDL-JEP')
            ->call('addBarcodeItem')
            ->set('barcodeScan', 'SDL-JEP')
            ->call('addBarcodeItem')
            ->assertSet('items.0.produk_id', $this->produk->id)
            ->assertSet('items.0.qty_masuk', 2)
            ->assertHasNoErrors();
    }

    public function test_fails_when_scanning_invalid_barcode(): void
    {
        Livewire::actingAs($this->user)
            ->test(PembelianCreate::class)
            ->set('barcodeScan', 'INVALID-CODE')
            ->call('addBarcodeItem')
            ->assertHasErrors('barcodeScan');
    }

    public function test_can_save_transaction_successfully(): void
    {
        Livewire::actingAs($this->user)
            ->test(PembelianCreate::class)
            ->set('tanggal', '2026-06-16')
            ->set('tipe_nota', 'Pembelian')
            ->set('supplier_id', $this->supplier->id)
            ->set('nomor_nota', 'INV-IN-001')
            ->set('items', [
                [
                    'produk_id' => $this->produk->id,
                    'qty_masuk' => 5,
                    'hpp' => 12000.00,
                    'lokasi_simpan' => 'GUDANG',
                    'po_detail_id' => null,
                ]
            ])
            ->call('store')
            ->assertHasNoErrors()
            ->assertRedirect('/barang-masuk');

        $this->produk->refresh();
        $this->assertEquals(15, $this->produk->total_stok);
        // HPP = ((10 * 10000) + (5 * 12000)) / 15 = 10666.67
        $this->assertEquals(10666.67, round($this->produk->hpp, 2));
    }

    public function test_can_save_transaction_with_auto_generated_nomor_nota(): void
    {
        Livewire::actingAs($this->user)
            ->test(PembelianCreate::class)
            ->set('tanggal', '2026-06-16')
            ->set('tipe_nota', 'Pembelian')
            ->set('supplier_id', $this->supplier->id)
            ->assertSet('nomor_nota', 'BM/20260616/0001')
            ->set('items', [
                [
                    'produk_id' => $this->produk->id,
                    'qty_masuk' => 5,
                    'hpp' => 12000.00,
                    'lokasi_simpan' => 'GUDANG',
                    'po_detail_id' => null,
                ]
            ])
            ->call('store')
            ->assertHasNoErrors()
            ->assertRedirect('/barang-masuk');

        $this->assertDatabaseHas('trx_barang_masuk_header', [
            'nomor_nota' => 'BM/20260616/0001',
            'supplier_id' => $this->supplier->id,
        ]);
    }
}
