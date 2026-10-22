<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\Lokasi;
use App\Models\Pelanggan;
use App\Models\PickingList;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PickingService;
use App\Services\StockEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PickingPicTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $operatorUser;
    private Supplier $supplier;
    private Pelanggan $pelanggan;
    private Produk $produk;
    private Lokasi $rak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first();

        $this->operatorUser = User::create([
            'name' => 'Operator Gudang Alpha',
            'username' => 'operator_alpha',
            'email' => 'alpha@lafmarket.com',
            'password' => bcrypt('password'),
            'role' => 'warehouse',
        ]);

        $this->supplier = Supplier::create([
            'nama_supplier' => 'Supplier PIC Test',
            'kontak' => '0812345678',
            'alamat' => 'Alamat PIC Supplier',
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan PIC Test',
            'kontak' => '0898765432',
            'alamat' => 'Alamat Pelanggan PIC',
        ]);

        $this->rak = Lokasi::create([
            'kode_lokasi' => 'GDG-R99',
            'nama_lokasi' => 'Rak 99 Test',
            'tipe_lokasi' => 'GUDANG',
            'is_active' => true,
        ]);

        $this->produk = Produk::create([
            'nama_produk' => 'Kemeja Flannel PIC',
            'kategori' => 'KEMEJA',
            'kode_barang' => 'KMJ-FLN-001',
            'nama_barang' => 'Kemeja Flannel Merah M',
            'variasi_barang' => 'Merah / M',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-R99',
            'hpp' => 50000,
        ]);

        // Stock in
        $bm = BarangMasuk::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-PIC-001',
            'data_dimasukan_oleh' => $this->user->name,
            'status' => 'COMPLETED',
        ]);
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bm->id,
            'produk_id' => $this->produk->id,
            'qty_masuk' => 50,
            'satuan' => 'pcs',
            'lokasi_simpan' => 'GDG-R99',
            'hpp' => 50000,
        ]);
        \App\Models\StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->rak->id],
            ['qty' => 50]
        );
    }

    private function createSampleBarangKeluar(): BarangKeluar
    {
        $bk = BarangKeluar::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK-PIC-' . uniqid(),
            'dikeluarkan_oleh' => $this->user->name,
            'status' => 'PENDING',
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 5,
            'satuan' => 'pcs',
            'lokasi_pengambilan' => 'GDG-R99',
            'hpp' => 50000,
        ]);

        return $bk;
    }

    public function test_picking_can_be_generated_with_pic(): void
    {
        $bk = $this->createSampleBarangKeluar();

        $picking = PickingService::generatePickingList('BarangKeluar', $bk->id, 'Budi Freelance');

        $this->assertInstanceOf(PickingList::class, $picking);
        $this->assertEquals('Budi Freelance', $picking->picker_name);
        $this->assertEquals('PENDING', $picking->status);
    }

    public function test_picking_can_be_generated_unassigned(): void
    {
        $bk = $this->createSampleBarangKeluar();

        $picking = PickingService::generatePickingList('BarangKeluar', $bk->id, null);

        $this->assertInstanceOf(PickingList::class, $picking);
        $this->assertNull($picking->picker_name);
    }

    public function test_assign_picker_service_updates_pic_and_writes_audit_log(): void
    {
        $this->actingAs($this->user);
        $bk = $this->createSampleBarangKeluar();

        $picking = PickingService::generatePickingList('BarangKeluar', $bk->id, null);
        $this->assertNull($picking->picker_name);

        $updatedPicking = PickingService::assignPicker($picking->id, 'Operator Budi');
        $this->assertEquals('Operator Budi', $updatedPicking->picker_name);

        $log = AuditLog::where('auditable_type', PickingList::class)
            ->where('auditable_id', $picking->id)
            ->where('action', 'ASSIGN_PICKER')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('Operator Budi', $log->new_values['picker_name']);
    }

    public function test_cannot_assign_picker_when_picking_is_completed(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('PIC tidak dapat diubah karena order picking sudah selesai (COMPLETED)');

        $bk = $this->createSampleBarangKeluar();
        $picking = PickingService::generatePickingList('BarangKeluar', $bk->id, 'Ahmad');

        // Mark completed
        $picking->update(['status' => 'COMPLETED']);

        PickingService::assignPicker($picking->id, 'Budi Baru');
    }

    public function test_start_picking_preserves_preassigned_picker_if_null_passed(): void
    {
        $bk = $this->createSampleBarangKeluar();
        $picking = PickingService::generatePickingList('BarangKeluar', $bk->id, 'Preassigned PIC');

        $started = PickingService::startPicking($picking->id, null);
        $this->assertEquals('Preassigned PIC', $started->picker_name);
        $this->assertEquals('PICKING', $started->status);
    }

    public function test_livewire_generate_modal_workflow(): void
    {
        $this->actingAs($this->user);
        $bk = $this->createSampleBarangKeluar();

        Livewire::test(\App\Livewire\Outbound\PickingManager::class)
            ->call('openGenerateModal', 'BarangKeluar', $bk->id)
            ->assertSet('isGenerateModalOpen', true)
            ->assertSet('genRefType', 'BarangKeluar')
            ->assertSet('genRefId', $bk->id)
            ->set('genPicType', 'user')
            ->set('genPicUserId', $this->operatorUser->id)
            ->call('confirmGeneratePicking')
            ->assertSet('isGenerateModalOpen', false);

        $picking = PickingList::where('referensi_id', $bk->id)
            ->where('referensi_tipe', 'BarangKeluar')
            ->first();
        $this->assertNotNull($picking);
        $this->assertEquals($this->operatorUser->name, $picking->picker_name);
    }

    public function test_livewire_reassign_pic_modal(): void
    {
        $this->actingAs($this->user);
        $bk = $this->createSampleBarangKeluar();
        $picking = PickingService::generatePickingList('BarangKeluar', $bk->id, null);

        Livewire::test(\App\Livewire\Outbound\PickingManager::class)
            ->call('openAssignModal', $picking->id)
            ->assertSet('isAssignModalOpen', true)
            ->assertSet('assignPickingId', $picking->id)
            ->set('selectedPicType', 'manual')
            ->set('manualPicName', 'Kru Harian Siti')
            ->call('saveAssignPic')
            ->assertSet('isAssignModalOpen', false);

        $picking->refresh();
        $this->assertEquals('Kru Harian Siti', $picking->picker_name);
    }

    public function test_livewire_filter_by_pic_and_my_tasks(): void
    {
        $this->actingAs($this->user);
        $bk1 = $this->createSampleBarangKeluar();
        $bk2 = $this->createSampleBarangKeluar();

        $pickingMine = PickingService::generatePickingList('BarangKeluar', $bk1->id, $this->user->name);
        $pickingOther = PickingService::generatePickingList('BarangKeluar', $bk2->id, 'Other Person');

        // Test filter by PIC
        Livewire::test(\App\Livewire\Outbound\PickingManager::class)
            ->set('filterPic', $this->user->name)
            ->assertSee($pickingMine->nomor_picking)
            ->assertDontSee($pickingOther->nomor_picking);

        // Test toggle "Tugas Saya"
        Livewire::test(\App\Livewire\Outbound\PickingManager::class)
            ->call('toggleMyTasks')
            ->assertSet('filterOnlyMyTasks', true)
            ->assertSee($pickingMine->nomor_picking)
            ->assertDontSee($pickingOther->nomor_picking);
    }

    public function test_livewire_pagination_and_per_page_options(): void
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Outbound\PickingManager::class)
            ->assertSet('perPage', 10)
            ->assertSet('perPagePending', 10)
            ->set('perPage', 25)
            ->assertSet('perPage', 25)
            ->set('perPage', 50)
            ->assertSet('perPage', 50)
            ->set('perPage', 100)
            ->assertSet('perPage', 100)
            ->set('activeTab', 'pending_outbound')
            ->set('perPagePending', 25)
            ->assertSet('perPagePending', 25)
            ->set('perPagePending', 50)
            ->assertSet('perPagePending', 50)
            ->set('perPagePending', 100)
            ->assertSet('perPagePending', 100);
    }
}

