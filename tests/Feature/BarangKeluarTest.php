<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\AnalyticsProduk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Penjualan\Create as PenjualanCreate;
use Tests\TestCase;

class BarangKeluarTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Pelanggan $pelanggan;
    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->user = User::factory()->create([
            'name' => 'Sales Test'
        ]);
        $this->user->assignRole('sales');

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Toko Test Customer',
            'kontak' => '0823456',
            'alamat' => 'Alamat Customer',
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
        $response = $this->get('/barang-keluar/create');
        $response->assertRedirect('/login');
    }

    public function test_can_render_create_page_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get('/barang-keluar/create');
        $response->assertStatus(200);
        $response->assertSee('Pencatatan Barang Keluar');
    }

    public function test_can_add_item_by_scanning_barcode(): void
    {
        Livewire::actingAs($this->user)
            ->test(PenjualanCreate::class)
            ->set('barcodeScan', 'SDL-JEP')
            ->call('addBarcodeItem')
            ->assertSet('items.0.produk_id', $this->produk->id)
            ->assertSet('items.0.qty_keluar', 1)
            ->assertHasNoErrors();
    }

    public function test_scanning_same_barcode_multiple_times_increments_quantity(): void
    {
        Livewire::actingAs($this->user)
            ->test(PenjualanCreate::class)
            ->set('barcodeScan', 'SDL-JEP')
            ->call('addBarcodeItem')
            ->set('barcodeScan', 'SDL-JEP')
            ->call('addBarcodeItem')
            ->assertSet('items.0.produk_id', $this->produk->id)
            ->assertSet('items.0.qty_keluar', 2)
            ->assertHasNoErrors();
    }

    public function test_fails_when_scanning_invalid_barcode(): void
    {
        Livewire::actingAs($this->user)
            ->test(PenjualanCreate::class)
            ->set('barcodeScan', 'INVALID-CODE')
            ->call('addBarcodeItem')
            ->assertHasErrors('barcodeScan');
    }

    public function test_cannot_save_when_stock_is_insufficient(): void
    {
        Livewire::actingAs($this->user)
            ->test(PenjualanCreate::class)
            ->set('tanggal', '2026-06-16')
            ->set('tipe_nota', 'Penjualan')
            ->set('pelanggan_id', $this->pelanggan->id)
            ->set('nomor_nota', 'INV-OUT-001')
            ->set('items', [
                [
                    'produk_id' => $this->produk->id,
                    'qty_keluar' => 15,
                    'lokasi_pengambilan' => 'GUDANG',
                ]
            ])
            ->call('store')
            ->assertHasErrors('items.0.qty_keluar');

        $this->produk->refresh();
        $this->assertEquals(10, $this->produk->total_stok);
    }

    public function test_can_save_transaction_successfully(): void
    {
        Livewire::actingAs($this->user)
            ->test(PenjualanCreate::class)
            ->set('tanggal', '2026-06-16')
            ->set('tipe_nota', 'Penjualan')
            ->set('pelanggan_id', $this->pelanggan->id)
            ->set('nomor_nota', 'INV-OUT-001')
            ->set('items', [
                [
                    'produk_id' => $this->produk->id,
                    'qty_keluar' => 4,
                    'lokasi_pengambilan' => 'GUDANG',
                ]
            ])
            ->call('store')
            ->assertHasNoErrors()
            ->assertRedirect('/barang-keluar');

        $this->produk->refresh();
        $this->assertEquals(6, $this->produk->total_stok);
        $this->assertEquals(4, $this->produk->stok_keluar);
    }

    public function test_can_save_transaction_with_auto_generated_nomor_nota(): void
    {
        Livewire::actingAs($this->user)
            ->test(PenjualanCreate::class)
            ->set('tanggal', '2026-06-16')
            ->set('tipe_nota', 'Penjualan')
            ->set('pelanggan_id', $this->pelanggan->id)
            ->assertSet('nomor_nota', 'BK/20260616/0001')
            ->set('items', [
                [
                    'produk_id' => $this->produk->id,
                    'qty_keluar' => 4,
                    'lokasi_pengambilan' => 'GUDANG',
                ]
            ])
            ->call('store')
            ->assertHasNoErrors()
            ->assertRedirect('/barang-keluar');

        $this->assertDatabaseHas('trx_barang_keluar_header', [
            'nomor_nota' => 'BK/20260616/0001',
            'pelanggan_id' => $this->pelanggan->id,
        ]);
    }

    public function test_can_delete_barang_keluar_transaction_and_reverse_stock(): void
    {
        $lokasi = \App\Models\Lokasi::where('kode_lokasi', 'GUDANG')->first();
        if (!$lokasi) {
            $lokasi = \App\Models\Lokasi::create([
                'kode_lokasi' => 'GUDANG',
                'nama_lokasi' => 'Gudang Utama',
                'tipe_lokasi' => 'GUDANG',
                'is_active' => true,
            ]);
        }

        $stokLokasi = \App\Models\StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $lokasi->id],
            ['qty' => 7]
        );

        $header = \App\Models\BarangKeluar::create([
            'tanggal' => '2026-06-16',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK-DELETE-001',
            'dikeluarkan_oleh' => 'Sales Test',
            'is_qc_passed' => false,
        ]);

        $detail = $header->details()->create([
            'produk_id' => $this->produk->id,
            'qty_keluar' => 3,
            'satuan' => $this->produk->satuan,
            'lokasi_pengambilan' => 'GUDANG',
            'hpp' => $this->produk->hpp,
        ]);

        $this->produk->stok_keluar += 3;
        $this->produk->total_stok = 7;
        $this->produk->save();

        $this->produk->refresh();
        $this->assertEquals(7, $this->produk->total_stok);

        // Call confirmedDelete on the Index component
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Penjualan\Index::class)
            ->call('confirmedDelete', ['id' => $header->id])
            ->assertHasNoErrors();

        // Verify stock is restored
        $this->produk->refresh();
        $this->assertEquals(10, $this->produk->total_stok);
        $this->assertEquals(0, $this->produk->stok_keluar);

        $stokLokasi->refresh();
        $this->assertEquals(10, $stokLokasi->qty);

        // Verify transaction is deleted from DB
        $this->assertDatabaseMissing('trx_barang_keluar_header', [
            'id' => $header->id
        ]);
        $this->assertDatabaseMissing('trx_barang_keluar_detail', [
            'id' => $detail->id
        ]);
    }

    public function test_cannot_delete_qc_passed_barang_keluar_transaction(): void
    {
        $header = \App\Models\BarangKeluar::create([
            'tanggal' => '2026-06-16',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK-DELETE-002',
            'dikeluarkan_oleh' => 'Sales Test',
            'is_qc_passed' => true,
        ]);

        $detail = $header->details()->create([
            'produk_id' => $this->produk->id,
            'qty_keluar' => 3,
            'satuan' => $this->produk->satuan,
            'lokasi_pengambilan' => 'GUDANG',
            'hpp' => $this->produk->hpp,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Penjualan\Index::class)
            ->call('confirmedDelete', ['id' => $header->id])
            ->assertHasNoErrors();

        // Verify transaction is NOT deleted
        $this->assertDatabaseHas('trx_barang_keluar_header', [
            'id' => $header->id
        ]);
    }
}
