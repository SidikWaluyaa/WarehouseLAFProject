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
use App\Livewire\Pembelian\RiwayatTanggal as RiwayatMasukTanggal;
use App\Livewire\Penjualan\RiwayatTanggal as RiwayatKeluarTanggal;
use Tests\TestCase;

class RiwayatTanggalTest extends TestCase
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

        // Create test Supplier
        $this->supplier = Supplier::create([
            'nama_supplier' => 'Financial Supplier',
            'kontak' => '081234567890',
            'alamat' => 'Alamat Supplier',
        ]);

        // Create test Pelanggan
        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Financial Customer',
            'kontak' => '08987654321',
            'alamat' => 'Alamat Pelanggan',
        ]);

        // Create products with different HPP values
        $this->produk1 = Produk::create([
            'nama_produk' => 'Sepatu Premium',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SPT-PREM-001',
            'nama_barang' => 'Sepatu Premium Hitam',
            'variasi_barang' => 'Hitam / 42',
            'satuan' => 'pasang',
            'lokasi' => 'GUDANG',
            'hpp' => 100000,
        ]);

        $this->produk2 = Produk::create([
            'nama_produk' => 'T-Shirt Casual',
            'kategori' => 'BAJU',
            'kode_barang' => 'TSH-CAS-001',
            'nama_barang' => 'Kaos Casual Putih',
            'variasi_barang' => 'Putih / L',
            'satuan' => 'pcs',
            'lokasi' => 'TOKO',
            'hpp' => 50000,
        ]);
    }

    public function test_riwayat_tanggal_pages_require_auth(): void
    {
        $this->get('/barang-masuk/riwayat-tanggal')->assertRedirect('/login');
        $this->get('/barang-keluar/riwayat-tanggal')->assertRedirect('/login');
    }

    public function test_can_render_riwayat_tanggal_pages(): void
    {
        $this->actingAs($this->user)->get('/barang-masuk/riwayat-tanggal')->assertStatus(200)->assertSee('TOTAL NILAI BARANG MASUK');
        $this->actingAs($this->user)->get('/barang-keluar/riwayat-tanggal')->assertStatus(200)->assertSee('TOTAL NILAI BARANG KELUAR');
    }

    public function test_riwayat_masuk_tanggal_calculates_correct_grand_total_and_filters(): void
    {
        // 1. Create incoming transactions
        $bmHeader = BarangMasuk::create([
            'tanggal' => '2026-06-15',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-FIN1',
            'data_dimasukan_oleh' => 'Operator Fin',
        ]);

        // 10 units * Rp 100.000 = Rp 1.000.000
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bmHeader->id,
            'produk_id' => $this->produk1->id,
            'qty_masuk' => 10,
            'satuan' => 'pasang',
            'lokasi_simpan' => 'GUDANG',
            'hpp' => 100000,
        ]);

        // 5 units * Rp 50.000 = Rp 250.000
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bmHeader->id,
            'produk_id' => $this->produk2->id,
            'qty_masuk' => 5,
            'satuan' => 'pcs',
            'lokasi_simpan' => 'TOKO',
            'hpp' => 50000,
        ]);

        // Total should be: Rp 1.250.000 (excluding initial seed data because of date filter)
        Livewire::actingAs($this->user)
            ->test(RiwayatMasukTanggal::class)
            ->set('tanggalStart', '2026-06-12')
            ->set('tanggalEnd', '2026-06-18')
            ->assertViewHas('grandTotal', 1250000.00)
            ->assertSee('Rp 1.250.000')
            // Filter by GUDANG only (should leave only produk1 -> Rp 1.000.000)
            ->set('lokasiFilter', 'GUDANG')
            ->assertViewHas('grandTotal', 1000000.00)
            ->assertSee('Rp 1.000.000')
            ->assertDontSee('Kaos Casual Putih');
    }

    public function test_riwayat_keluar_tanggal_calculates_correct_grand_total_and_filters(): void
    {
        // 1. Create outgoing transactions
        $bkHeader = BarangKeluar::create([
            'tanggal' => '2026-06-15',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK-FIN1',
            'dikeluarkan_oleh' => 'Operator Fin',
        ]);

        // 3 units * Rp 100.000 = Rp 300.000
        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bkHeader->id,
            'produk_id' => $this->produk1->id,
            'qty_keluar' => 3,
            'satuan' => 'pasang',
            'lokasi_pengambilan' => 'GUDANG',
            'hpp' => 100000,
        ]);

        // 4 units * Rp 50.000 = Rp 200.000
        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bkHeader->id,
            'produk_id' => $this->produk2->id,
            'qty_keluar' => 4,
            'satuan' => 'pcs',
            'lokasi_pengambilan' => 'TOKO',
            'hpp' => 50000,
        ]);

        // Total should be: Rp 500.000
        Livewire::actingAs($this->user)
            ->test(RiwayatKeluarTanggal::class)
            ->set('tanggalStart', '2026-06-12')
            ->set('tanggalEnd', '2026-06-18')
            ->assertViewHas('grandTotal', 500000.00)
            ->assertSee('Rp 500.000')
            // Filter by general search "Premium" (should filter out produk2 -> Rp 300.000)
            ->set('search', 'Premium')
            ->assertViewHas('grandTotal', 300000.00)
            ->assertSee('Rp 300.000')
            ->assertDontSee('Kaos Casual Putih');
    }
}
