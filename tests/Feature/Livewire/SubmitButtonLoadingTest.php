<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Analytics\Index as AnalyticsIndex;
use App\Livewire\Lokasi\Index as LokasiIndex;
use App\Livewire\Outbound\PickingManager as PickingManager;
use App\Livewire\Pelanggan\Index as PelangganIndex;
use App\Livewire\Pembelian\Create as PembelianCreate;
use App\Livewire\Pembelian\Edit as PembelianEdit;
use App\Livewire\Pengkodean\Index as PengkodeanIndex;
use App\Livewire\Penjualan\Create as PenjualanCreate;
use App\Livewire\PurchaseOrder\Manager as PurchaseOrderManager;
use App\Livewire\StockMaster\Index as StockMasterIndex;
use App\Livewire\Supplier\Index as SupplierIndex;
use App\Livewire\Transfer\Create as TransferCreate;
use App\Livewire\UserManagement\Index as UserManagementIndex;
use App\Models\AnalyticsProduk;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\Lokasi;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubmitButtonLoadingTest extends TestCase
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
     * Helper untuk memverifikasi atribut anti double-submit pada tombol simpan/aksi:
     * - wire:loading.attr="disabled"
     * - wire:target matching method
     * - CSS disabled:opacity-50 & disabled:cursor-not-allowed
     * - Spinner element with wire:loading & wire:target
     * - Menyimpan... / Memproses... text with wire:loading & wire:target
     */
    private function assertSaveButtonHasLoadingState(string $html, string $targetMethod, ?string $clickOrSubmit = null, string $loadingText = 'Menyimpan...'): void
    {
        $this->assertStringContainsString('wire:loading.attr="disabled"', $html);
        $this->assertStringContainsString('wire:target="' . $targetMethod . '"', $html);
        $this->assertStringContainsString('disabled:opacity-50', $html);
        $this->assertStringContainsString('disabled:cursor-not-allowed', $html);
        $this->assertStringContainsString('animate-spin', $html);
        $this->assertStringContainsString($loadingText, $html);

        if ($clickOrSubmit) {
            $this->assertStringContainsString($clickOrSubmit, $html);
        }
    }

    public function test_stock_master_modal_save_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(StockMasterIndex::class)
            ->call('create');

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'store', 'wire:click="store"');
    }

    public function test_lokasi_modal_save_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(LokasiIndex::class)
            ->call('create');

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'store', 'wire:click="store"');
    }

    public function test_pelanggan_modal_save_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(PelangganIndex::class)
            ->call('create');

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'store', 'wire:click="store"');
    }

    public function test_supplier_modal_save_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(SupplierIndex::class)
            ->call('create');

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'store', 'wire:click="store"');
    }

    public function test_pengkodean_modal_save_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(PengkodeanIndex::class)
            ->call('create');

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'store', 'wire:click="store"');
    }

    public function test_user_management_create_modal_save_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(UserManagementIndex::class)
            ->call('openCreateModal');

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'createUser', 'wire:click="createUser"');
    }

    public function test_user_management_edit_modal_save_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(UserManagementIndex::class)
            ->call('openEditModal', $this->user->id);

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'updateUser', 'wire:click="updateUser"');
    }

    public function test_pembelian_create_save_buttons_have_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(PembelianCreate::class);

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'store', 'wire:click="store"');
    }

    public function test_pembelian_edit_save_buttons_have_loading_protection(): void
    {
        $supplier = Supplier::first();
        $produk = Produk::first();
        $lokasi = Lokasi::first();

        $barangMasuk = BarangMasuk::create([
            'tanggal' => now()->format('Y-m-d'),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $supplier?->id,
            'nomor_nota' => 'BM-TEST-LOADING-001',
            'keterangan_tambahan' => 'Test loading state',
            'data_dimasukan_oleh' => $this->user->name,
        ]);

        BarangMasukDetail::create([
            'barang_masuk_header_id' => $barangMasuk->id,
            'produk_id' => $produk->id,
            'qty_masuk' => 10,
            'satuan' => $produk->satuan ?? 'pcs',
            'lokasi_simpan' => $lokasi->kode_lokasi ?? 'GDG-A01',
            'hpp' => 50000,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(PembelianEdit::class, ['id' => $barangMasuk->id]);

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'update', 'wire:click="update"');
    }

    public function test_penjualan_create_save_buttons_have_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(PenjualanCreate::class);

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'store', 'wire:click="store"');
    }

    public function test_transfer_create_save_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(TransferCreate::class);

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'store', 'type="submit"');
    }

    public function test_stock_master_import_modal_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(StockMasterIndex::class)
            ->call('openImportModal');

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'importExcel', 'wire:click="importExcel"', 'Memproses...');
    }

    public function test_stock_master_wa_modal_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(StockMasterIndex::class)
            ->set('isWaModalOpen', true);

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'sendWaFonnte', 'wire:click="sendWaFonnte"', 'Memproses...');
    }

    public function test_purchase_order_manager_modal_save_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(PurchaseOrderManager::class)
            ->call('openModal');

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'store', 'wire:click="store"', 'Menyimpan...');
    }

    public function test_analytics_modal_save_button_has_loading_protection(): void
    {
        $analytics = AnalyticsProduk::first();
        if (!$analytics) {
            $produk = Produk::first();
            $analytics = AnalyticsProduk::create([
                'produk_id' => $produk->id,
                'leadtime_produksi' => 14,
            ]);
        }

        $component = Livewire::actingAs($this->user)
            ->test(AnalyticsIndex::class)
            ->call('edit', $analytics->id);

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'store', 'wire:click="store"', 'Menyimpan...');
    }

    public function test_picking_manager_assign_modal_save_button_has_loading_protection(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(PickingManager::class)
            ->set('isAssignModalOpen', true);

        $html = $component->html();
        $this->assertSaveButtonHasLoadingState($html, 'saveAssignPic', 'wire:click="saveAssignPic"', 'Menyimpan...');
    }
}
