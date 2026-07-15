<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\Pelanggan;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Pembelian\Riwayat as RiwayatMasuk;
use App\Livewire\Penjualan\Riwayat as RiwayatKeluar;
use Tests\TestCase;

class RiwayatTransaksiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Produk $produk1;
    private Produk $produk2;
    private Supplier $supplier;
    private Pelanggan $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first(); // Grab seeded super admin

        // Create standard test data
        $this->supplier = Supplier::create([
            'nama_supplier' => 'CV Test Supplier',
            'kontak' => '081234567890',
            'alamat' => 'Alamat Supplier',
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Toko Test Pelanggan',
            'kontak' => '08987654321',
            'alamat' => 'Alamat Pelanggan',
        ]);

        $this->produk1 = Produk::create([
            'nama_produk' => 'Sandal Gunung',
            'kategori' => 'SANDAL',
            'kode_barang' => 'LAF-SAN-SG01-001',
            'nama_barang' => 'Sandal Gunung Hitam',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'lokasi' => 'GDG-A01',
            'hpp' => 50000,
        ]);

        $this->produk2 = Produk::create([
            'nama_produk' => 'Tas Backpacker',
            'kategori' => 'TAS',
            'kode_barang' => 'LAF-TAS-TB01-001',
            'nama_barang' => 'Tas Backpacker Merah',
            'variasi_barang' => 'Merah / L',
            'satuan' => 'buah',
            'lokasi' => 'GDG-A02',
            'hpp' => 150000,
        ]);
    }

    public function test_riwayat_pages_require_authentication(): void
    {
        $this->get('/barang-masuk/riwayat')->assertRedirect('/login');
        $this->get('/barang-keluar/riwayat')->assertRedirect('/login');
    }

    public function test_can_render_riwayat_pages_when_authenticated(): void
    {
        $this->actingAs($this->user)->get('/barang-masuk/riwayat')->assertStatus(200)->assertSee('Riwayat Detail Barang');
        $this->actingAs($this->user)->get('/barang-keluar/riwayat')->assertStatus(200)->assertSee('Riwayat Detail Barang');
    }

    public function test_can_filter_riwayat_masuk_by_product(): void
    {
        // 1. Create incoming transactions
        $bmHeader = BarangMasuk::create([
            'tanggal' => '2026-06-19',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-20260619-0001',
            'data_dimasukan_oleh' => 'Test Operator',
        ]);

        $detail1 = BarangMasukDetail::create([
            'barang_masuk_header_id' => $bmHeader->id,
            'produk_id' => $this->produk1->id,
            'qty_masuk' => 10,
            'satuan' => 'pasang',
            'lokasi_simpan' => 'TOKO',
            'hpp' => 50000,
        ]);

        $detail2 = BarangMasukDetail::create([
            'barang_masuk_header_id' => $bmHeader->id,
            'produk_id' => $this->produk2->id,
            'qty_masuk' => 5,
            'satuan' => 'buah',
            'lokasi_simpan' => 'GUDANG',
            'hpp' => 150000,
        ]);

        // 2. Test Livewire filtering
        Livewire::actingAs($this->user)
            ->test(RiwayatMasuk::class)
            ->assertSee('Sandal Gunung Hitam')
            ->assertSee('Tas Backpacker Merah')
            ->call('selectProduct', $this->produk1->id)
            ->assertSet('selectedProductId', $this->produk1->id)
            ->assertSee('Sandal Gunung Hitam')
            ->assertDontSee('Tas Backpacker Merah')
            ->call('deselectProduct')
            ->assertSet('selectedProductId', null)
            ->assertSee('Tas Backpacker Merah');
    }

    public function test_can_filter_riwayat_keluar_by_date_and_location(): void
    {
        // 1. Create outgoing transactions
        $bkHeader1 = BarangKeluar::create([
            'tanggal' => '2026-06-10',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK-20260610-0001',
            'dikeluarkan_oleh' => 'Test Operator',
        ]);

        $bkHeader2 = BarangKeluar::create([
            'tanggal' => '2026-06-18',
            'tipe_nota' => 'Sample',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK-20260618-0001',
            'dikeluarkan_oleh' => 'Test Operator',
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bkHeader1->id,
            'produk_id' => $this->produk1->id,
            'qty_keluar' => 2,
            'satuan' => 'pasang',
            'lokasi_pengambilan' => 'TOKO',
            'hpp' => 50000,
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bkHeader2->id,
            'produk_id' => $this->produk2->id,
            'qty_keluar' => 1,
            'satuan' => 'buah',
            'lokasi_pengambilan' => 'GUDANG',
            'hpp' => 150000,
        ]);

        // 2. Test Livewire filter checks
        Livewire::actingAs($this->user)
            ->test(RiwayatKeluar::class)
            // Date filter start 2026-06-12 should hide bkHeader1 (2026-06-10)
            ->set('tanggalStart', '2026-06-12')
            ->assertSee('Tas Backpacker Merah')
            ->assertDontSee('Sandal Gunung Hitam')
            // Reset dates and check location filter GUDANG (should show only produk2)
            ->set('tanggalStart', '')
            ->set('lokasiFilter', 'GUDANG')
            ->assertSee('Tas Backpacker Merah')
            ->assertDontSee('Sandal Gunung Hitam')
            // Check Tipe Nota filter
            ->set('lokasiFilter', '')
            ->set('tipeFilter', 'Penjualan')
            ->assertSee('Sandal Gunung Hitam')
            ->assertDontSee('Tas Backpacker Merah');
    }

    public function test_can_autocomplete_search_products(): void
    {
        $test = Livewire::actingAs($this->user)
            ->test(RiwayatMasuk::class)
            ->set('productSearch', 'Sandal');

        $this->assertGreaterThanOrEqual(1, count($test->get('products')));

        $test->set('productSearch', 'nonexistent');
        $this->assertCount(0, $test->get('products'));
    }
}
