<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\AnalyticsProduk;
use App\Livewire\Topbar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TopbarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Seed needed permissions and roles
        Permission::create(['name' => 'manage system']);
        Permission::create(['name' => 'manage master']);

        Role::create(['name' => 'super_admin'])->givePermissionTo(Permission::all());
        Role::create(['name' => 'admin'])->givePermissionTo(['manage master']);
    }

    public function test_topbar_component_can_be_rendered_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user);

        Livewire::test(Topbar::class)
            ->assertStatus(200)
            ->assertSee('Notifikasi')
            ->assertSee('Pengaturan Sistem')
            ->assertSee('SUPER ADMIN');
    }

    public function test_topbar_shows_low_stock_notification_correctly(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);

        // Create a product with low stock
        $produk = Produk::create([
            'nama_produk' => 'Sepatu Test',
            'kategori' => 'SEPATU',
            'kode_barang' => 'SPT-TST',
            'nama_barang' => 'Sepatu Test Item',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'hpp' => 50000.00,
            'stok_masuk' => 2,
            'stok_keluar' => 0,
            'total_stok' => 2,
            'lokasi' => 'GUDANG',
            'is_threshold_active' => true,
            'min_stok' => 10,
        ]);

        AnalyticsProduk::create([
            'produk_id' => $produk->id,
            'kategori' => 'SEPATU',
            'rata_rata_harian' => 0.00,
            'leadtime_produksi' => 14,
            'minimal_stock' => 10, // stok (2) <= min_stock (10) -> low stock alert
            'safety_stock' => 5,
            'maximum_stock' => 20,
        ]);

        Livewire::test(Topbar::class)
            ->assertSee('Stok Menipis')
            ->assertSee('Sepatu Test')
            ->assertSee('SPT-TST');
    }
}
