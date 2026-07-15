<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pelanggan;
use App\Models\BarangKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Pelanggan\Index as PelangganIndex;
use Tests\TestCase;

class PelangganTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first(); // Grab Super Admin seeded user
    }

    public function test_pelanggan_page_requires_authentication(): void
    {
        $response = $this->get('/pelanggan');
        $response->assertRedirect('/login');
    }

    public function test_can_render_pelanggan_page_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get('/pelanggan');
        $response->assertStatus(200);
        $response->assertSee('Master Pelanggan');
    }

    public function test_can_create_pelanggan(): void
    {
        Livewire::actingAs($this->user)
            ->test(PelangganIndex::class)
            ->set('nama_pelanggan', 'Toko Baru')
            ->set('kontak', '0856789')
            ->set('alamat', 'Jl. Baru No. 2')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mst_pelanggan', [
            'nama_pelanggan' => 'Toko Baru',
            'kontak' => '0856789',
            'alamat' => 'Jl. Baru No. 2',
        ]);
    }

    public function test_can_update_pelanggan(): void
    {
        $pelanggan = Pelanggan::first();

        Livewire::actingAs($this->user)
            ->test(PelangganIndex::class)
            ->call('edit', $pelanggan->id)
            ->assertSet('nama_pelanggan', $pelanggan->nama_pelanggan)
            ->set('nama_pelanggan', 'Toko Updated')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mst_pelanggan', [
            'id' => $pelanggan->id,
            'nama_pelanggan' => 'Toko Updated',
        ]);
    }

    public function test_can_delete_pelanggan(): void
    {
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Pelanggan Temporary',
            'kontak' => '1234',
            'alamat' => 'Alamat Temp',
        ]);

        Livewire::actingAs($this->user)
            ->test(PelangganIndex::class)
            ->call('destroy', $pelanggan->id)
            ->assertDispatched('alert:confirm', function ($name, $args) use ($pelanggan) {
                $data = $args[0] ?? [];
                return ($data['action'] ?? null) === 'confirmed-delete-pelanggan' 
                    && ($data['params']['id'] ?? null) === $pelanggan->id;
            })
            ->call('confirmedDelete', ['id' => $pelanggan->id])
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('mst_pelanggan', [
            'id' => $pelanggan->id,
        ]);
    }

    public function test_cannot_delete_pelanggan_with_transactions(): void
    {
        // First we create a transaction (barang keluar) for a pelanggan
        $pelanggan = Pelanggan::first();
        
        BarangKeluar::create([
            'tanggal' => '2026-06-17',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $pelanggan->id,
            'nomor_nota' => 'BK-TEST-1',
            'dikeluarkan_oleh' => 'Test User',
        ]);

        Livewire::actingAs($this->user)
            ->test(PelangganIndex::class)
            ->call('destroy', $pelanggan->id)
            ->assertDispatched('alert:error', function ($name, $args) use ($pelanggan) {
                $data = $args[0] ?? [];
                return str_contains($data['text'] ?? '', "Pelanggan '{$pelanggan->nama_pelanggan}' tidak dapat dihapus");
            });

        $this->assertDatabaseHas('mst_pelanggan', [
            'id' => $pelanggan->id,
        ]);
    }
}
