<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Lokasi;
use App\Models\StokLokasi;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\Supplier;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\Pelanggan;
use App\Models\PickingList;
use App\Models\PickingDetail;
use App\Services\PickingService;
use App\Services\StockEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PickingPackingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private Pelanggan $pelanggan;
    private Produk $produk;
    private Lokasi $rak1;
    private Lokasi $rak2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first();

        $this->supplier = Supplier::create([
            'nama_supplier' => 'Test Supplier',
            'kontak' => '0812',
            'alamat' => 'Alamat',
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Test Customer',
            'kontak' => '0813',
            'alamat' => 'Alamat',
        ]);

        // Create locations
        $this->rak1 = Lokasi::create(['kode_lokasi' => 'GDG-R01', 'nama_lokasi' => 'Rak 1', 'tipe_lokasi' => 'GUDANG', 'is_active' => true]);
        $this->rak2 = Lokasi::create(['kode_lokasi' => 'GDG-R02', 'nama_lokasi' => 'Rak 2', 'tipe_lokasi' => 'GUDANG', 'is_active' => true]);

        // Create product
        $this->produk = Produk::create([
            'nama_produk' => 'Sepatu Adidas',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SPT-ADI-001',
            'nama_barang' => 'Sepatu Adidas Hitam',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'lokasi' => 'GDG-R01',
            'hpp' => 100000,
        ]);
    }

    public function test_fifo_picking_recommendation(): void
    {
        // 1. Receive stock in Rak 2 (older date: 2026-06-01)
        $bm1 = BarangMasuk::create([
            'tanggal' => '2026-06-01',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-OLD',
            'data_dimasukan_oleh' => 'Test Admin',
        ]);
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bm1->id,
            'produk_id' => $this->produk->id,
            'qty_masuk' => 10,
            'satuan' => 'pasang',
            'lokasi_simpan' => 'GDG-R02',
            'hpp' => 100000,
        ]);
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->rak2->id],
            ['qty' => 10]
        );

        // 2. Receive stock in Rak 1 (newer date: 2026-06-15)
        $bm2 = BarangMasuk::create([
            'tanggal' => '2026-06-15',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-NEW',
            'data_dimasukan_oleh' => 'Test Admin',
        ]);
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bm2->id,
            'produk_id' => $this->produk->id,
            'qty_masuk' => 20,
            'satuan' => 'pasang',
            'lokasi_simpan' => 'GDG-R01',
            'hpp' => 100000,
        ]);
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->rak1->id],
            ['qty' => 20]
        );

        // 3. Request picking of 15 units. FIFO recommendation should suggest:
        // - 10 units from Rak 2 (oldest)
        // - 5 units from Rak 1 (newer)
        $recommendations = PickingService::getFifoLocationsForProduct($this->produk->id, 15);

        $this->assertCount(2, $recommendations);
        $this->assertEquals('GDG-R02', $recommendations[0]['kode_lokasi']);
        $this->assertEquals(10, $recommendations[0]['qty']);
        $this->assertEquals('GDG-R01', $recommendations[1]['kode_lokasi']);
        $this->assertEquals(5, $recommendations[1]['qty']);
    }

    public function test_picking_and_packing_flow(): void
    {
        // Setup initial stock in Rak 1
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->rak1->id],
            ['qty' => 50]
        );

        // Create outgoing transaction
        $bk = BarangKeluar::create([
            'tanggal' => '2026-06-20',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK-TEST-1',
            'dikeluarkan_oleh' => 'Test Operator',
        ]);
        $detail = BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 10,
            'satuan' => 'pasang',
            'lokasi_pengambilan' => 'GUDANG',
            'hpp' => 100000,
        ]);

        // Generate Picking List
        $picking = PickingService::generatePickingList('BarangKeluar', $bk->id, 'Picker John');
        $this->assertEquals('PENDING', $picking->status);
        $this->assertCount(1, $picking->details);

        // Start Picking
        PickingService::startPicking($picking->id, 'Picker John');
        $picking->refresh();
        $this->assertEquals('PICKING', $picking->status);

        // Verify picking items
        $pickDetail = $picking->details->first();
        PickingService::verifyPickItem($pickDetail->id, 10);
        $pickDetail->refresh();
        $this->assertTrue((bool)$pickDetail->is_verified);

        // Complete picking
        PickingService::completePicking($picking->id);
        $picking->refresh();
        $this->assertEquals('COMPLETED', $picking->status);
    }

    public function test_complete_picking_does_not_deduct_stock_again(): void
    {
        // 1. Initial stock is 50 in Rak 1 (GDG-R01)
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->rak1->id],
            ['qty' => 50]
        );
        $this->produk->total_stok = 50;
        $this->produk->save();

        // 2. Create outgoing transaction using StockEngineService
        $data = [
            'tanggal' => '2026-06-20',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'dikeluarkan_oleh' => 'Test Operator',
            'items' => [
                [
                    'produk_id' => $this->produk->id,
                    'qty_keluar' => 5,
                    'lokasi_pengambilan' => 'GDG-R01',
                ]
            ]
        ];
        $bk = StockEngineService::tambahBarangKeluar($data);

        // Verify stock is decremented to 45
        $this->produk->refresh();
        $this->assertEquals(45, $this->produk->total_stok);

        $stokLok = StokLokasi::where(['produk_id' => $this->produk->id, 'lokasi_id' => $this->rak1->id])->first();
        $this->assertEquals(45, $stokLok->qty);

        // 3. Generate Picking List
        $picking = PickingService::generatePickingList('BarangKeluar', $bk->id, 'Picker John');
        $this->assertEquals('PENDING', $picking->status);

        // Start Picking
        PickingService::startPicking($picking->id, 'Picker John');
        
        // Verify pick item
        $pickDetail = $picking->details->first();
        PickingService::verifyPickItem($pickDetail->id, 5);

        // Complete picking through Livewire to simulate the frontend action
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Outbound\PickingManager::class)
            ->set('activePickingId', $picking->id)
            ->call('completePicking');

        // 4. Verify picking status is COMPLETED
        $picking->refresh();
        $this->assertEquals('COMPLETED', $picking->status);

        // 5. Verify stock is STILL 45 (Not decremented to 40)
        $this->produk->refresh();
        $this->assertEquals(45, $this->produk->total_stok);

        $stokLok->refresh();
        $this->assertEquals(45, $stokLok->qty);
    }
}
