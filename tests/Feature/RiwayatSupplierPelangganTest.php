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
use App\Livewire\Supplier\Riwayat as RiwayatSupplier;
use App\Livewire\Pelanggan\Riwayat as RiwayatPelanggan;
use Tests\TestCase;

class RiwayatSupplierPelangganTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Produk $produk1;
    private Produk $produk2;
    private Supplier $supplier1;
    private Supplier $supplier2;
    private Pelanggan $pelanggan1;
    private Pelanggan $pelanggan2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first(); // Grab seeded super admin

        // Create test Suppliers
        $this->supplier1 = Supplier::create([
            'nama_supplier' => 'Aris Supplier Sandal',
            'kontak' => '081111111111',
            'alamat' => 'Semarang',
        ]);
        $this->supplier2 = Supplier::create([
            'nama_supplier' => 'Budi Supplier Tas',
            'kontak' => '082222222222',
            'alamat' => 'Bandung',
        ]);

        // Create test Pelanggan
        $this->pelanggan1 = Pelanggan::create([
            'nama_pelanggan' => 'Toko Charlie',
            'kontak' => '083333333333',
            'alamat' => 'Surabaya',
        ]);
        $this->pelanggan2 = Pelanggan::create([
            'nama_pelanggan' => 'Ritel Delta',
            'kontak' => '084444444444',
            'alamat' => 'Jakarta',
        ]);

        // Create products
        $this->produk1 = Produk::create([
            'nama_produk' => 'Sandal Slide',
            'kategori' => 'SANDAL',
            'kode_barang' => 'SDL-SLI-BLK-40',
            'nama_barang' => 'Sandal Slide Hitam',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'lokasi' => 'GUDANG',
            'hpp' => 30000,
        ]);
        $this->produk2 = Produk::create([
            'nama_produk' => 'Tas Backpack',
            'kategori' => 'TAS',
            'kode_barang' => 'TAS-BAC-RED-L',
            'nama_barang' => 'Tas Backpack Merah',
            'variasi_barang' => 'Merah / L',
            'satuan' => 'buah',
            'lokasi' => 'TOKO',
            'hpp' => 120000,
        ]);
    }

    public function test_riwayat_supplier_pelanggan_require_auth(): void
    {
        $this->get('/supplier/riwayat')->assertRedirect('/login');
        $this->get('/pelanggan/riwayat')->assertRedirect('/login');
    }

    public function test_can_render_riwayat_supplier_pelanggan_pages(): void
    {
        $this->actingAs($this->user)->get('/supplier/riwayat')->assertStatus(200)->assertSee('Riwayat Supplier');
        $this->actingAs($this->user)->get('/pelanggan/riwayat')->assertStatus(200)->assertSee('Riwayat Pelanggan');
    }

    public function test_can_autocomplete_search_suppliers_and_customers(): void
    {
        // 1. Test Supplier autocomplete
        $testSupplier = Livewire::actingAs($this->user)
            ->test(RiwayatSupplier::class)
            ->set('supplierSearch', 'Aris');

        $this->assertGreaterThanOrEqual(1, count($testSupplier->get('suppliers')));
        $testSupplier->set('supplierSearch', 'nonexistent');
        $this->assertCount(0, $testSupplier->get('suppliers'));

        // 2. Test Pelanggan autocomplete
        $testPelanggan = Livewire::actingAs($this->user)
            ->test(RiwayatPelanggan::class)
            ->set('pelangganSearch', 'Charlie');

        $this->assertGreaterThanOrEqual(1, count($testPelanggan->get('pelanggans')));
        $testPelanggan->set('pelangganSearch', 'nonexistent');
        $this->assertCount(0, $testPelanggan->get('pelanggans'));
    }

    public function test_can_filter_riwayat_supplier_by_supplier_and_general_search(): void
    {
        // Setup incoming goods
        $bmHeader1 = BarangMasuk::create([
            'tanggal' => '2026-06-10',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier1->id,
            'nomor_nota' => 'BM-SUP1',
            'data_dimasukan_oleh' => 'Test Operator',
        ]);
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bmHeader1->id,
            'produk_id' => $this->produk1->id,
            'qty_masuk' => 20,
            'satuan' => 'pasang',
            'lokasi_simpan' => 'GUDANG',
            'hpp' => 30000,
        ]);

        $bmHeader2 = BarangMasuk::create([
            'tanggal' => '2026-06-11',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier2->id,
            'nomor_nota' => 'BM-SUP2',
            'data_dimasukan_oleh' => 'Test Operator',
        ]);
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bmHeader2->id,
            'produk_id' => $this->produk2->id,
            'qty_masuk' => 10,
            'satuan' => 'buah',
            'lokasi_simpan' => 'TOKO',
            'hpp' => 120000,
        ]);

        // Test Livewire filtering
        Livewire::actingAs($this->user)
            ->test(RiwayatSupplier::class)
            ->assertSee('Aris Supplier Sandal')
            ->assertSee('Budi Supplier Tas')
            // Filter by supplier1
            ->call('selectSupplier', $this->supplier1->id)
            ->assertSet('selectedSupplierId', $this->supplier1->id)
            ->assertSee('Aris Supplier Sandal')
            ->assertDontSee('Budi Supplier Tas')
            // Reset and test general search
            ->call('deselectSupplier')
            ->assertSet('selectedSupplierId', null)
            ->set('search', 'Backpack')
            ->assertSee('Tas Backpack Merah')
            ->assertDontSee('Sandal Slide Hitam');
    }

    public function test_can_filter_riwayat_pelanggan_by_pelanggan_and_date(): void
    {
        // Setup outgoing goods
        $bkHeader1 = BarangKeluar::create([
            'tanggal' => '2026-06-10',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan1->id,
            'nomor_nota' => 'BK-PEL1',
            'dikeluarkan_oleh' => 'Test Operator',
        ]);
        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bkHeader1->id,
            'produk_id' => $this->produk1->id,
            'qty_keluar' => 5,
            'satuan' => 'pasang',
            'lokasi_pengambilan' => 'GUDANG',
            'hpp' => 30000,
        ]);

        $bkHeader2 = BarangKeluar::create([
            'tanggal' => '2026-06-15',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan2->id,
            'nomor_nota' => 'BK-PEL2',
            'dikeluarkan_oleh' => 'Test Operator',
        ]);
        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bkHeader2->id,
            'produk_id' => $this->produk2->id,
            'qty_keluar' => 2,
            'satuan' => 'buah',
            'lokasi_pengambilan' => 'TOKO',
            'hpp' => 120000,
        ]);

        // Test Livewire filtering
        Livewire::actingAs($this->user)
            ->test(RiwayatPelanggan::class)
            ->assertSee('Toko Charlie')
            ->assertSee('Ritel Delta')
            // Filter by pelanggan1
            ->call('selectPelanggan', $this->pelanggan1->id)
            ->assertSet('selectedPelangganId', $this->pelanggan1->id)
            ->assertSee('Toko Charlie')
            ->assertDontSee('Ritel Delta')
            // Reset and filter by date
            ->call('deselectPelanggan')
            ->assertSet('selectedPelangganId', null)
            ->set('tanggalStart', '2026-06-12')
            ->assertSee('Ritel Delta')
            ->assertDontSee('Toko Charlie');
    }
}
