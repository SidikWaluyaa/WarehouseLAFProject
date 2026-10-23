<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Transfer\Create as TransferCreate;
use App\Livewire\Transfer\Detail as TransferDetail;
use App\Models\Lokasi;
use App\Models\Produk;
use App\Models\TransferDetail as TransferDetailModel;
use App\Models\TransferHeader;
use App\Models\User;
use App\Services\TransferService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransferQtyGuardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Lokasi $lokasiAsal;
    private Lokasi $lokasiTujuan;
    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first();
        $this->lokasiAsal = Lokasi::first();
        $this->lokasiTujuan = Lokasi::where('id', '!=', $this->lokasiAsal->id)->first();
        $this->produk = Produk::first();
    }

    /**
     * Test 1: Simpan transfer dengan kuantitas 0 ditolak di form dan di service.
     */
    public function test_simpan_transfer_qty_0_ditolak(): void
    {
        $initialCount = TransferHeader::count();

        // 1. Simpan via Livewire Transfer Create
        Livewire::actingAs($this->user)
            ->test(TransferCreate::class)
            ->set('tanggal', now()->format('Y-m-d'))
            ->set('lokasi_asal_id', $this->lokasiAsal->id)
            ->set('lokasi_tujuan_id', $this->lokasiTujuan->id)
            ->set('items', [
                [
                    'produk_id' => $this->produk->id,
                    'search_query' => $this->produk->nama_barang,
                    'qty_requested' => 0,
                ]
            ])
            ->call('store')
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'kuantitas lebih dari 0');
            });

        $this->assertEquals($initialCount, TransferHeader::count(), 'Header transfer tidak boleh dibuat jika qty 0');

        // 2. Simpan langsung via TransferService::createTransferOrder
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Transfer order harus memiliki minimal satu item dengan kuantitas lebih dari 0.');

        TransferService::createTransferOrder([
            'tanggal' => now()->format('Y-m-d'),
            'lokasi_asal_id' => $this->lokasiAsal->id,
            'lokasi_tujuan_id' => $this->lokasiTujuan->id,
            'created_by' => $this->user->name,
            'items' => [
                [
                    'produk_id' => $this->produk->id,
                    'qty_requested' => 0,
                ]
            ]
        ]);
    }

    /**
     * Test 2: Eksekusi kirim transfer dengan kuantitas 0 ditolak di UI request, confirmed event, dan service.
     */
    public function test_kirim_transfer_qty_0_ditolak(): void
    {
        // Buat transfer DRAFT yang memiliki item qty_requested = 0
        $transfer = TransferHeader::create([
            'nomor_transfer' => 'TRF/' . date('Ymd') . '/9999',
            'tanggal' => now()->format('Y-m-d'),
            'lokasi_asal_id' => $this->lokasiAsal->id,
            'lokasi_tujuan_id' => $this->lokasiTujuan->id,
            'status' => 'DRAFT',
            'created_by' => $this->user->name,
        ]);

        TransferDetailModel::create([
            'transfer_header_id' => $transfer->id,
            'produk_id' => $this->produk->id,
            'qty_requested' => 0,
            'qty_sent' => 0,
            'qty_received' => 0,
        ]);

        // 1. Request kirim dari UI harus ditolak sebelum dialog konfirmasi muncul
        Livewire::actingAs($this->user)
            ->test(TransferDetail::class, ['transfer' => $transfer])
            ->call('requestProcessShipment')
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'kuantitas lebih dari 0');
            })
            ->assertNotDispatched('alert:confirm');

        // 2. Eksekusi confirmedProcessShipment (misal trigger dari console browser) harus tetap ditolak
        Livewire::actingAs($this->user)
            ->test(TransferDetail::class, ['transfer' => $transfer])
            ->call('confirmedProcessShipment', $transfer->id)
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'kuantitas lebih dari 0');
            });

        $transfer->refresh();
        $this->assertEquals('DRAFT', $transfer->status, 'Status transfer harus tetap DRAFT');

        // 3. Eksekusi via TransferService::processShipment langsung
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Transfer order tidak dapat dikirim karena tidak memiliki item dengan kuantitas lebih dari 0.');

        TransferService::processShipment($transfer->id, $this->user->name);
    }
}
