<?php

namespace Tests\Feature;

use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\Lokasi;
use App\Models\Pelanggan;
use App\Models\PickingList;
use App\Models\Produk;
use App\Models\StokLokasi;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PickingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PickingRunnerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private Pelanggan $pelanggan;
    private Produk $produk;
    private Lokasi $rak;
    private PickingList $picking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first();

        $this->supplier = Supplier::create([
            'nama_supplier' => 'Supplier Runner Test',
            'kontak' => '0812345678',
            'alamat' => 'Alamat Runner',
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Runner Test',
            'kontak' => '0898765432',
            'alamat' => 'Alamat Pelanggan Runner',
        ]);

        $this->rak = Lokasi::create([
            'kode_lokasi' => 'GDG-RUN',
            'nama_lokasi' => 'Rak Runner Test',
            'tipe_lokasi' => 'GUDANG',
            'is_active' => true,
        ]);

        $this->produk = Produk::create([
            'nama_produk' => 'Sepatu Sneakers Runner',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SPT-RUN-001',
            'nama_barang' => 'Sneakers Hitam 42',
            'variasi_barang' => 'Hitam / 42',
            'satuan' => 'pasang',
            'lokasi' => 'GDG-RUN',
            'hpp' => 120000,
        ]);

        // Stock in
        $bm = BarangMasuk::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-RUN-001',
            'data_dimasukan_oleh' => $this->user->name,
            'status' => 'COMPLETED',
        ]);
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bm->id,
            'produk_id' => $this->produk->id,
            'qty_masuk' => 20,
            'satuan' => 'pasang',
            'lokasi_simpan' => 'GDG-RUN',
            'hpp' => 120000,
        ]);
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->rak->id],
            ['qty' => 20]
        );

        $bk = BarangKeluar::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK-RUN-001',
            'dikeluarkan_oleh' => $this->user->name,
            'status' => 'PENDING',
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 2,
            'satuan' => 'pasang',
            'lokasi_pengambilan' => 'GDG-RUN',
            'hpp' => 120000,
        ]);

        $this->picking = PickingService::generatePickingList('BarangKeluar', $bk->id, 'Operator Budi');
    }

    public function test_guest_cannot_access_picking_runner(): void
    {
        $response = $this->get(route('picking.process', $this->picking->id));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_picking_runner(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('picking.process', $this->picking->id));
        $response->assertStatus(200);
        $response->assertSee($this->picking->nomor_picking);
        $response->assertSee('Operator Budi');
        $response->assertSee('Sneakers Hitam 42');
        $response->assertSee('GDG-RUN');
    }

    public function test_scan_barcode_via_runner_component(): void
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Outbound\PickingRunner::class, ['id' => $this->picking->id])
            ->set('scanProduct', 'SPT-RUN-001')
            ->set('scanQty', 2)
            ->call('scanBarcode')
            ->assertSet('scanProduct', '')
            ->assertDispatched('focus-product');

        $detail = $this->picking->fresh()->details->first();
        $this->assertEquals(2, $detail->qty_picked);
        $this->assertTrue((bool)$detail->is_verified);
    }

    public function test_complete_picking_via_runner(): void
    {
        $this->actingAs($this->user);

        // Verify items first
        Livewire::test(\App\Livewire\Outbound\PickingRunner::class, ['id' => $this->picking->id])
            ->set('scanProduct', 'SPT-RUN-001')
            ->set('scanQty', 2)
            ->call('scanBarcode')
            ->call('completePicking');

        $this->assertEquals('COMPLETED', $this->picking->fresh()->status);
    }
}
