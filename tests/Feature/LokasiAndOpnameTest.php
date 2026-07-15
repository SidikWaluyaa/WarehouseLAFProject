<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Lokasi;
use App\Models\Produk;
use App\Models\StokLokasi;
use App\Models\MutasiLokasi;
use App\Models\OpnameHeader;
use App\Models\OpnameDetail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class LokasiAndOpnameTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        // Re-cache and set up Spatie permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::create(['name' => 'manage master']);
        Permission::create(['name' => 'input masuk']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(['manage master', 'input masuk']);
        
        $operatorRole = Role::create(['name' => 'operator']);
        $operatorRole->givePermissionTo(['input masuk']);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('admin');

        $this->user = User::create([
            'name' => 'Operator User',
            'email' => 'operator@test.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->assignRole('operator');



        $this->produk = Produk::create([
            'nama_produk' => 'Sandal Keren',
            'kategori' => 'SANDAL',
            'kode_barang' => 'SDL-KRN',
            'nama_barang' => 'Sandal Keren A1',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pasang',
            'hpp' => 10000.00,
            'stok_masuk' => 10,
            'stok_keluar' => 0,
            'total_stok' => 10,
            'lokasi' => 'GDG-A01',
        ]);
    }

    /**
     * Test Location page render and dynamic CRUD
     */
    public function test_can_manage_locations(): void
    {
        // 1. Render Lokasi page as admin
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Lokasi\Index::class)
            ->assertSee('GDG-A01')
            ->assertSee('Gudang Utama - Rak A1')
            ->call('create')
            ->set('kode_lokasi', 'GDG-B02')
            ->set('nama_lokasi', 'Gudang Utama - Rak B2')
            ->set('tipe_lokasi', 'GUDANG')
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('toast:success');

        $this->assertDatabaseHas('mst_lokasi', [
            'kode_lokasi' => 'GDG-B02',
            'nama_lokasi' => 'Gudang Utama - Rak B2',
        ]);
    }

    /**
     * Test duplicate location validation
     */
    public function test_cannot_create_duplicate_location_code(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Lokasi\Index::class)
            ->call('create')
            ->set('kode_lokasi', 'GDG-A01')
            ->set('nama_lokasi', 'Rak A1 Duplikat')
            ->call('store')
            ->assertHasErrors(['kode_lokasi' => 'unique']);
    }

    /**
     * Test deletion constraint on occupied locations
     */
    public function test_cannot_delete_occupied_locations(): void
    {
        // GDG-A01 has product with stock
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Lokasi\Index::class)
            ->call('destroy', Lokasi::where(['kode_lokasi' => 'GDG-A01'])->first()->id)
            ->assertDispatched('alert:error');
    }

    /**
     * Test Stock Opname / Auditing workflow
     */
    public function test_stock_opname_flow_with_double_scan(): void
    {
        $lokasi = Lokasi::where(['kode_lokasi' => 'GDG-A01'])->first();

        // 1. Double scan step 1: scanning location code starts draft session
        $comp = Livewire::actingAs($this->user)
            ->test(\App\Livewire\Audit\Index::class)
            ->set('barcodeScan', 'GDG-A01')
            ->call('processBarcodeScan')
            ->assertDispatched('toast:success');

        $sessionHeader = OpnameHeader::where(['lokasi_id' => $lokasi->id])->first();
        $this->assertNotNull($sessionHeader);
        $this->assertEquals('DRAFT', $sessionHeader->status);

        // 2. Double scan step 2: scan product code SDL-KRN to increment physical count
        $comp->set('barcodeScan', 'SDL-KRN')
            ->call('processBarcodeScan')
            ->assertDispatched('toast:success');

        $detail = OpnameDetail::where(['opname_header_id' => $sessionHeader->id])
            ->where(['produk_id' => $this->produk->id])
            ->first();

        // Expected system stock was 10. We scanned 1 product, but since draft initialized with system count,
        // incrementing counts by 1 makes physical = 11.
        $this->assertEquals(11, $detail->qty_fisik);
        $this->assertEquals(1, $detail->selisih);

        // 3. Complete counting session
        $comp->call('completeSession')
            ->assertDispatched('toast:success');

        $sessionHeader->refresh();
        $this->assertEquals('COMPLETED', $sessionHeader->status);

        // 4. Admin performs adjustments and updates DB stock and mutations log
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Audit\Index::class)
            ->call('loadSession', $sessionHeader->id)
            ->call('executeAdjustment')
            ->assertDispatched('toast:success');

        $sessionHeader->refresh();
        $this->assertEquals('ADJUSTED', $sessionHeader->status);
        $this->assertEquals('Admin User', $sessionHeader->adjusted_by);

        // Database checks
        $this->produk->refresh();
        $this->assertEquals(11, $this->produk->total_stok);

        $stokLokasi = StokLokasi::where(['produk_id' => $this->produk->id])
            ->where(['lokasi_id' => $lokasi->id])
            ->first();
        $this->assertEquals(11, $stokLokasi->qty);

        // Verify mutation log
        $this->assertDatabaseHas('trx_mutasi_lokasi', [
            'produk_id' => $this->produk->id,
            'lokasi_tujuan_id' => $lokasi->id,
            'lokasi_asal_id' => null,
            'qty' => 1,
            'tipe_mutasi' => 'OPNAME_ADJUST',
        ]);
    }

    /**
     * Test location detail page renders, displays products, and supports search/sorting
     */
    public function test_can_view_location_details(): void
    {
        $lokasi = Lokasi::where(['kode_lokasi' => 'GDG-A01'])->first();

        // Ensure stock exists at this location for $this->produk
        $this->assertDatabaseHas('trx_stok_lokasi', [
            'produk_id' => $this->produk->id,
            'lokasi_id' => $lokasi->id,
            'qty' => 10,
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Lokasi\Detail::class, ['lokasi' => $lokasi])
            ->assertSee('GDG-A01')
            ->assertSee('Sandal Keren')
            ->assertSee('SDL-KRN')
            ->assertSee('10 pasang')
            ->set('search', 'NON_EXISTENT_PRODUCT')
            ->assertDontSee('SDL-KRN')
            ->set('search', 'Sandal')
            ->assertSee('SDL-KRN')
            ->call('sortBy', 'mst_produk.kode_barang')
            ->assertHasNoErrors();
    }
}
