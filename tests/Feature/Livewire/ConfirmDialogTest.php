<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Audit\Index as AuditIndex;
use App\Livewire\Lokasi\Index as LokasiIndex;
use App\Livewire\Pelanggan\Index as PelangganIndex;
use App\Livewire\Pembelian\Index as PembelianIndex;
use App\Livewire\Pengkodean\Index as PengkodeanIndex;
use App\Livewire\Penjualan\Index as PenjualanIndex;
use App\Livewire\StockMaster\Index as StockMasterIndex;
use App\Livewire\Supplier\Index as SupplierIndex;
use App\Livewire\Transfer\Detail as TransferDetail;
use App\Livewire\UserManagement\Index as UserManagementIndex;
use App\Models\AturanKode;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\BarangMasuk;
use App\Models\BarangMasukDetail;
use App\Models\Lokasi;
use App\Models\OpnameDetail;
use App\Models\OpnameHeader;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\StokLokasi;
use App\Models\Supplier;
use App\Models\TransferDetail as TransferDetailModel;
use App\Models\TransferHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConfirmDialogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Lokasi $gudangAsal;
    private Lokasi $gudangTujuan;
    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first();

        $this->gudangAsal = Lokasi::create([
            'kode_lokasi' => 'GDG-SRC',
            'nama_lokasi' => 'Gudang Sumber',
            'tipe_lokasi' => 'GUDANG',
            'is_active' => true,
        ]);

        $this->gudangTujuan = Lokasi::create([
            'kode_lokasi' => 'GDG-DST',
            'nama_lokasi' => 'Gudang Tujuan',
            'tipe_lokasi' => 'GUDANG',
            'is_active' => true,
        ]);

        $this->produk = Produk::create([
            'nama_produk' => 'Kemeja Casual Test',
            'kategori' => 'KEMEJA',
            'kode_barang' => 'KMJ-TS-001',
            'nama_barang' => 'Kemeja Putih L',
            'variasi_barang' => 'Putih / L',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-SRC',
            'hpp' => 50000,
            'total_stok' => 50,
        ]);
    }

    private function assertDispatchedActionMatchesListener(string $class, string $method, string $action): void
    {
        $refMethod = new \ReflectionMethod($class, $method);
        $attributes = $refMethod->getAttributes(\Livewire\Attributes\On::class);
        $this->assertNotEmpty($attributes, "Method {$method} on {$class} must have #[On] attribute.");
        $registeredAction = $attributes[0]->getArguments()[0] ?? null;
        $this->assertSame($registeredAction, $action, "Action '{$action}' does not match #[On('{$registeredAction}')] on {$class}::{$method}");
    }

    /*
    |--------------------------------------------------------------------------
    | MODUL 1: Penjualan (Barang Keluar)
    |--------------------------------------------------------------------------
    */

    public function test_penjualan_request_delete_dispatches_confirm_and_does_not_modify_data(): void
    {
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Customer Test Penjualan',
            'kontak' => '08123456789',
        ]);

        $bk = BarangKeluar::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $pelanggan->id,
            'nomor_nota' => 'BK-CONFIRM-001',
            'dikeluarkan_oleh' => $this->user->name,
            'status' => 'PENDING',
            'is_qc_passed' => false,
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 5,
            'satuan' => 'pcs',
            'hpp' => 50000,
            'harga_satuan' => 75000,
            'subtotal' => 375000,
        ]);

        $capturedAction = null;

        Livewire::actingAs($this->user)
            ->test(PenjualanIndex::class)
            ->call('requestDelete', $bk->id)
            ->assertDispatched('alert:confirm', function ($event, $params) use ($bk, &$capturedAction) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'] ?? null;
                return $capturedAction === 'confirmed-delete-barang-keluar'
                    && ($data['destructive'] ?? false) === true
                    && ($data['params']['id'] ?? null) === $bk->id
                    && str_contains($data['text'] ?? '', $bk->nomor_nota);
            });

        $this->assertDispatchedActionMatchesListener(PenjualanIndex::class, 'confirmedDelete', $capturedAction);

        // Pastikan transaksi belum dihapus
        $this->assertDatabaseHas('trx_barang_keluar_header', [
            'id' => $bk->id,
            'nomor_nota' => 'BK-CONFIRM-001',
        ]);
    }

    public function test_penjualan_confirmed_delete_deletes_transaction_and_restores_stock(): void
    {
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Customer Test Penjualan 2',
            'kontak' => '08123456789',
        ]);

        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->gudangAsal->id],
            ['qty' => 20]
        );

        $bk = BarangKeluar::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $pelanggan->id,
            'nomor_nota' => 'BK-CONFIRM-002',
            'dikeluarkan_oleh' => $this->user->name,
            'status' => 'PENDING',
            'is_qc_passed' => false,
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 5,
            'satuan' => 'pcs',
            'hpp' => 50000,
            'harga_satuan' => 75000,
            'subtotal' => 375000,
            'lokasi_pengambilan' => 'GDG-SRC',
        ]);

        Livewire::actingAs($this->user)
            ->test(PenjualanIndex::class)
            ->call('confirmedDelete', ['id' => $bk->id])
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('trx_barang_keluar_header', [
            'id' => $bk->id,
        ]);
    }

    public function test_penjualan_confirmed_delete_rejects_transaction_that_already_passed_qc(): void
    {
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Customer QC Passed',
            'kontak' => '08123456789',
        ]);

        $bk = BarangKeluar::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $pelanggan->id,
            'nomor_nota' => 'BK-CONFIRM-003',
            'dikeluarkan_oleh' => $this->user->name,
            'status' => 'COMPLETED',
            'is_qc_passed' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(PenjualanIndex::class)
            ->call('confirmedDelete', ['id' => $bk->id])
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'QC');
            });

        $this->assertDatabaseHas('trx_barang_keluar_header', [
            'id' => $bk->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MODUL 2: Transfer Detail
    |--------------------------------------------------------------------------
    */

    private function createTransfer(string $status = 'DRAFT'): TransferHeader
    {
        $th = TransferHeader::create([
            'nomor_transfer' => 'TRF-TEST-' . uniqid(),
            'tanggal' => now()->toDateString(),
            'lokasi_asal_id' => $this->gudangAsal->id,
            'lokasi_tujuan_id' => $this->gudangTujuan->id,
            'status' => $status,
            'created_by' => $this->user->name,
        ]);

        TransferDetailModel::create([
            'transfer_header_id' => $th->id,
            'produk_id' => $this->produk->id,
            'qty' => 5,
        ]);

        return $th;
    }

    public function test_transfer_detail_request_and_execute_shipment(): void
    {
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->gudangAsal->id],
            ['qty' => 20]
        );

        $transfer = $this->createTransfer('DRAFT');

        $component = Livewire::actingAs($this->user)
            ->test(TransferDetail::class, ['transfer' => $transfer]);

        $capturedAction = null;

        // 1. Request Shipment -> dispatch alert:confirm, status belum berubah
        $component->call('requestProcessShipment')
            ->assertDispatched('alert:confirm', function ($event, $params) use ($transfer, &$capturedAction) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'] ?? null;
                return $capturedAction === 'confirmed-process-shipment'
                    && ($data['destructive'] ?? false) === false
                    && str_contains($data['text'] ?? '', $transfer->nomor_transfer);
            });

        $this->assertDispatchedActionMatchesListener(TransferDetail::class, 'confirmedProcessShipment', $capturedAction);
        $this->assertEquals('DRAFT', $transfer->fresh()->status);

        // 2. Confirmed Shipment -> status berubah ke IN_TRANSIT
        $component->call('confirmedProcessShipment')
            ->assertDispatched('toast:success');

        $this->assertEquals('IN_TRANSIT', $transfer->fresh()->status);

        // 3. Menolak shipment jika status bukan DRAFT
        $component->call('confirmedProcessShipment')
            ->assertDispatched('alert:error');
    }

    public function test_transfer_detail_request_and_execute_receive_and_close(): void
    {
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->gudangTujuan->id],
            ['qty' => 0]
        );

        $transfer = $this->createTransfer('IN_TRANSIT');

        $component = Livewire::actingAs($this->user)
            ->test(TransferDetail::class, ['transfer' => $transfer]);

        $capturedReceiveAction = null;

        // 1. Request Receive -> dispatch alert:confirm
        $component->call('requestProcessReceive')
            ->assertDispatched('alert:confirm', function ($event, $params) use ($transfer, &$capturedReceiveAction) {
                $data = $params[0] ?? $params;
                $capturedReceiveAction = $data['action'] ?? null;
                return $capturedReceiveAction === 'confirmed-process-receive'
                    && ($data['destructive'] ?? false) === false
                    && str_contains($data['text'] ?? '', $transfer->nomor_transfer);
            });

        $this->assertDispatchedActionMatchesListener(TransferDetail::class, 'confirmedProcessReceive', $capturedReceiveAction);
        $this->assertEquals('IN_TRANSIT', $transfer->fresh()->status);

        // 2. Confirmed Receive -> status berubah ke RECEIVED
        $component->call('confirmedProcessReceive')
            ->assertDispatched('toast:success');

        $this->assertEquals('RECEIVED', $transfer->fresh()->status);

        $capturedCloseAction = null;

        // 3. Request Close -> dispatch alert:confirm
        $component->call('requestCloseTransfer')
            ->assertDispatched('alert:confirm', function ($event, $params) use ($transfer, &$capturedCloseAction) {
                $data = $params[0] ?? $params;
                $capturedCloseAction = $data['action'] ?? null;
                return $capturedCloseAction === 'confirmed-close-transfer'
                    && ($data['destructive'] ?? false) === false
                    && str_contains($data['text'] ?? '', $transfer->nomor_transfer);
            });

        $this->assertDispatchedActionMatchesListener(TransferDetail::class, 'confirmedCloseTransfer', $capturedCloseAction);

        // 4. Confirmed Close -> status berubah ke CLOSED
        $component->call('confirmedCloseTransfer')
            ->assertDispatched('toast:success');

        $this->assertEquals('CLOSED', $transfer->fresh()->status);
    }

    public function test_transfer_detail_request_and_execute_cancel(): void
    {
        $transfer = $this->createTransfer('DRAFT');

        $component = Livewire::actingAs($this->user)
            ->test(TransferDetail::class, ['transfer' => $transfer]);

        $capturedCancelAction = null;

        // 1. Request Cancel -> dispatch alert:confirm with destructive = true
        $component->call('requestCancelTransfer')
            ->assertDispatched('alert:confirm', function ($event, $params) use ($transfer, &$capturedCancelAction) {
                $data = $params[0] ?? $params;
                $capturedCancelAction = $data['action'] ?? null;
                return $capturedCancelAction === 'confirmed-cancel-transfer'
                    && ($data['destructive'] ?? false) === true
                    && str_contains($data['text'] ?? '', $transfer->nomor_transfer);
            });

        $this->assertDispatchedActionMatchesListener(TransferDetail::class, 'confirmedCancelTransfer', $capturedCancelAction);
        $this->assertEquals('DRAFT', $transfer->fresh()->status);

        // 2. Confirmed Cancel -> status berubah ke CANCELLED
        $component->call('confirmedCancelTransfer')
            ->assertDispatched('toast:success');

        $this->assertEquals('CANCELLED', $transfer->fresh()->status);

        // 3. Menolak pembatalan ulang jika status bukan DRAFT
        $component->call('confirmedCancelTransfer')
            ->assertDispatched('alert:error');
    }

    public function test_cancelled_transfer_cannot_be_shipped(): void
    {
        $transfer = $this->createTransfer('CANCELLED');

        Livewire::actingAs($this->user)
            ->test(TransferDetail::class, ['transfer' => $transfer])
            ->call('requestProcessShipment')
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'DRAFT');
            })
            ->call('confirmedProcessShipment')
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'DRAFT');
            });

        $this->assertEquals('CANCELLED', $transfer->fresh()->status);
    }

    public function test_non_draft_transfer_cannot_be_cancelled(): void
    {
        $transferInTransit = $this->createTransfer('IN_TRANSIT');

        Livewire::actingAs($this->user)
            ->test(TransferDetail::class, ['transfer' => $transferInTransit])
            ->call('requestCancelTransfer')
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'DRAFT');
            })
            ->call('confirmedCancelTransfer')
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'DRAFT');
            });

        $this->assertEquals('IN_TRANSIT', $transferInTransit->fresh()->status);
    }

    /*
    |--------------------------------------------------------------------------
    | MODUL 3: Audit (Stock Opname)
    |--------------------------------------------------------------------------
    */

    public function test_audit_request_and_execute_cancel_session(): void
    {
        $session = OpnameHeader::create([
            'nomor_opname' => 'OPN-TEST-' . uniqid(),
            'tanggal' => now()->toDateString(),
            'lokasi_id' => $this->gudangAsal->id,
            'status' => 'DRAFT',
            'operator_name' => $this->user->name,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(AuditIndex::class)
            ->call('loadSession', $session->id);

        $capturedAction = null;

        // 1. Request cancel session -> dispatch alert:confirm with destructive = true
        $component->call('requestCancelSession')
            ->assertDispatched('alert:confirm', function ($event, $params) use ($session, &$capturedAction) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'] ?? null;
                return $capturedAction === 'confirmed-cancel-session'
                    && ($data['destructive'] ?? false) === true
                    && str_contains($data['text'] ?? '', $session->nomor_opname);
            });

        $this->assertDispatchedActionMatchesListener(AuditIndex::class, 'cancelSession', $capturedAction);
        $this->assertEquals('DRAFT', $session->fresh()->status);

        // 2. Confirmed cancel session -> status berubah ke CANCELLED
        $component->call('cancelSession')
            ->assertDispatched('toast:success');

        $this->assertEquals('CANCELLED', $session->fresh()->status);
    }

    public function test_audit_request_and_execute_adjustment_with_summary(): void
    {
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->gudangAsal->id],
            ['qty' => 10]
        );

        $session = OpnameHeader::create([
            'nomor_opname' => 'OPN-ADJ-' . uniqid(),
            'tanggal' => now()->toDateString(),
            'lokasi_id' => $this->gudangAsal->id,
            'status' => 'COMPLETED',
            'operator_name' => $this->user->name,
        ]);

        // Buat detail dengan selisih: sistem 10, fisik 12 (selisih +2)
        OpnameDetail::create([
            'opname_header_id' => $session->id,
            'produk_id' => $this->produk->id,
            'lokasi_id' => $this->gudangAsal->id,
            'qty_sistem' => 10,
            'qty_fisik' => 12,
            'selisih' => 2,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(AuditIndex::class)
            ->call('loadSession', $session->id);

        $capturedAction = null;

        // 1. Request adjustment -> memuat rincian terpisah surplus/defisit, destructive = false, focusCancel = true
        $component->call('requestExecuteAdjustment')
            ->assertDispatched('alert:confirm', function ($event, $params) use ($session, &$capturedAction) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'] ?? null;
                return $capturedAction === 'confirmed-execute-adjustment'
                    && ($data['destructive'] ?? false) === false
                    && ($data['focusCancel'] ?? false) === true
                    && str_contains($data['text'] ?? '', $session->nomor_opname)
                    && str_contains($data['text'] ?? '', 'Surplus 1 item (+2 unit)')
                    && str_contains($data['text'] ?? '', 'Defisit 0 item (-0 unit)');
            });

        $this->assertDispatchedActionMatchesListener(AuditIndex::class, 'executeAdjustment', $capturedAction);
        $this->assertEquals('COMPLETED', $session->fresh()->status);

        // 2. Execute adjustment -> stok disinkronkan ke 12 dan status berubah ke ADJUSTED
        $component->call('executeAdjustment')
            ->assertDispatched('toast:success');

        $this->assertEquals('ADJUSTED', $session->fresh()->status);
        $stokTerkini = StokLokasi::where(['produk_id' => $this->produk->id, 'lokasi_id' => $this->gudangAsal->id])->first();
        $this->assertEquals(12, $stokTerkini->qty);
    }

    public function test_audit_non_completed_session_cannot_execute_adjustment(): void
    {
        $draftSession = OpnameHeader::create([
            'nomor_opname' => 'OPN-DRAFT-' . uniqid(),
            'tanggal' => now()->toDateString(),
            'lokasi_id' => $this->gudangAsal->id,
            'status' => 'DRAFT',
            'operator_name' => $this->user->name,
        ]);

        Livewire::actingAs($this->user)
            ->test(AuditIndex::class)
            ->call('loadSession', $draftSession->id)
            ->call('requestExecuteAdjustment')
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'COMPLETED');
            })
            ->call('executeAdjustment');

        // Status tetap DRAFT dan tidak berubah menjadi ADJUSTED
        $this->assertEquals('DRAFT', $draftSession->fresh()->status);
    }

    /*
    |--------------------------------------------------------------------------
    | GUARD TEST: PENOLAKAN DISPATCH DENGAN ID DOKUMEN LAIN
    |--------------------------------------------------------------------------
    */

    public function test_transfer_detail_dispatch_with_mismatched_id_rejected(): void
    {
        $transfer1 = $this->createTransfer('DRAFT');
        $transfer2 = $this->createTransfer('DRAFT');

        Livewire::actingAs($this->user)
            ->test(TransferDetail::class, ['transfer' => $transfer1])
            ->dispatch('confirmed-process-shipment', id: $transfer2->id)
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'tidak cocok');
            })
            ->dispatch('confirmed-cancel-transfer', id: $transfer2->id)
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'tidak cocok');
            });

        $this->assertEquals('DRAFT', $transfer1->fresh()->status);
        $this->assertEquals('DRAFT', $transfer2->fresh()->status);
    }

    public function test_audit_dispatch_with_mismatched_id_rejected(): void
    {
        $session1 = OpnameHeader::create([
            'nomor_opname' => 'OPN-MISMATCH-1',
            'tanggal' => now()->toDateString(),
            'lokasi_id' => $this->gudangAsal->id,
            'status' => 'DRAFT',
            'operator_name' => $this->user->name,
        ]);

        $session2 = OpnameHeader::create([
            'nomor_opname' => 'OPN-MISMATCH-2',
            'tanggal' => now()->toDateString(),
            'lokasi_id' => $this->gudangAsal->id,
            'status' => 'DRAFT',
            'operator_name' => $this->user->name,
        ]);

        Livewire::actingAs($this->user)
            ->test(AuditIndex::class)
            ->call('loadSession', $session1->id)
            ->dispatch('confirmed-cancel-session', id: $session2->id)
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'tidak cocok');
            })
            ->dispatch('confirmed-execute-adjustment', id: $session2->id)
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'tidak cocok');
            });

        $this->assertEquals('DRAFT', $session1->fresh()->status);
        $this->assertEquals('DRAFT', $session2->fresh()->status);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST DATA-DRIVEN BERBASIS PAYLOAD alert:confirm UNTUK SEMUA AKSI
    |--------------------------------------------------------------------------
    */

    public function test_penjualan_request_and_dispatch_delete_from_payload(): void
    {
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Customer Payload Test',
            'kontak' => '08123456780',
        ]);

        $bk = BarangKeluar::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $pelanggan->id,
            'nomor_nota' => 'BK-PAYLOAD-001',
            'dikeluarkan_oleh' => $this->user->name,
            'status' => 'PENDING',
            'is_qc_passed' => false,
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 2,
            'satuan' => 'pcs',
            'hpp' => 50000,
            'harga_satuan' => 75000,
            'subtotal' => 150000,
            'lokasi_pengambilan' => 'GDG-SRC',
        ]);

        $capturedAction = null;
        $capturedParams = null;

        $component = Livewire::actingAs($this->user)
            ->test(PenjualanIndex::class)
            ->call('requestDelete', $bk->id)
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$capturedAction, &$capturedParams) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'] ?? null;
                $capturedParams = $data['params'] ?? [];
                return true;
            });

        $this->assertNotNull($capturedAction);
        $this->assertNotEmpty($capturedParams);

        // Kirim balik persis payload hasil tangkapan
        $component->dispatch($capturedAction, ...$capturedParams)
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('trx_barang_keluar_header', ['id' => $bk->id]);
    }

    public function test_pembelian_request_and_dispatch_delete_from_payload(): void
    {
        $supplier = Supplier::create([
            'nama_supplier' => 'Supplier Payload Test',
            'kontak' => '08123456788',
        ]);

        $bm = BarangMasuk::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $supplier->id,
            'nomor_nota' => 'BM-PAYLOAD-' . uniqid(),
            'data_dimasukan_oleh' => $this->user->name,
            'diterima_oleh' => $this->user->name,
            'total_tagihan' => 100000,
        ]);

        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bm->id,
            'produk_id' => $this->produk->id,
            'qty_masuk' => 2,
            'satuan' => 'pcs',
            'hpp' => 50000,
            'subtotal' => 100000,
            'lokasi_penerimaan' => 'GDG-SRC',
        ]);

        $capturedAction = null;
        $capturedParams = null;

        $component = Livewire::actingAs($this->user)
            ->test(PembelianIndex::class)
            ->call('destroy', $bm->id)
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$capturedAction, &$capturedParams) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'] ?? null;
                $capturedParams = $data['params'] ?? [];
                return true;
            });

        $this->assertNotNull($capturedAction);
        $this->assertNotEmpty($capturedParams);

        // Kirim balik persis payload hasil tangkapan
        $component->dispatch($capturedAction, ...$capturedParams)
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('trx_barang_masuk_header', ['id' => $bm->id]);
    }

    public function test_transfer_detail_request_and_dispatch_four_actions_from_payload(): void
    {
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->gudangAsal->id],
            ['qty' => 30]
        );
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->gudangTujuan->id],
            ['qty' => 0]
        );

        $transfer = $this->createTransfer('DRAFT');

        $component = Livewire::actingAs($this->user)
            ->test(TransferDetail::class, ['transfer' => $transfer]);

        // 1. Shipment
        $shipAction = null;
        $shipParams = null;
        $component->call('requestProcessShipment')
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$shipAction, &$shipParams) {
                $data = $params[0] ?? $params;
                $shipAction = $data['action'];
                $shipParams = $data['params'];
                return true;
            });
        $component->dispatch($shipAction, ...$shipParams)->assertDispatched('toast:success');
        $this->assertEquals('IN_TRANSIT', $transfer->fresh()->status);

        // 2. Receive
        $rcvAction = null;
        $rcvParams = null;
        $component->call('requestProcessReceive')
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$rcvAction, &$rcvParams) {
                $data = $params[0] ?? $params;
                $rcvAction = $data['action'];
                $rcvParams = $data['params'];
                return true;
            });
        $component->dispatch($rcvAction, ...$rcvParams)->assertDispatched('toast:success');
        $this->assertEquals('RECEIVED', $transfer->fresh()->status);

        // 3. Close
        $clsAction = null;
        $clsParams = null;
        $component->call('requestCloseTransfer')
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$clsAction, &$clsParams) {
                $data = $params[0] ?? $params;
                $clsAction = $data['action'];
                $clsParams = $data['params'];
                return true;
            });
        $component->dispatch($clsAction, ...$clsParams)->assertDispatched('toast:success');
        $this->assertEquals('CLOSED', $transfer->fresh()->status);

        // 4. Cancel (pada transfer DRAFT lain)
        $transferBatal = $this->createTransfer('DRAFT');
        $compBatal = Livewire::actingAs($this->user)
            ->test(TransferDetail::class, ['transfer' => $transferBatal]);

        $cancelAction = null;
        $cancelParams = null;
        $compBatal->call('requestCancelTransfer')
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$cancelAction, &$cancelParams) {
                $data = $params[0] ?? $params;
                $cancelAction = $data['action'];
                $cancelParams = $data['params'];
                return true;
            });
        $compBatal->dispatch($cancelAction, ...$cancelParams)->assertDispatched('toast:success');
        $this->assertEquals('CANCELLED', $transferBatal->fresh()->status);
    }

    public function test_audit_request_and_dispatch_cancel_and_adjustment_from_payload(): void
    {
        // 1. Cancel Sesi
        $sessionCancel = OpnameHeader::create([
            'nomor_opname' => 'OPN-PAYLOAD-CANCEL-' . uniqid(),
            'tanggal' => now()->toDateString(),
            'lokasi_id' => $this->gudangAsal->id,
            'status' => 'DRAFT',
            'operator_name' => $this->user->name,
        ]);

        $cancelAction = null;
        $cancelParams = null;

        $compCancel = Livewire::actingAs($this->user)
            ->test(AuditIndex::class)
            ->call('loadSession', $sessionCancel->id)
            ->call('requestCancelSession')
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$cancelAction, &$cancelParams) {
                $data = $params[0] ?? $params;
                $cancelAction = $data['action'];
                $cancelParams = $data['params'];
                return true;
            });

        $compCancel->dispatch($cancelAction, ...$cancelParams)->assertDispatched('toast:success');
        $this->assertEquals('CANCELLED', $sessionCancel->fresh()->status);

        // 2. Execute Adjustment
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->gudangAsal->id],
            ['qty' => 10]
        );

        $sessionAdjust = OpnameHeader::create([
            'nomor_opname' => 'OPN-PAYLOAD-ADJ-' . uniqid(),
            'tanggal' => now()->toDateString(),
            'lokasi_id' => $this->gudangAsal->id,
            'status' => 'COMPLETED',
            'operator_name' => $this->user->name,
        ]);

        OpnameDetail::create([
            'opname_header_id' => $sessionAdjust->id,
            'produk_id' => $this->produk->id,
            'lokasi_id' => $this->gudangAsal->id,
            'qty_sistem' => 10,
            'qty_fisik' => 15,
            'selisih' => 5,
        ]);

        $adjAction = null;
        $adjParams = null;

        $compAdjust = Livewire::actingAs($this->user)
            ->test(AuditIndex::class)
            ->call('loadSession', $sessionAdjust->id)
            ->call('requestExecuteAdjustment')
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$adjAction, &$adjParams) {
                $data = $params[0] ?? $params;
                $adjAction = $data['action'];
                $adjParams = $data['params'];
                return true;
            });

        $compAdjust->dispatch($adjAction, ...$adjParams)->assertDispatched('toast:success');
        $this->assertEquals('ADJUSTED', $sessionAdjust->fresh()->status);
        $stokTerkini = StokLokasi::where(['produk_id' => $this->produk->id, 'lokasi_id' => $this->gudangAsal->id])->first();
        $this->assertEquals(15, $stokTerkini->qty);
    }

    public function test_stock_master_request_and_dispatch_delete_from_payload(): void
    {
        $newProduct = Produk::create([
            'nama_produk' => 'Produk Payload Master',
            'kategori' => 'KEMEJA',
            'kode_barang' => 'KMJ-MST-999',
            'nama_barang' => 'Kemeja Master Hapus',
            'variasi_barang' => 'Biru / M',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-SRC',
            'hpp' => 45000,
            'total_stok' => 0,
        ]);

        $capturedAction = null;
        $capturedParams = null;

        $component = Livewire::actingAs($this->user)
            ->test(StockMasterIndex::class)
            ->call('destroy', $newProduct->id)
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$capturedAction, &$capturedParams) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'];
                $capturedParams = $data['params'];
                return true;
            });

        $this->assertNotNull($capturedAction);
        $this->assertNotEmpty($capturedParams);

        $component->dispatch($capturedAction, ...$capturedParams)
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('mst_produk', ['id' => $newProduct->id]);
    }

    public function test_master_lokasi_request_and_dispatch_delete_from_payload(): void
    {
        $lokasi = Lokasi::create([
            'kode_lokasi' => 'GDG-PAYLOAD-' . uniqid(),
            'nama_lokasi' => 'Gudang Payload',
            'tipe_lokasi' => 'GUDANG',
            'is_active' => true,
        ]);

        $capturedAction = null;
        $capturedParams = null;

        $component = Livewire::actingAs($this->user)
            ->test(LokasiIndex::class)
            ->call('destroy', $lokasi->id)
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$capturedAction, &$capturedParams) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'];
                $capturedParams = $data['params'];
                return true;
            });

        $this->assertNotNull($capturedAction);
        $this->assertNotEmpty($capturedParams);

        $component->dispatch($capturedAction, ...$capturedParams)
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('mst_lokasi', ['id' => $lokasi->id]);
    }

    public function test_pelanggan_request_and_dispatch_delete_from_payload(): void
    {
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Customer Payload Standalone',
            'kontak' => '0899999999',
        ]);

        $capturedAction = null;
        $capturedParams = null;

        $component = Livewire::actingAs($this->user)
            ->test(PelangganIndex::class)
            ->call('destroy', $pelanggan->id)
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$capturedAction, &$capturedParams) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'];
                $capturedParams = $data['params'];
                return true;
            });

        $this->assertNotNull($capturedAction);
        $this->assertNotEmpty($capturedParams);

        $component->dispatch($capturedAction, ...$capturedParams)
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('mst_pelanggan', ['id' => $pelanggan->id]);
    }

    public function test_supplier_request_and_dispatch_delete_from_payload(): void
    {
        $supplier = Supplier::create([
            'nama_supplier' => 'Supplier Payload Standalone',
            'kontak' => '0877777777',
        ]);

        $capturedAction = null;
        $capturedParams = null;

        $component = Livewire::actingAs($this->user)
            ->test(SupplierIndex::class)
            ->call('destroy', $supplier->id)
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$capturedAction, &$capturedParams) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'];
                $capturedParams = $data['params'];
                return true;
            });

        $this->assertNotNull($capturedAction);
        $this->assertNotEmpty($capturedParams);

        $component->dispatch($capturedAction, ...$capturedParams)
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('mst_supplier', ['id' => $supplier->id]);
    }

    public function test_pengkodean_request_and_dispatch_delete_from_payload(): void
    {
        $aturan = AturanKode::create([
            'kategori' => 'TOPI_KHUSUS',
            'prefix' => 'TPK',
            'increment_digits' => 3,
            'format_tipe' => 'DEFAULT',
            'is_active' => true,
        ]);

        $capturedAction = null;
        $capturedParams = null;

        $component = Livewire::actingAs($this->user)
            ->test(PengkodeanIndex::class)
            ->call('destroy', $aturan->id)
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$capturedAction, &$capturedParams) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'];
                $capturedParams = $data['params'];
                return true;
            });

        $this->assertNotNull($capturedAction);
        $this->assertNotEmpty($capturedParams);

        $component->dispatch($capturedAction, ...$capturedParams)
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('mst_aturan_kode', ['id' => $aturan->id]);
    }

    public function test_user_management_request_and_dispatch_delete_from_payload(): void
    {
        $operator = User::create([
            'name' => 'Operator Delete Payload',
            'email' => 'operator.payload@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $operator->assignRole('operator_gudang');

        $capturedAction = null;
        $capturedParams = null;

        $component = Livewire::actingAs($this->user)
            ->test(UserManagementIndex::class)
            ->call('requestDeleteUser', $operator->id)
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$capturedAction, &$capturedParams) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'];
                $capturedParams = $data['params'];
                return true;
            });

        $this->assertNotNull($capturedAction);
        $this->assertNotEmpty($capturedParams);

        $component->dispatch($capturedAction, ...$capturedParams)
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('users', ['id' => $operator->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | GUARD EKSEKUSI: event confirmed-* dikirim langsung (mis. dari console
    | browser) tanpa melewati method request. Method eksekusi harus menolak.
    |--------------------------------------------------------------------------
    */

    private function assertAlertErrorContains(string $needle): \Closure
    {
        return function ($event, $params) use ($needle) {
            $data = $params[0] ?? $params;
            return str_contains($data['text'] ?? '', $needle);
        };
    }

    private function createBarangKeluarFor(Produk $produk, ?Pelanggan $pelanggan = null, bool $qcPassed = false): BarangKeluar
    {
        $pelanggan ??= Pelanggan::create(['nama_pelanggan' => 'Customer Guard ' . uniqid(), 'kontak' => '0800']);

        $bk = BarangKeluar::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $pelanggan->id,
            'nomor_nota' => 'BK-GUARD-' . uniqid(),
            'dikeluarkan_oleh' => $this->user->name,
            'status' => $qcPassed ? 'COMPLETED' : 'PENDING',
            'is_qc_passed' => $qcPassed,
        ]);

        BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $produk->id,
            'qty_keluar' => 1,
            'satuan' => 'pcs',
            'hpp' => 50000,
            'harga_satuan' => 75000,
            'subtotal' => 75000,
        ]);

        return $bk;
    }

    public function test_guard_stock_master_dispatch_rejects_product_with_transactions(): void
    {
        $this->createBarangKeluarFor($this->produk);

        Livewire::actingAs($this->user)
            ->test(StockMasterIndex::class)
            ->dispatch('confirmed-delete', id: $this->produk->id)
            ->assertDispatched('alert:error', $this->assertAlertErrorContains('riwayat transaksi'))
            ->assertNotDispatched('toast:success');

        $this->assertDatabaseHas('mst_produk', ['id' => $this->produk->id]);
    }

    public function test_guard_lokasi_dispatch_rejects_default_and_location_with_stock(): void
    {
        $default = Lokasi::where('kode_lokasi', 'GDG-A01')->first()
            ?? Lokasi::create(['kode_lokasi' => 'GDG-A01', 'nama_lokasi' => 'Default', 'tipe_lokasi' => 'GUDANG', 'is_active' => true]);

        Livewire::actingAs($this->user)
            ->test(LokasiIndex::class)
            ->dispatch('confirmed-delete', id: $default->id)
            ->assertDispatched('alert:error', $this->assertAlertErrorContains('default'))
            ->assertNotDispatched('toast:success');

        $this->assertDatabaseHas('mst_lokasi', ['id' => $default->id]);

        // gudangAsal berisi stok (qty > 0)
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->gudangAsal->id],
            ['qty' => 7]
        );

        Livewire::actingAs($this->user)
            ->test(LokasiIndex::class)
            ->dispatch('confirmed-delete', id: $this->gudangAsal->id)
            ->assertDispatched('alert:error', $this->assertAlertErrorContains('stok barang'))
            ->assertNotDispatched('toast:success');

        $this->assertDatabaseHas('mst_lokasi', ['id' => $this->gudangAsal->id]);
    }

    public function test_guard_pelanggan_dispatch_rejects_customer_with_transactions(): void
    {
        $pelanggan = Pelanggan::create(['nama_pelanggan' => 'Customer Berelasi', 'kontak' => '0811']);
        $this->createBarangKeluarFor($this->produk, $pelanggan);

        Livewire::actingAs($this->user)
            ->test(PelangganIndex::class)
            ->dispatch('confirmed-delete-pelanggan', id: $pelanggan->id)
            ->assertDispatched('alert:error', $this->assertAlertErrorContains('riwayat transaksi'))
            ->assertNotDispatched('toast:success');

        $this->assertDatabaseHas('mst_pelanggan', ['id' => $pelanggan->id]);
    }

    public function test_guard_supplier_dispatch_rejects_supplier_with_transactions(): void
    {
        $supplier = Supplier::create(['nama_supplier' => 'Supplier Berelasi', 'kontak' => '0822']);
        BarangMasuk::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $supplier->id,
            'nomor_nota' => 'BM-GUARD-' . uniqid(),
            'data_dimasukan_oleh' => $this->user->name,
        ]);

        Livewire::actingAs($this->user)
            ->test(SupplierIndex::class)
            ->dispatch('confirmed-delete-supplier', id: $supplier->id)
            ->assertDispatched('alert:error', $this->assertAlertErrorContains('riwayat transaksi'))
            ->assertNotDispatched('toast:success');

        $this->assertDatabaseHas('mst_supplier', ['id' => $supplier->id]);
    }

    public function test_guard_pengkodean_dispatch_rejects_default_global_rule(): void
    {
        $global = AturanKode::whereNull('kategori')->first()
            ?? AturanKode::create(['kategori' => null, 'prefix' => 'GLB', 'increment_digits' => 4, 'format_tipe' => 'INCREMENT', 'is_active' => true]);

        Livewire::actingAs($this->user)
            ->test(PengkodeanIndex::class)
            ->dispatch('confirmed-delete-rule', id: $global->id)
            ->assertDispatched('alert:error', $this->assertAlertErrorContains('Default Global'))
            ->assertNotDispatched('toast:success');

        $this->assertDatabaseHas('mst_aturan_kode', ['id' => $global->id]);
    }

    public function test_guard_penjualan_dispatch_rejects_qc_passed_transaction(): void
    {
        $bk = $this->createBarangKeluarFor($this->produk, null, true);

        Livewire::actingAs($this->user)
            ->test(PenjualanIndex::class)
            ->dispatch('confirmed-delete-barang-keluar', id: $bk->id)
            ->assertDispatched('alert:error', $this->assertAlertErrorContains('QC'))
            ->assertNotDispatched('toast:success');

        $this->assertDatabaseHas('trx_barang_keluar_header', ['id' => $bk->id, 'is_qc_passed' => true]);
    }

    public function test_guard_pembelian_dispatch_rejects_reversal_that_makes_stock_negative(): void
    {
        $supplier = Supplier::create(['nama_supplier' => 'Supplier Negatif', 'kontak' => '0833']);
        $bm = BarangMasuk::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $supplier->id,
            'nomor_nota' => 'BM-NEG-' . uniqid(),
            'data_dimasukan_oleh' => $this->user->name,
        ]);
        // qty_masuk (999) > total_stok produk (50) -> reversal akan negatif
        BarangMasukDetail::create([
            'barang_masuk_header_id' => $bm->id,
            'produk_id' => $this->produk->id,
            'qty_masuk' => 999,
            'satuan' => 'pcs',
            'hpp' => 50000,
            'lokasi_simpan' => 'GDG-SRC',
        ]);

        Livewire::actingAs($this->user)
            ->test(PembelianIndex::class)
            ->dispatch('confirmed-delete-barang-masuk', id: $bm->id)
            ->assertDispatched('alert:error', $this->assertAlertErrorContains('negatif'))
            ->assertNotDispatched('toast:success');

        $this->assertDatabaseHas('trx_barang_masuk_header', ['id' => $bm->id]);
        $this->assertEquals(50, $this->produk->fresh()->total_stok);
    }

    public function test_guard_pembelian_dispatch_rejects_user_without_permission(): void
    {
        $supplier = Supplier::create(['nama_supplier' => 'Supplier Auth', 'kontak' => '0844']);
        $bm = BarangMasuk::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $supplier->id,
            'nomor_nota' => 'BM-AUTH-' . uniqid(),
            'data_dimasukan_oleh' => $this->user->name,
        ]);

        // role finance: tidak punya 'manage master' maupun 'input masuk'
        $finance = User::where('email', 'finance@wmslaf.com')->firstOrFail();

        Livewire::actingAs($finance)
            ->test(PembelianIndex::class)
            ->dispatch('confirmed-delete-barang-masuk', id: $bm->id)
            ->assertForbidden();

        $this->assertDatabaseHas('trx_barang_masuk_header', ['id' => $bm->id]);
    }

    public function test_guard_pembelian_request_and_execute_share_same_authorization(): void
    {
        // Admin dilewatkan oleh Gate::before (kecuali 'manage system') walau izin
        // eksplisitnya dicabut. Request dan eksekusi harus memberi keputusan yang sama.
        $admin = User::where('email', 'admin@wmslaf.com')->firstOrFail();
        \Spatie\Permission\Models\Role::findByName('admin')->revokePermissionTo(['manage master', 'input masuk']);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $admin = $admin->fresh();

        $supplier = Supplier::create(['nama_supplier' => 'Supplier Konsisten', 'kontak' => '0855']);
        $bm = BarangMasuk::create([
            'tanggal' => now()->toDateString(),
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $supplier->id,
            'nomor_nota' => 'BM-KONSISTEN-' . uniqid(),
            'data_dimasukan_oleh' => $this->user->name,
        ]);

        $capturedAction = null;
        $capturedParams = null;

        $component = Livewire::actingAs($admin)
            ->test(PembelianIndex::class)
            ->call('destroy', $bm->id)
            ->assertDispatched('alert:confirm', function ($event, $params) use (&$capturedAction, &$capturedParams) {
                $data = $params[0] ?? $params;
                $capturedAction = $data['action'];
                $capturedParams = $data['params'];
                return true;
            });

        // Dialog sudah muncul => eksekusi tidak boleh 403
        $component->dispatch($capturedAction, ...$capturedParams)
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('trx_barang_masuk_header', ['id' => $bm->id]);
    }

    public function test_guard_user_management_dispatch_rejects_deleting_self(): void
    {
        Livewire::actingAs($this->user)
            ->test(UserManagementIndex::class)
            ->dispatch('confirmed-delete-user', id: $this->user->id)
            ->assertDispatched('toast:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'akun Anda sendiri');
            })
            ->assertNotDispatched('toast:success');

        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    public function test_guard_user_management_dispatch_rejects_deleting_last_super_admin(): void
    {
        $superAdmin = User::where('email', 'superadmin@wmslaf.com')->firstOrFail();
        $this->assertSame(1, User::role('super_admin')->count());

        // Pengelola non-super-admin dengan izin 'manage system'
        $manager = User::create([
            'name' => 'Manager Guard',
            'email' => 'manager.guard@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $manager->givePermissionTo('manage system');

        Livewire::actingAs($manager)
            ->test(UserManagementIndex::class)
            ->dispatch('confirmed-delete-user', id: $superAdmin->id)
            ->assertDispatched('toast:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'satu-satunya akun Super Admin');
            })
            ->assertNotDispatched('toast:success');

        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }
}
