<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\Lokasi;
use App\Models\StokLokasi;
use App\Models\MutasiLokasi;
use Livewire\Livewire;
use App\Livewire\StockMaster\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StockMasterMultiLocationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Lokasi $gudang;
    protected Lokasi $toko;
    protected Produk $product;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::findOrCreate('view master', 'web');
        Permission::findOrCreate('manage master', 'web');

        $role = Role::findOrCreate('Super Admin', 'web');
        $role->givePermissionTo(['view master', 'manage master']);

        $this->user = User::factory()->create();
        $this->user->assignRole($role);

        // Setup locations
        $this->gudang = Lokasi::firstOrCreate(
            ['kode_lokasi' => 'GDG-A01'],
            ['nama_lokasi' => 'Gudang Utama - Rak A1', 'tipe_lokasi' => 'GUDANG', 'is_active' => true]
        );

        $this->toko = Lokasi::firstOrCreate(
            ['kode_lokasi' => 'TKO-R01'],
            ['nama_lokasi' => 'Toko Depan - Rak R1', 'tipe_lokasi' => 'TOKO', 'is_active' => true]
        );

        // Setup 1 product with stock in BOTH locations
        $this->product = Produk::create([
            'nama_produk' => 'Sandal Gunung Aron',
            'nama_barang' => 'ARON HITAM 36',
            'kode_barang' => 'ARON-HTM-36',
            'kategori' => 'SANDAL',
            'variasi_barang' => 'HITAM / 36',
            'warna' => 'HITAM',
            'size' => '36',
            'satuan' => 'PSG',
            'lokasi' => 'GDG-A01',
            'hpp' => 50000,
            'harga_jual' => 100000,
            'stok_masuk' => 30,
            'stok_keluar' => 0,
            'total_stok' => 30,
            'is_active' => true,
        ]);

        // Allocate: 20 pcs in GDG-A01, 10 pcs in TKO-R01
        StokLokasi::updateOrCreate(
            ['produk_id' => $this->product->id, 'lokasi_id' => $this->gudang->id],
            ['qty' => 20]
        );

        StokLokasi::updateOrCreate(
            ['produk_id' => $this->product->id, 'lokasi_id' => $this->toko->id],
            ['qty' => 10]
        );
    }

    public function test_can_view_product_under_both_location_filters()
    {
        // 1. Without filter: product is visible, total stok is 30
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSee('ARON HITAM 36')
            ->assertSee('30');

        // 2. Filter by Toko (TKO-R01): product is visible, shows 10 at TKO-R01 and Total Semua 30
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('locationFilter', 'TKO-R01')
            ->assertSee('ARON HITAM 36')
            ->assertSee('10')
            ->assertSee('TKO-R01')
            ->assertSee('Total Semua:')
            ->assertSee('30')
            ->assertSee('SKU di TKO-R01');

        // 3. Filter by Gudang (GDG-A01): product is visible, shows 20 at GDG-A01
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('locationFilter', 'GDG-A01')
            ->assertSee('ARON HITAM 36')
            ->assertSee('20')
            ->assertSee('GDG-A01')
            ->assertSee('SKU di GDG-A01');
    }

    public function test_kpi_cards_are_dynamic_for_selected_location()
    {
        // For TKO-R01: 1 SKU, 10 pcs * 50,000 HPP = Rp500,000
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('locationFilter', 'TKO-R01')
            ->assertSee('Nilai Stok (TKO-R01)')
            ->assertSee('Rp500.000');

        // For GDG-A01: 1 SKU, 20 pcs * 50,000 HPP = Rp1,000,000
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('locationFilter', 'GDG-A01')
            ->assertSee('Nilai Stok (GDG-A01)')
            ->assertSee('Rp1,0Jt');
    }

    public function test_pagination_per_page_system()
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->assertSet('perPage', 10)
            ->assertSee('10 per halaman')
            ->assertSee('25 per halaman')
            ->assertSee('50 per halaman')
            ->assertSee('100 per halaman')
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    }
}
