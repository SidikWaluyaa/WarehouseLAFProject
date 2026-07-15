<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Lokasi;
use App\Models\StokLokasi;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\Pelanggan;
use App\Models\PickingList;
use App\Models\PickingDetail;
use App\Services\PickingService;
use App\Livewire\Outbound\PickingManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PickingBarcodeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Pelanggan $pelanggan;
    private Produk $produk1;
    private Produk $produk2;
    private Lokasi $rak1;
    private Lokasi $rak2;
    private PickingList $picking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::where('email', 'superadmin@wmslaf.com')->first();

        $this->pelanggan = Pelanggan::first();

        // Create locations
        $this->rak1 = Lokasi::create(['kode_lokasi' => 'GDG-R01', 'nama_lokasi' => 'Rak 1', 'tipe_lokasi' => 'GUDANG', 'is_active' => true]);
        $this->rak2 = Lokasi::create(['kode_lokasi' => 'GDG-R02', 'nama_lokasi' => 'Rak 2', 'tipe_lokasi' => 'GUDANG', 'is_active' => true]);

        // Create products
        $this->produk1 = Produk::create([
            'nama_produk' => 'Product Alpha',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SKU-ALPHA',
            'nama_barang' => 'Product Alpha Item',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'lokasi' => 'GDG-R01',
            'hpp' => 100000,
        ]);

        $this->produk2 = Produk::create([
            'nama_produk' => 'Product Beta',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SKU-BETA',
            'nama_barang' => 'Product Beta Item',
            'variasi_barang' => 'Putih / 40',
            'satuan' => 'pasang',
            'lokasi' => 'GDG-R01',
            'hpp' => 120000,
        ]);

        // Setup stock
        StokLokasi::updateOrCreate(['produk_id' => $this->produk1->id, 'lokasi_id' => $this->rak1->id], ['qty' => 50]);
        StokLokasi::updateOrCreate(['produk_id' => $this->produk2->id, 'lokasi_id' => $this->rak1->id], ['qty' => 50]);

        // Create outgoing transaction
        $bk = BarangKeluar::create([
            'tanggal' => '2026-06-20',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK-TEST-SCAN',
            'dikeluarkan_oleh' => 'Test Operator',
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $this->produk1->id,
            'qty_keluar' => 2,
            'satuan' => 'pasang',
            'lokasi_pengambilan' => 'GDG-R01',
            'hpp' => 100000,
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $this->produk2->id,
            'qty_keluar' => 1,
            'satuan' => 'pasang',
            'lokasi_pengambilan' => 'GDG-R01',
            'hpp' => 120000,
        ]);

        // Generate Picking List
        $this->picking = PickingService::generatePickingList('BarangKeluar', $bk->id, 'Picker John');
    }

    public function test_select_picking_dispatches_focus_product(): void
    {
        Livewire::actingAs($this->user)
            ->test(PickingManager::class)
            ->call('selectPicking', $this->picking->id)
            ->assertSet('activePickingId', $this->picking->id)
            ->assertDispatched('focus-product');
    }

    public function test_scan_product_successfully(): void
    {
        PickingService::startPicking($this->picking->id, 'Picker John');

        Livewire::actingAs($this->user)
            ->test(PickingManager::class)
            ->set('activePickingId', $this->picking->id)
            ->set('scanProduct', 'SKU-ALPHA')
            ->call('scanBarcode')
            ->assertSet('scanProduct', '')
            ->assertDispatched('focus-product');

        // Verify it was updated in the DB
        $detail = PickingDetail::where('picking_list_id', $this->picking->id)
            ->where('produk_id', $this->produk1->id)
            ->first();
        $this->assertEquals(1, $detail->qty_picked);
        $this->assertFalse((bool)$detail->is_verified);
    }

    public function test_scan_invalid_product_shows_error(): void
    {
        PickingService::startPicking($this->picking->id, 'Picker John');

        Livewire::actingAs($this->user)
            ->test(PickingManager::class)
            ->set('activePickingId', $this->picking->id)
            ->set('scanProduct', 'INVALID-SKU')
            ->call('scanBarcode')
            ->assertDispatched('focus-product');
    }
}
