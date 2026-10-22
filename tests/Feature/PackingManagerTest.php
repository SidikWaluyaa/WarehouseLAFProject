<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Lokasi;
use App\Models\StokLokasi;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\Pelanggan;
use App\Models\PickingList;
use App\Services\PickingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PackingManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Pelanggan $pelanggan;
    private Produk $produk;
    private Lokasi $rak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first();

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Toko Sepatu Berkah',
            'kontak' => '081299998888',
            'alamat' => 'Jl. Pasar Baru No. 12',
        ]);

        $this->rak = Lokasi::create([
            'kode_lokasi' => 'GDG-R01',
            'nama_lokasi' => 'Rak 1',
            'tipe_lokasi' => 'GUDANG',
            'is_active' => true,
        ]);

        $this->produk = Produk::create([
            'nama_produk' => 'Sneakers LAF Pro',
            'kategori' => 'SEPATU',
            'kode_barang' => 'BRG-LAF-001',
            'nama_barang' => 'Sneakers LAF Pro Hitam',
            'variasi_barang' => 'Hitam / 42',
            'satuan' => 'pasang',
            'lokasi' => 'GDG-R01',
            'hpp' => 100000,
        ]);

        StokLokasi::updateOrCreate(
            ['produk_id' => $this->produk->id, 'lokasi_id' => $this->rak->id],
            ['qty' => 50]
        );
    }

    public function test_guest_cannot_access_packing_page(): void
    {
        $response = $this->get('/packing');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_packing_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/packing');
        $response->assertStatus(200);
        $response->assertSee('Outbound QC');
    }

    public function test_packing_manager_pagination_and_tab_switching(): void
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Outbound\PackingManager::class)
            ->assertSet('activeTab', 'ready')
            ->assertSet('perPage', 10)
            ->assertSet('perPageCompleted', 10)
            ->set('perPage', 25)
            ->assertSet('perPage', 25)
            ->set('perPage', 50)
            ->assertSet('perPage', 50)
            ->set('perPage', 100)
            ->assertSet('perPage', 100)
            ->call('setTab', 'completed')
            ->assertSet('activeTab', 'completed')
            ->set('perPageCompleted', 25)
            ->assertSet('perPageCompleted', 25)
            ->set('perPageCompleted', 50)
            ->assertSet('perPageCompleted', 50)
            ->set('perPageCompleted', 100)
            ->assertSet('perPageCompleted', 100);
    }

    public function test_qc_checklist_and_submit_packing_flow(): void
    {
        $this->actingAs($this->user);

        // 1. Create BarangKeluar
        $bk = BarangKeluar::create([
            'tanggal' => '2026-10-09',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'nomor_nota' => 'BK/20261009/TEST',
            'dikeluarkan_oleh' => $this->user->name,
        ]);

        $detail = BarangKeluarDetail::create([
            'barang_keluar_header_id' => $bk->id,
            'produk_id' => $this->produk->id,
            'qty_keluar' => 2,
            'satuan' => 'pasang',
            'lokasi_pengambilan' => 'GDG-R01',
            'hpp' => 100000,
        ]);

        // 2. Generate and complete picking
        $picking = PickingService::generatePickingList('BarangKeluar', $bk->id, $this->user->name);
        PickingService::startPicking($picking->id, $this->user->name);
        PickingService::completePicking($picking->id);

        // 3. Test Livewire component interacts with this ready order
        $test = Livewire::test(\App\Livewire\Outbound\PackingManager::class)
            ->assertSee($bk->nomor_nota)
            ->assertSee('Toko Sepatu Berkah')
            // Check all QC
            ->call('checkAllQc', $bk->id, 'BarangKeluar')
            ->set('packingData.BarangKeluar_' . $bk->id . '.kurir', 'J&T')
            ->set('packingData.BarangKeluar_' . $bk->id . '.resi', 'JT123456789ID')
            ->call('submitPacking', $bk->id, 'BarangKeluar');

        $test->assertHasNoErrors();

        // 4. Verify DB updated
        $bk->refresh();
        $this->assertTrue((bool)$bk->is_qc_passed);
        $this->assertEquals('J&T', $bk->kurir);
        $this->assertEquals('JT123456789ID', $bk->resi_ekspedisi);
    }
}
