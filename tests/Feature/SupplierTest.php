<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Supplier;
use App\Models\BarangMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Supplier\Index as SupplierIndex;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
        $this->user = User::first(); // Grab the Super Admin seeded user
    }

    public function test_supplier_page_requires_authentication(): void
    {
        $response = $this->get('/supplier');
        $response->assertRedirect('/login');
    }

    public function test_can_render_supplier_page_when_authenticated(): void
    {
        $response = $this->actingAs($this->user)->get('/supplier');
        $response->assertStatus(200);
        $response->assertSee('Master Supplier');
    }

    public function test_can_create_supplier(): void
    {
        Livewire::actingAs($this->user)
            ->test(SupplierIndex::class)
            ->set('nama_supplier', 'PT Supplier Baru')
            ->set('kontak', '08123456789')
            ->set('alamat', 'Jl. Baru No. 1')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mst_supplier', [
            'nama_supplier' => 'PT Supplier Baru',
            'kontak' => '08123456789',
            'alamat' => 'Jl. Baru No. 1',
        ]);
    }

    public function test_can_update_supplier(): void
    {
        $supplier = Supplier::first();

        Livewire::actingAs($this->user)
            ->test(SupplierIndex::class)
            ->call('edit', $supplier->id)
            ->assertSet('nama_supplier', $supplier->nama_supplier)
            ->set('nama_supplier', 'PT Supplier Updated')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mst_supplier', [
            'id' => $supplier->id,
            'nama_supplier' => 'PT Supplier Updated',
        ]);
    }

    public function test_can_delete_supplier(): void
    {
        $supplier = Supplier::create([
            'nama_supplier' => 'Supplier Temporary',
            'kontak' => '1234',
            'alamat' => 'Alamat Temp',
        ]);

        Livewire::actingAs($this->user)
            ->test(SupplierIndex::class)
            ->call('destroy', $supplier->id)
            ->assertDispatched('alert:confirm', function ($name, $args) use ($supplier) {
                $data = $args[0] ?? [];
                return ($data['action'] ?? null) === 'confirmed-delete-supplier' 
                    && ($data['params']['id'] ?? null) === $supplier->id;
            })
            ->call('confirmedDelete', ['id' => $supplier->id])
            ->assertDispatched('toast:success');

        $this->assertDatabaseMissing('mst_supplier', [
            'id' => $supplier->id,
        ]);
    }

    public function test_cannot_delete_supplier_with_transactions(): void
    {
        $supplier = Supplier::first(); // This supplier has transaction in seeded database

        Livewire::actingAs($this->user)
            ->test(SupplierIndex::class)
            ->call('destroy', $supplier->id)
            ->assertDispatched('alert:error', function ($name, $args) use ($supplier) {
                $data = $args[0] ?? [];
                return str_contains($data['text'] ?? '', "Supplier '{$supplier->nama_supplier}' tidak dapat dihapus");
            });

        $this->assertDatabaseHas('mst_supplier', [
            'id' => $supplier->id,
        ]);
    }
}
