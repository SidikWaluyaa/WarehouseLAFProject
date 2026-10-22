<?php

namespace Tests\Feature;

use App\Livewire\Calendar\Index as CalendarIndex;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\Pelanggan;
use App\Models\PoDetail;
use App\Models\PoHeader;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Supplier $supplier;
    protected Pelanggan $pelanggan;
    protected Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
        $this->user = User::factory()->create();
        $this->user->assignRole($adminRole);

        $this->supplier = Supplier::create([
            'nama_supplier' => 'PT Mitra Sejati',
            'kontak' => 'Budi',
            'no_hp' => '08123456789',
            'alamat' => 'Jakarta',
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Toko Makmur',
            'kontak' => 'Siti',
            'no_hp' => '08987654321',
            'alamat' => 'Bandung',
        ]);

        $this->produk = Produk::create([
            'nama_produk' => 'Sepatu Sneakers LAF',
            'kategori' => 'Sepatu',
            'kategori_2' => 'Produk Berjalan',
            'kode_barang' => 'TEST-SKU-001',
            'nama_barang' => 'Sepatu Sneakers LAF 40',
            'warna' => 'Hitam',
            'size' => '40',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'hpp' => 150000,
            'stok_masuk' => 10,
            'stok_keluar' => 0,
            'total_stok' => 10,
            'lokasi' => 'GUDANG',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_calendar()
    {
        $response = $this->get(route('kalender'));
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_calendar()
    {
        $response = $this->actingAs($this->user)->get(route('kalender'));
        $response->assertStatus(200);
        $response->assertSee('Kalender Transaksi');
    }

    public function test_calendar_month_navigation()
    {
        Livewire::actingAs($this->user)
            ->test(CalendarIndex::class, ['date' => '2026-10-04'])
            ->assertSet('currentMonth', 10)
            ->assertSet('currentYear', 2026)
            ->call('nextMonth')
            ->assertSet('currentMonth', 11)
            ->assertSet('currentYear', 2026)
            ->call('previousMonth')
            ->assertSet('currentMonth', 10)
            ->assertSet('currentYear', 2026)
            ->call('goToToday')
            ->assertSet('currentMonth', (int) now()->month)
            ->assertSet('currentYear', (int) now()->year);
    }

    public function test_calendar_date_selection_loads_transaction_details()
    {
        $targetDate = '2026-10-15';

        // 1. Buat Barang Masuk
        $bm = BarangMasuk::create([
            'tanggal' => $targetDate,
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-20261015-001',
            'data_dimasukan_oleh' => 'Admin Test',
        ]);

        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bm->id,
            'produk_id' => $this->produk->id,
            'qty_masuk' => 25,
            'satuan' => 'pasang',
            'hpp' => 150000,
            'lokasi_simpan' => 'GUDANG',
        ]);

        // 2. Buat Barang Keluar
        $bk = BarangKeluar::create([
            'tanggal' => $targetDate,
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK-20261015-001',
            'dikeluarkan_oleh' => 'Admin Test',
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 10,
            'satuan' => 'pasang',
            'hpp' => 150000,
            'lokasi_pengambilan' => 'GUDANG',
        ]);

        // 3. Buat Purchase Order
        $po = PoHeader::create([
            'nomor_po' => 'PO/20261015/001',
            'tanggal' => $targetDate,
            'supplier_id' => $this->supplier->id,
            'artikel' => 'ART-CAL-01',
            'jumlah' => 50,
        ]);

        PoDetail::create([
            'po_header_id' => $po->id,
            'produk_id' => $this->produk->id,
            'jumlah' => 50,
            'barang_masuk' => 0,
            'sisa' => 50,
            'hpp' => 150000,
            'total_hpp' => 7500000,
        ]);

        // Test component rendering with date selected
        Livewire::actingAs($this->user)
            ->test(CalendarIndex::class, ['date' => $targetDate])
            ->assertSee('BM-20261015-001')
            ->assertSee('BK-20261015-001')
            ->assertSee('PO/20261015/001')
            ->assertSee('PT Mitra Sejati')
            ->assertSee('Toko Makmur')
            ->assertSee('Sepatu Sneakers LAF 40')
            ->assertSee('+25')
            ->assertSee('-10');
    }

    public function test_calendar_category_filter()
    {
        Livewire::actingAs($this->user)
            ->test(CalendarIndex::class)
            ->assertSet('filterCategory', 'all')
            ->call('setFilterCategory', 'inbound')
            ->assertSet('filterCategory', 'inbound')
            ->call('setFilterCategory', 'outbound')
            ->assertSet('filterCategory', 'outbound')
            ->call('setFilterCategory', 'po')
            ->assertSet('filterCategory', 'po');
    }

    public function test_calendar_dynamic_month_and_year_update_with_clamped_days()
    {
        // Mulai dari 31 Maret 2026
        Livewire::actingAs($this->user)
            ->test(CalendarIndex::class, ['date' => '2026-03-31'])
            ->assertSet('currentMonth', 3)
            ->assertSet('currentYear', 2026)
            ->assertSet('selectedDate', '2026-03-31')
            // Ganti ke Februari (2026 bukan tahun kabisat -> max 28 hari)
            ->set('currentMonth', 2)
            ->assertSet('currentMonth', 2)
            ->assertSet('selectedDate', '2026-02-28')
            // Ganti ke tahun 2024 (tahun kabisat -> Februari max 29 hari)
            ->set('currentYear', 2024)
            ->assertSet('currentYear', 2024)
            // Ganti bulan ke 4 (April -> 30 hari)
            ->set('currentMonth', 4)
            ->assertSet('selectedDate', '2024-04-28');
    }

    public function test_calendar_renders_indonesian_month_names_and_years_list()
    {
        Livewire::actingAs($this->user)
            ->test(CalendarIndex::class, ['date' => '2026-10-04'])
            ->assertSee('Januari')
            ->assertSee('Oktober')
            ->assertSee('Desember')
            ->assertSee('2026');
    }
}

