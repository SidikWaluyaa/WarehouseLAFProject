<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Outbound\PickingRunner;
use App\Livewire\Penjualan\Index as PenjualanIndex;
use App\Livewire\StockMaster\Index as StockMasterIndex;
use App\Livewire\Supplier\Riwayat as SupplierRiwayat;
use App\Livewire\Transfer\Index as TransferIndex;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\Lokasi;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\StokLokasi;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PickingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AlertNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first();
    }

    /**
     * a. StockMaster: pada kondisi tidak ada produk stok menipis,
     * memanggil aksi kirim WhatsApp TIDAK melempar error dan men-dispatch event alert:warning.
     */
    public function test_stock_master_open_wa_modal_dispatches_alert_warning_when_no_low_stock(): void
    {
        // Pastikan tidak ada produk yang low stock
        Produk::query()->update([
            'is_threshold_active' => false,
            'total_stok' => 100,
            'min_stok' => 10,
        ]);

        $this->assertEquals(0, Produk::lowStock()->count());

        Livewire::actingAs($this->user)
            ->test(StockMasterIndex::class)
            ->call('openWaModal')
            ->assertDispatched('alert:warning', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'Tidak ada produk');
            });
    }

    /**
     * b. PickingRunner:
     * - Scan SKU salah menetapkan pesan error inline ($errorMessage) yang menetap (bukan toast/notify)
     * - Scan SKU benar mengosongkan $errorMessage dan men-dispatch event toast:success
     * - Selesaikan picking dengan item belum lengkap men-dispatch alert:error
     */
    public function test_picking_runner_scan_feedback_and_alerts(): void
    {
        $supplier = Supplier::create([
            'nama_supplier' => 'Supplier Picking Test',
            'kontak' => '0812345678',
            'alamat' => 'Alamat Supplier',
        ]);

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Picking Test',
            'kontak' => '0898765432',
            'alamat' => 'Alamat Pelanggan',
        ]);

        $rak = Lokasi::create([
            'kode_lokasi' => 'GDG-PIK',
            'nama_lokasi' => 'Rak Picking Test',
            'tipe_lokasi' => 'GUDANG',
            'is_active' => true,
        ]);

        $produk = Produk::create([
            'nama_produk' => 'Sepatu Test Picking',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SPT-PIK-001',
            'nama_barang' => 'Sepatu Hitam 40',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'lokasi' => 'GDG-PIK',
            'hpp' => 100000,
        ]);

        $bm = BarangMasuk::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $supplier->id,
            'nomor_nota' => 'BM-PIK-001',
            'data_dimasukan_oleh' => $this->user->name,
            'status' => 'COMPLETED',
        ]);

        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bm->id,
            'produk_id' => $produk->id,
            'qty_masuk' => 10,
            'satuan' => 'pasang',
            'lokasi_simpan' => 'GDG-PIK',
            'hpp' => 100000,
        ]);

        StokLokasi::updateOrCreate(
            ['produk_id' => $produk->id, 'lokasi_id' => $rak->id],
            ['qty' => 10]
        );

        $bk = BarangKeluar::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $pelanggan->id,
            'nomor_nota' => 'BK-PIK-001',
            'dikeluarkan_oleh' => $this->user->name,
            'status' => 'PENDING',
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $produk->id,
            'qty_keluar' => 2,
            'satuan' => 'pasang',
            'hpp' => 100000,
            'lokasi_pengambilan' => 'GDG-PIK',
            'harga_satuan' => 150000,
            'subtotal' => 300000,
        ]);

        $picking = PickingService::generatePickingList('BarangKeluar', $bk->id, $this->user->name);

        $component = Livewire::actingAs($this->user)
            ->test(PickingRunner::class, ['id' => $picking->id]);

        // Verifikasi listener Alpine focus-product terpasang di HTML view
        $component->assertSeeHtml("x-on:focus-product.window");

        // 1. Scan SKU salah: harus menetapkan errorMessage inline dan muncul di blade, dispatch focus-product, bukan toast atau notify
        $component->set('scanProduct', 'SKU-SALAH-999')
            ->set('scanQty', 1)
            ->call('scanBarcode')
            ->assertSet('errorMessage', "Produk dengan SKU 'SKU-SALAH-999' tidak ditemukan.")
            ->assertSee("Produk dengan SKU 'SKU-SALAH-999' tidak ditemukan.")
            ->assertDispatched('focus-product')
            ->assertNotDispatched('toast:error')
            ->assertNotDispatched('notify');

        // 2. Coba selesaikan picking saat item belum lengkap: harus dispatch alert:error modal
        $component->call('completePicking')
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'belum diambil/diverifikasi');
            });

        // 3. Scan SKU benar: errorMessage harus di-clear, dispatch toast:success, dan dispatch focus-product
        $component->set('scanProduct', 'SPT-PIK-001')
            ->set('scanQty', 2)
            ->call('scanBarcode')
            ->assertSet('errorMessage', null)
            ->assertDispatched('toast:success')
            ->assertDispatched('focus-product');
    }

    /**
     * c. Penjualan/Index dan Transfer/Index:
     * render() / mount() tidak lagi men-dispatch toast dari session('success_message').
     */
    public function test_penjualan_and_transfer_index_do_not_dispatch_duplicate_toast_on_mount_or_render(): void
    {
        // Simulasikan session flash dari redirect create
        session()->flash('success_message', 'Transaksi berhasil disimpan!');

        // Penjualan/Index tidak boleh dispatch toast:success dari PHP Livewire
        Livewire::actingAs($this->user)
            ->test(PenjualanIndex::class)
            ->assertOk()
            ->assertNotDispatched('toast:success');

        // Simulasikan lagi session flash untuk Transfer/Index
        session()->flash('success_message', 'Transfer Order berhasil dibuat!');

        // Transfer/Index tidak boleh dispatch toast:success dari PHP Livewire
        Livewire::actingAs($this->user)
            ->test(TransferIndex::class)
            ->assertOk()
            ->assertNotDispatched('toast:success');
    }

    /**
     * d. Supplier/Riwayat: komponen render normal dan tidak men-dispatch event alert/toast kosong.
     */
    public function test_supplier_riwayat_renders_normally_and_does_not_dispatch_empty_alerts(): void
    {
        Livewire::actingAs($this->user)
            ->test(SupplierRiwayat::class)
            ->assertOk()
            ->assertNotDispatched('toast:success')
            ->assertNotDispatched('toast:error')
            ->assertNotDispatched('alert:error')
            ->assertNotDispatched('alert:success');
    }
}
