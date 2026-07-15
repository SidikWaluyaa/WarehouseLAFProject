<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\BarangMasuk;
use App\Models\AuditLog;
use App\Models\StokLokasi;
use App\Models\Lokasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BarangMasukEditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private Supplier $supplier;
    private Lokasi $lokasiGudang;
    private Lokasi $lokasiToko;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles, permissions, and users
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);

        // Find seeded users
        $this->admin = User::whereHas('roles', function($q) { $q->where('name', 'super_admin'); })->first();
        $this->operator = User::whereHas('roles', function($q) { $q->where('name', 'operator_gudang'); })->first();

        $this->supplier = Supplier::first();
        
        $this->lokasiGudang = Lokasi::where('kode_lokasi', 'GDG-A01')->first() ?: Lokasi::create([
            'kode_lokasi' => 'GDG-A01',
            'nama_lokasi' => 'Gudang Utama',
            'tipe_lokasi' => 'GUDANG',
            'is_active' => true,
        ]);

        $this->lokasiToko = Lokasi::where('kode_lokasi', 'TKO-R01')->first() ?: Lokasi::create([
            'kode_lokasi' => 'TKO-R01',
            'nama_lokasi' => 'Toko Retail',
            'tipe_lokasi' => 'TOKO',
            'is_active' => true,
        ]);
    }

    public function test_unauthorized_user_cannot_view_edit_page(): void
    {
        // Operator doesn't have manage master or input masuk permissions
        // Wait, does operator have 'input masuk'? Let's check DatabaseSeeder.
        // Yes, operator_gudang has 'input masuk' in DatabaseSeeder.
        // Let's create a completely clean user without roles or permissions.
        $cleanUser = User::create([
            'name' => 'Clean User',
            'email' => 'clean@wmslaf.com',
            'password' => bcrypt('password'),
        ]);

        $bm = BarangMasuk::create([
            'tanggal' => '2026-06-24',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-TEST-001',
            'data_dimasukan_oleh' => 'System',
        ]);

        $this->actingAs($cleanUser);

        Livewire::test(\App\Livewire\Pembelian\Edit::class, ['id' => $bm->id])
            ->assertForbidden();
    }

    public function test_authorized_user_can_view_edit_page(): void
    {
        $bm = BarangMasuk::create([
            'tanggal' => '2026-06-24',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-TEST-001',
            'data_dimasukan_oleh' => 'System',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Pembelian\Edit::class, ['id' => $bm->id])
            ->assertOk()
            ->assertSet('nomor_nota', $bm->nomor_nota);
    }

    public function test_edit_transaction_reverses_and_reapplies_stok_and_hpp(): void
    {
        // 1. Create a product with initial stock
        $product = Produk::create([
            'kode_barang' => 'PROD-A',
            'nama_produk' => 'Product A',
            'nama_barang' => 'Product A Display',
            'kategori' => 'Sandal Dewasa',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-A01',
            'hpp' => 20000.00,
            'harga_jual' => 30000.00,
            'is_active' => true,
            'stok_masuk' => 10,
            'stok_keluar' => 0,
            'total_stok' => 10,
        ]);


        // 2. Create the BarangMasuk transaction we will edit
        $bm = BarangMasuk::create([
            'tanggal' => '2026-06-24',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-TEST-EDIT',
            'data_dimasukan_oleh' => 'System',
        ]);

        $detail = $bm->details()->create([
            'produk_id' => $product->id,
            'qty_masuk' => 5,
            'satuan' => 'pcs',
            'lokasi_simpan' => 'GDG-A01',
            'hpp' => 26000.00,
        ]);

        // Recalculate HPP weighted average:
        // Stock before: 10 @ 20000. Stock added: 5 @ 26000.
        // New HPP: (10*20000 + 5*26000) / 15 = 330000 / 15 = 22000.
        $product->total_stok += 5;
        $product->stok_masuk += 5;
        $product->hpp = 22000.00;
        $product->save();

        $stokGudang = StokLokasi::where(['produk_id' => $product->id, 'lokasi_id' => $this->lokasiGudang->id])->first();
        $stokGudang->qty += 5;
        $stokGudang->save();

        $this->assertEquals(15, $product->total_stok);
        $this->assertEquals(22000.00, $product->hpp);

        // 3. Edit the transaction using Livewire Edit
        // Change QTY from 5 to 10, change HPP from 26000 to 30000, change Location from GDG-A01 to TKO-R01
        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Pembelian\Edit::class, ['id' => $bm->id])
            ->set('tanggal', '2026-06-24')
            ->set('tipe_nota', 'Pembelian')
            ->set('supplier_id', $this->supplier->id)
            ->set('nomor_nota', 'BM-TEST-EDIT-MODIFIED')
            ->set('keterangan_tambahan', 'Updated via edit test')
            ->set('items', [
                [
                    'produk_id' => $product->id,
                    'qty_masuk' => 10,
                    'hpp' => 30000.00,
                    'lokasi_simpan' => 'TKO-R01',
                    'po_detail_id' => null,
                ]
            ])
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect('/barang-masuk');

        // 4. Verify Stok & HPP calculations after edit (reversal & re-apply)
        // Expected Reversal:
        // - Deduct old detail (qty 5 @ 26000) from product
        //   - total_stok: 15 - 5 = 10
        //   - HPP reverted: (15 * 22000 - 5 * 26000) / 10 = (330000 - 130000) / 10 = 20000
        // - Deduct qty 5 from GDG-A01: 15 - 5 = 10
        // Expected Re-apply:
        // - Add new detail (qty 10 @ 30000) to product
        //   - total_stok: 10 + 10 = 20
        //   - HPP re-calculated: (10 * 20000 + 10 * 30000) / 20 = 500000 / 20 = 25000
        // - Add qty 10 to TKO-R01: 0 + 10 = 10
        $product->refresh();
        $this->assertEquals(20, $product->total_stok);
        $this->assertEquals(25000.00, $product->hpp);

        $stokGdg = StokLokasi::where(['produk_id' => $product->id, 'lokasi_id' => $this->lokasiGudang->id])->first();
        $stokTko = StokLokasi::where(['produk_id' => $product->id, 'lokasi_id' => $this->lokasiToko->id])->first();

        $this->assertEquals(10, $stokGdg->qty);
        $this->assertEquals(10, $stokTko->qty);

        // 5. Verify Audit Log was generated
        $this->assertDatabaseHas('trx_audit_logs', [
            'action' => 'UPDATE',
            'auditable_type' => get_class($bm),
            'auditable_id' => $bm->id,
            'username' => $this->admin->name,
        ]);

        $auditLog = AuditLog::where([
            'auditable_type' => get_class($bm),
            'auditable_id' => $bm->id
        ])->first();

        $this->assertNotNull($auditLog->old_values);
        $this->assertNotNull($auditLog->new_values);
        $this->assertEquals('BM-TEST-EDIT', $auditLog->old_values['header']['nomor_nota']);
        $this->assertEquals('BM-TEST-EDIT-MODIFIED', $auditLog->new_values['header']['nomor_nota']);
    }

    public function test_unauthorized_user_cannot_delete_transaction(): void
    {
        $cleanUser = User::create([
            'name' => 'Clean User',
            'email' => 'clean-delete@wmslaf.com',
            'password' => bcrypt('password'),
        ]);

        $bm = BarangMasuk::create([
            'tanggal' => '2026-06-24',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-TEST-DEL-1',
            'data_dimasukan_oleh' => 'System',
        ]);

        $this->actingAs($cleanUser);

        Livewire::test(\App\Livewire\Pembelian\Index::class)
            ->assertForbidden();
    }

    public function test_authorized_user_can_delete_transaction_which_reverses_stok_and_hpp_and_creates_audit_log(): void
    {
        // 1. Create a product with initial stock
        $product = Produk::create([
            'kode_barang' => 'PROD-DEL',
            'nama_produk' => 'Product Delete',
            'nama_barang' => 'Product Delete Display',
            'kategori' => 'Sandal Dewasa',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-A01',
            'hpp' => 20000.00,
            'harga_jual' => 30000.00,
            'is_active' => true,
            'stok_masuk' => 10,
            'stok_keluar' => 0,
            'total_stok' => 10,
        ]);

        // 2. Create the BarangMasuk transaction we will delete
        $bm = BarangMasuk::create([
            'tanggal' => '2026-06-24',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-TEST-DELETE-REAL',
            'data_dimasukan_oleh' => 'System',
        ]);

        $detail = $bm->details()->create([
            'produk_id' => $product->id,
            'qty_masuk' => 5,
            'satuan' => 'pcs',
            'lokasi_simpan' => 'GDG-A01',
            'hpp' => 26000.00,
        ]);

        // Recalculate HPP weighted average:
        // Stock before: 10 @ 20000. Stock added: 5 @ 26000.
        // New HPP: (10*20000 + 5*26000) / 15 = 22000.
        $product->total_stok += 5;
        $product->stok_masuk += 5;
        $product->hpp = 22000.00;
        $product->save();

        $stokGudang = StokLokasi::where(['produk_id' => $product->id, 'lokasi_id' => $this->lokasiGudang->id])->first();
        $stokGudang->qty += 5;
        $stokGudang->save();

        $this->assertEquals(15, $product->total_stok);

        // 3. Delete the transaction using Livewire Index
        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Pembelian\Index::class)
            ->call('destroy', $bm->id)
            ->assertHasNoErrors()
            ->assertDispatched('alert:confirm');

        Livewire::test(\App\Livewire\Pembelian\Index::class)
            ->call('confirmedDelete', ['id' => $bm->id])
            ->assertHasNoErrors()
            ->assertDispatched('toast:success');

        // 4. Verify data was deleted
        $this->assertDatabaseMissing('trx_barang_masuk_header', ['id' => $bm->id]);
        $this->assertDatabaseMissing('trx_barang_masuk_detail', ['barang_masuk_header_id' => $bm->id]);

        // 5. Verify Stok & HPP calculations reversed
        $product->refresh();
        $this->assertEquals(10, $product->total_stok);
        $this->assertEquals(20000.00, $product->hpp);

        $stokGdg = StokLokasi::where(['produk_id' => $product->id, 'lokasi_id' => $this->lokasiGudang->id])->first();
        $this->assertEquals(10, $stokGdg->qty);

        // 6. Verify Audit Log was generated for DELETE action
        $this->assertDatabaseHas('trx_audit_logs', [
            'action' => 'DELETE',
            'auditable_type' => get_class($bm),
            'auditable_id' => $bm->id,
            'username' => $this->admin->name,
        ]);

        $auditLog = AuditLog::where([
            'auditable_type' => get_class($bm),
            'auditable_id' => $bm->id,
            'action' => 'DELETE'
        ])->first();

        $this->assertNotNull($auditLog->old_values);
        $this->assertNull($auditLog->new_values);
        $this->assertEquals('BM-TEST-DELETE-REAL', $auditLog->old_values['header']['nomor_nota']);
    }

    public function test_deleting_transaction_deletes_orphaned_product_with_zero_stock_and_no_other_history(): void
    {
        // 1. Create a product with 0 stock that will be orphaned upon deletion
        $product = Produk::create([
            'kode_barang' => 'PROD-ORPHAN',
            'nama_produk' => 'Orphaned Product',
            'nama_barang' => 'Orphaned Product Display',
            'kategori' => 'Sandal Dewasa',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-A01',
            'hpp' => 0.00,
            'harga_jual' => 30000.00,
            'is_active' => true,
            'stok_masuk' => 0,
            'stok_keluar' => 0,
            'total_stok' => 0,
        ]);

        // 2. Create the BarangMasuk transaction
        $bm = BarangMasuk::create([
            'tanggal' => '2026-06-24',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-TEST-ORPHAN-DEL',
            'data_dimasukan_oleh' => 'System',
        ]);

        $detail = $bm->details()->create([
            'produk_id' => $product->id,
            'qty_masuk' => 5,
            'satuan' => 'pcs',
            'lokasi_simpan' => 'GDG-A01',
            'hpp' => 26000.00,
        ]);

        // Set the product stock to represent this transaction
        $product->total_stok += 5;
        $product->stok_masuk += 5;
        $product->hpp = 26000.00;
        $product->save();

        $stokGudang = StokLokasi::where(['produk_id' => $product->id, 'lokasi_id' => $this->lokasiGudang->id])->first();
        $stokGudang->qty += 5;
        $stokGudang->save();

        // 3. Delete the transaction
        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Pembelian\Index::class)
            ->call('confirmedDelete', ['id' => $bm->id])
            ->assertHasNoErrors();

        // 4. Verify that the product is completely deleted from the database
        $this->assertDatabaseMissing('mst_produk', ['id' => $product->id]);
        $this->assertDatabaseMissing('trx_stok_lokasi', ['produk_id' => $product->id]);
    }

    public function test_cannot_delete_or_edit_transaction_if_it_leads_to_negative_stock(): void
    {
        // 1. Create a product with initial stock
        $product = Produk::create([
            'kode_barang' => 'PROD-NEG-CHECK',
            'nama_produk' => 'Product Negative Check',
            'nama_barang' => 'Product Negative Check Display',
            'kategori' => 'Sandal Dewasa',
            'variasi_barang' => 'Hitam / 40',
            'satuan' => 'pcs',
            'lokasi' => 'GDG-A01',
            'hpp' => 20000.00,
            'harga_jual' => 30000.00,
            'is_active' => true,
            'stok_masuk' => 5,
            'stok_keluar' => 0,
            'total_stok' => 5,
        ]);

        // 2. Create the BarangMasuk transaction we will try to delete
        $bm = BarangMasuk::create([
            'tanggal' => '2026-06-24',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'nomor_nota' => 'BM-TEST-NEG-1',
            'data_dimasukan_oleh' => 'System',
        ]);

        $detail = $bm->details()->create([
            'produk_id' => $product->id,
            'qty_masuk' => 5,
            'satuan' => 'pcs',
            'lokasi_simpan' => 'GDG-A01',
            'hpp' => 20000.00,
        ]);

        StokLokasi::updateOrCreate(
            ['produk_id' => $product->id, 'lokasi_id' => $this->lokasiGudang->id],
            ['qty' => 5]
        );

        // Simulate that 3 of these units have been sold/withdrawn, leaving only 2 in stock
        $product->total_stok = 2;
        $product->stok_keluar = 3;
        $product->save();

        $stokGudang = StokLokasi::where(['produk_id' => $product->id, 'lokasi_id' => $this->lokasiGudang->id])->first();
        $stokGudang->qty = 2;
        $stokGudang->save();

        // 3. Try to delete the transaction (which would subtract 5 from stock, making it -3)
        $this->actingAs($this->admin);

        Livewire::test(\App\Livewire\Pembelian\Index::class)
            ->call('confirmedDelete', ['id' => $bm->id])
            ->assertDispatched('alert:error');

        // Verify that the transaction was NOT deleted
        $this->assertDatabaseHas('trx_barang_masuk_header', ['id' => $bm->id]);

        // Verify that the stock remains at 2 (not negative)
        $product->refresh();
        $this->assertEquals(2, $product->total_stok);
    }
}
