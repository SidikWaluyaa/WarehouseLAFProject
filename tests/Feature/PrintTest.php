<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class PrintTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected BarangMasuk $barangMasuk;
    protected BarangKeluar $barangKeluar;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // 2. Setup Admin User
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);

        // 3. Setup Master Data
        $supplier = Supplier::create([
            'nama_supplier' => 'PT Test Supplier',
            'kontak' => '08123',
            'alamat' => 'Test Alamat'
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Toko Test Pelanggan',
            'kontak' => '08234',
            'alamat' => 'Test Alamat'
        ]);

        $produk = Produk::create([
            'nama_produk' => 'Test Product',
            'kategori' => 'Sandal Dewasa',
            'kode_barang' => 'SDL-TST-001',
            'nama_barang' => 'Sandal Test Keren',
            'warna' => 'Hitam',
            'size' => '40',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'hpp' => 50000.00,
            'stok_masuk' => 10,
            'stok_keluar' => 0,
            'total_stok' => 10,
            'lokasi' => 'GUDANG'
        ]);

        // 4. Setup Transactions
        $this->barangMasuk = BarangMasuk::create([
            'tanggal' => now(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $supplier->id,
            'nomor_nota' => 'BM-TEST-001',
            'data_dimasukan_oleh' => 'Admin'
        ]);

        $this->barangMasuk->details()->create([
            'produk_id' => $produk->id,
            'qty_masuk' => 10,
            'satuan' => 'pasang',
            'lokasi_simpan' => 'GUDANG',
            'hpp' => 50000.00
        ]);

        $this->barangKeluar = BarangKeluar::create([
            'tanggal' => now(),
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $pelanggan->id,
            'nomor_nota' => 'BK-TEST-001',
            'dikeluarkan_oleh' => 'Admin'
        ]);

        $this->barangKeluar->details()->create([
            'produk_id' => $produk->id,
            'qty_keluar' => 2,
            'satuan' => 'pasang',
            'lokasi_pengambilan' => 'GUDANG',
            'hpp' => 50000.00
        ]);
    }

    public function test_guest_cannot_access_print_pages()
    {
        $response = $this->get(route('barang-masuk.print', $this->barangMasuk->id));
        $response->assertRedirect('/login');

        $response = $this->get(route('barang-keluar.print', $this->barangKeluar->id));
        $response->assertRedirect('/login');

        $response = $this->get(route('reports.print'));
        $response->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_access_barang_masuk_print()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('barang-masuk.print', $this->barangMasuk->id));

        $response->assertStatus(200);
        $response->assertViewIs('print.barang-masuk');
        $response->assertSee('BM-TEST-001');
        $response->assertSee('PT Test Supplier');
        $response->assertSee('Sandal Test Keren');
    }

    public function test_authenticated_admin_can_access_barang_keluar_print()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('barang-keluar.print', $this->barangKeluar->id));

        $response->assertStatus(200);
        $response->assertViewIs('print.barang-keluar');
        $response->assertSee('BK-TEST-001');
        $response->assertSee('Toko Test Pelanggan');
        $response->assertSee('Sandal Test Keren');
    }

    public function test_authenticated_admin_can_access_report_prints()
    {
        $reportTypes = ['stock-card', 'stock-movement', 'aging-stock', 'sales-support', 'warehouse-performance'];

        foreach ($reportTypes as $type) {
            $response = $this->actingAs($this->adminUser)
                ->get(route('reports.print', ['reportType' => $type]));

            $response->assertStatus(200);
            $response->assertViewIs('print.report');
        }
    }

    public function test_guest_cannot_access_history_print_pages()
    {
        $response = $this->get(route('barang-masuk.riwayat.print'));
        $response->assertRedirect('/login');

        $response = $this->get(route('barang-masuk.riwayat-tanggal.print'));
        $response->assertRedirect('/login');

        $response = $this->get(route('barang-keluar.riwayat.print'));
        $response->assertRedirect('/login');

        $response = $this->get(route('barang-keluar.riwayat-tanggal.print'));
        $response->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_access_barang_masuk_riwayat_print()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('barang-masuk.riwayat.print'));

        $response->assertStatus(200);
        $response->assertViewIs('print.barang-masuk-riwayat');
        $response->assertSee('BM-TEST-001');
        $response->assertSee('PT Test Supplier');
        $response->assertSee('Sandal Test Keren');
    }

    public function test_authenticated_admin_can_access_barang_masuk_riwayat_tanggal_print()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('barang-masuk.riwayat-tanggal.print'));

        $response->assertStatus(200);
        $response->assertViewIs('print.barang-masuk-riwayat-tanggal');
        $response->assertSee('BM-TEST-001');
        $response->assertSee('PT Test Supplier');
        $response->assertSee('Sandal Test Keren');
    }

    public function test_authenticated_admin_can_access_barang_keluar_riwayat_print()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('barang-keluar.riwayat.print'));

        $response->assertStatus(200);
        $response->assertViewIs('print.barang-keluar-riwayat');
        $response->assertSee('BK-TEST-001');
        $response->assertSee('Toko Test Pelanggan');
        $response->assertSee('Sandal Test Keren');
    }

    public function test_authenticated_admin_can_access_barang_keluar_riwayat_tanggal_print()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('barang-keluar.riwayat-tanggal.print'));

        $response->assertStatus(200);
        $response->assertViewIs('print.barang-keluar-riwayat-tanggal');
        $response->assertSee('BK-TEST-001');
        $response->assertSee('Toko Test Pelanggan');
        $response->assertSee('Sandal Test Keren');
    }

    public function test_authenticated_admin_can_access_purchase_order_print()
    {
        $supplier = Supplier::first();
        $produk = Produk::first();

        $po = \App\Models\PoHeader::create([
            'nomor_po' => 'PO/20261003/9999',
            'tanggal' => '2026-10-03',
            'supplier_id' => $supplier->id,
            'artikel' => 'ART-PRINT-01',
            'jumlah' => 10,
        ]);

        \App\Models\PoDetail::create([
            'po_header_id' => $po->id,
            'produk_id' => $produk->id,
            'jumlah' => 10,
            'barang_masuk' => 4,
            'sisa' => 6,
            'hpp' => 50000.00,
            'total_hpp' => 300000.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('purchase-order.print', $po->id));

        $response->assertStatus(200);
        $response->assertViewIs('print.purchase-order');
        $response->assertSee('PO/20261003/9999');
        $response->assertSee($supplier->nama_supplier);
        $response->assertSee($produk->nama_barang);
    }

    public function test_authenticated_admin_can_access_purchase_order_rekap_print()
    {
        $supplier = Supplier::first();
        $produk = Produk::first();

        $po = \App\Models\PoHeader::create([
            'nomor_po' => 'PO/TEST/FILTER',
            'tanggal' => '2026-10-03',
            'supplier_id' => $supplier->id,
            'artikel' => 'ART-FILTER',
            'jumlah' => 20,
        ]);

        // Item 1: Sisa 10 (outstanding)
        \App\Models\PoDetail::create([
            'po_header_id' => $po->id,
            'produk_id' => $produk->id,
            'jumlah' => 10,
            'barang_masuk' => 0,
            'sisa' => 10,
            'hpp' => 50000.00,
            'total_hpp' => 500000.00,
        ]);

        // Item 2: Sisa 0 (lengkap)
        \App\Models\PoDetail::create([
            'po_header_id' => $po->id,
            'produk_id' => $produk->id,
            'jumlah' => 10,
            'barang_masuk' => 10,
            'sisa' => 0,
            'hpp' => 50000.00,
            'total_hpp' => 0,
        ]);

        // Print with filter=outstanding
        $response = $this->actingAs($this->adminUser)
            ->get(route('purchase-order.rekap.print', ['tab' => 'po_tracking', 'filter' => 'outstanding']));

        $response->assertStatus(200);
        $response->assertViewIs('print.purchase-order-rekap');
        $response->assertSee('Laporan Rekap Tracking & Sisa PO');
        $response->assertSee('Belum Lengkap (Outstanding)');
    }
}
