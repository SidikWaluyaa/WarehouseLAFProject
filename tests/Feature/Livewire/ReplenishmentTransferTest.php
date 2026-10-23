<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Replenishment\Index as ReplenishmentIndex;
use App\Models\Lokasi;
use App\Models\Produk;
use App\Models\TransferHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReplenishmentTransferTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Lokasi $gudang;
    private Lokasi $toko;
    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first();

        // Pastikan ada lokasi tipe GUDANG dan TOKO
        $this->gudang = Lokasi::firstOrCreate(
            ['kode_lokasi' => 'GDG-REP-01'],
            ['nama_lokasi' => 'Gudang Pusat Rep', 'tipe_lokasi' => 'GUDANG', 'is_active' => true]
        );

        $this->toko = Lokasi::firstOrCreate(
            ['kode_lokasi' => 'TKO-REP-01'],
            ['nama_lokasi' => 'Toko Cabang Rep', 'tipe_lokasi' => 'TOKO', 'is_active' => true]
        );

        $this->produk = Produk::first();
    }

    /**
     * Test pembuatan transfer lewat Replenishment dengan item kuantitas > 0 berhasil.
     */
    public function test_replenishment_create_transfer_with_valid_qty_succeeds(): void
    {
        $initialCount = TransferHeader::count();

        Livewire::actingAs($this->user)
            ->test(ReplenishmentIndex::class)
            ->set('selectedItems', [
                "{$this->produk->id}_{$this->toko->id}" => [
                    'produk_id' => $this->produk->id,
                    'lokasi_toko_id' => $this->toko->id,
                    'feasible_qty' => 10,
                ]
            ])
            ->call('createTransferOrder')
            ->assertDispatched('toast:success');

        $this->assertEquals($initialCount + 1, TransferHeader::count(), 'Transfer order replenishment harus berhasil dibuat');
        
        $latestTransfer = TransferHeader::latest('id')->first();
        $this->assertEquals('DRAFT', $latestTransfer->status);
        $this->assertEquals($this->toko->id, $latestTransfer->lokasi_tujuan_id);
    }

    /**
     * Test pembuatan transfer lewat Replenishment tanpa item qty > 0 (qty = 0) ditolak dengan pesan yang jelas.
     */
    public function test_replenishment_create_transfer_with_qty_zero_is_rejected(): void
    {
        $initialCount = TransferHeader::count();

        Livewire::actingAs($this->user)
            ->test(ReplenishmentIndex::class)
            ->set('selectedItems', [
                "{$this->produk->id}_{$this->toko->id}" => [
                    'produk_id' => $this->produk->id,
                    'lokasi_toko_id' => $this->toko->id,
                    'feasible_qty' => 0,
                ]
            ])
            ->call('createTransferOrder')
            ->assertDispatched('alert:error', function ($event, $params) {
                $data = $params[0] ?? $params;
                return str_contains($data['text'] ?? '', 'kuantitas lebih dari 0');
            });

        $this->assertEquals($initialCount, TransferHeader::count(), 'Transfer order tidak boleh dibuat jika item qty 0');
    }
}
