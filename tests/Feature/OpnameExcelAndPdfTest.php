<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lokasi;
use App\Models\Produk;
use App\Models\StokLokasi;
use App\Models\OpnameHeader;
use App\Models\OpnameDetail;
use App\Models\MutasiLokasi;
use App\Exports\OpnameCountSheetExport;
use Livewire\Livewire;
use App\Livewire\Audit\Index;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class OpnameExcelAndPdfTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Lokasi $lokasi;
    protected Produk $product1;
    protected Produk $product2;
    protected OpnameHeader $session;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::findOrCreate('view master', 'web');
        Permission::findOrCreate('manage master', 'web');
        Permission::findOrCreate('input masuk', 'web');

        $role = Role::findOrCreate('Super Admin', 'web');
        $role->givePermissionTo(['view master', 'manage master', 'input masuk']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($role);

        // Setup location
        $this->lokasi = Lokasi::firstOrCreate(
            ['kode_lokasi' => 'GDG-A01'],
            ['nama_lokasi' => 'Gudang Utama - Rak A1', 'tipe_lokasi' => 'GUDANG', 'is_active' => true]
        );

        // Setup products
        $this->product1 = Produk::create([
            'nama_produk' => 'Sandal Aron Hitam',
            'nama_barang' => 'ARON HITAM 36',
            'kode_barang' => 'ARON-36',
            'kategori' => 'SANDAL',
            'variasi_barang' => 'Hitam / 36',
            'warna' => 'Hitam',
            'size' => '36',
            'satuan' => 'pasang',
            'lokasi' => 'GDG-A01',
            'hpp' => 50000,
            'harga_jual' => 100000,
            'stok_masuk' => 20,
            'stok_keluar' => 0,
            'total_stok' => 20,
            'is_active' => true,
        ]);

        $this->product2 = Produk::create([
            'nama_produk' => 'Sandal Aron Coklat',
            'nama_barang' => 'ARON COKLAT 38',
            'kode_barang' => 'ARON-38',
            'kategori' => 'SANDAL',
            'variasi_barang' => 'Coklat / 38',
            'warna' => 'Coklat',
            'size' => '38',
            'satuan' => 'pasang',
            'lokasi' => 'GDG-A01',
            'hpp' => 60000,
            'harga_jual' => 120000,
            'stok_masuk' => 15,
            'stok_keluar' => 0,
            'total_stok' => 15,
            'is_active' => true,
        ]);

        StokLokasi::updateOrCreate(['produk_id' => $this->product1->id, 'lokasi_id' => $this->lokasi->id], ['qty' => 20]);
        StokLokasi::updateOrCreate(['produk_id' => $this->product2->id, 'lokasi_id' => $this->lokasi->id], ['qty' => 15]);

        // Setup Opname draft session
        $this->session = OpnameHeader::create([
            'nomor_opname' => 'OPN/20261003/TEST01',
            'tanggal' => now(),
            'lokasi_id' => $this->lokasi->id,
            'status' => 'DRAFT',
            'keterangan' => 'Audit Test Unit',
            'operator_name' => $this->adminUser->name,
        ]);

        OpnameDetail::create([
            'opname_header_id' => $this->session->id,
            'produk_id' => $this->product1->id,
            'qty_sistem' => 20,
            'qty_fisik' => 20,
            'selisih' => 0,
        ]);

        OpnameDetail::create([
            'opname_header_id' => $this->session->id,
            'produk_id' => $this->product2->id,
            'qty_sistem' => 15,
            'qty_fisik' => 15,
            'selisih' => 0,
        ]);
    }

    public function test_can_export_opname_count_sheet_excel()
    {
        Excel::fake();

        Livewire::actingAs($this->adminUser)
            ->test(Index::class)
            ->call('loadSession', $this->session->id)
            ->call('exportExcel');

        Excel::assertDownloaded('lembar_audit_OPN_20261003_TEST01.xlsx', function (OpnameCountSheetExport $export) {
            $array = $export->array();
            return count($array) === 2 && $array[0][1] === 'ARON-36';
        });
    }

    public function test_can_access_print_pdf_berita_acara()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('audit.print', $this->session->id));

        $response->assertOk();
        $response->assertViewIs('print.audit-opname');
        $response->assertSee('Berita Acara Stock Opname');
        $response->assertSee('OPN/20261003/TEST01');
        $response->assertSee('ARON-36');
        $response->assertSee('ARON-38');
        $response->assertSee('Petugas Pelaksana Opname');
        $response->assertSee('Kepala Gudang');
    }

    public function test_can_open_and_view_discrepancy_modal_stats()
    {
        // Update product1 to have deficit (-2), product2 surplus (+5)
        $detail1 = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product1->id)->first();
        $detail1->update(['qty_fisik' => 18, 'selisih' => -2]);

        $detail2 = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product2->id)->first();
        $detail2->update(['qty_fisik' => 20, 'selisih' => 5]);

        $component = Livewire::actingAs($this->adminUser)
            ->test(Index::class)
            ->call('loadSession', $this->session->id)
            ->call('openDiscrepancyModal');

        $component->assertSet('isDiscrepancyModalOpen', true);
        $component->assertSee('Rangkuman Hasil Audit', false);
        $component->assertSee('-2');
        $component->assertSee('+5');
    }

    public function test_can_import_opname_count_sheet_excel()
    {
        $rows = collect([
            [
                'kode_sku' => 'ARON-36',
                'hasil_fisik' => 18,
                'catatan' => 'Minus 2 rusak',
            ],
            [
                'kode_sku' => 'ARON-38',
                'hasil_fisik' => 22,
                'catatan' => 'Plus 7 terselip',
            ],
        ]);

        $importer = new \App\Imports\OpnameCountSheetImport($this->session->id);
        $importer->collection($rows);

        $this->assertEquals(2, $importer->updatedCount);

        $detail1 = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product1->id)->first();
        $this->assertEquals(18, $detail1->qty_fisik);
        $this->assertEquals(-2, $detail1->selisih);
        $this->assertEquals('Minus 2 rusak', $detail1->catatan);

        $detail2 = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product2->id)->first();
        $this->assertEquals(22, $detail2->qty_fisik);
        $this->assertEquals(7, $detail2->selisih);
        $this->assertEquals('Plus 7 terselip', $detail2->catatan);
    }

    public function test_import_treats_blank_cells_and_aliases_as_zero()
    {
        $rows = collect([
            [
                'kode_sku' => 'ARON-36',
                'hasil_fisik' => '', // blank/empty cell
                'catatan' => 'Kosong di rak',
            ],
            [
                'kode_sku' => 'ARON-38',
                'hasil_fisik' => '-', // dash notation
                'catatan' => 'Habis',
            ],
        ]);

        $importer = new \App\Imports\OpnameCountSheetImport($this->session->id);
        $importer->collection($rows);

        $this->assertEquals(2, $importer->updatedCount);

        $detail1 = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product1->id)->first();
        $this->assertEquals(0, $detail1->qty_fisik);
        $this->assertEquals(-20, $detail1->selisih); // 0 - 20 = -20

        $detail2 = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product2->id)->first();
        $this->assertEquals(0, $detail2->qty_fisik);
        $this->assertEquals(-15, $detail2->selisih); // 0 - 15 = -15
    }

    public function test_import_rejects_negative_numbers()
    {
        $this->expectException(\Exception::class);

        $rows = collect([
            [
                'kode_sku' => 'ARON-36',
                'hasil_fisik' => -5,
                'catatan' => 'Invalid negative',
            ],
        ]);

        $importer = new \App\Imports\OpnameCountSheetImport($this->session->id);
        $importer->collection($rows);
    }

    public function test_import_with_collection_rows_properly_reads_numeric_qty_fisik()
    {
        $rows = collect([
            collect([
                'kode_sku' => 'ARON-36',
                'hasil_fisik' => 18,
                'catatan' => 'Counted 18',
            ]),
            collect([
                'kode_sku' => 'ARON-38',
                'hasil_fisik' => '22',
                'catatan' => 'Counted 22 string',
            ]),
        ]);

        $importer = new \App\Imports\OpnameCountSheetImport($this->session->id);
        $importer->collection($rows);

        $this->assertEquals(2, $importer->updatedCount);

        $detail1 = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product1->id)->first();
        $this->assertEquals(18, $detail1->qty_fisik);
        $this->assertEquals(-2, $detail1->selisih);

        $detail2 = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product2->id)->first();
        $this->assertEquals(22, $detail2->qty_fisik);
        $this->assertEquals(7, $detail2->selisih);
    }

    public function test_can_complete_session_and_execute_stock_adjustment()
    {
        // Set physical count: product1 = 18 (-2), product2 = 20 (+5)
        $detail1 = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product1->id)->first();
        $detail1->update(['qty_fisik' => 18, 'selisih' => -2]);

        $detail2 = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product2->id)->first();
        $detail2->update(['qty_fisik' => 20, 'selisih' => 5]);

        $component = Livewire::actingAs($this->adminUser)
            ->test(Index::class)
            ->call('loadSession', $this->session->id)
            ->call('completeSession');

        $this->assertEquals('COMPLETED', $this->session->fresh()->status);
        $component->assertSet('isDiscrepancyModalOpen', true);

        // Execute adjustment
        $component->call('executeAdjustment');

        $this->assertEquals('ADJUSTED', $this->session->fresh()->status);

        // Verify stock in trx_stok_lokasi is updated to physical count
        $stok1 = StokLokasi::where(['produk_id' => $this->product1->id, 'lokasi_id' => $this->lokasi->id])->first();
        $stok2 = StokLokasi::where(['produk_id' => $this->product2->id, 'lokasi_id' => $this->lokasi->id])->first();
        $this->assertEquals(18, $stok1->qty);
        $this->assertEquals(20, $stok2->qty);

        // Verify global total_stok is synced
        $this->assertEquals(18, $this->product1->fresh()->total_stok);
        $this->assertEquals(20, $this->product2->fresh()->total_stok);

        // Verify audit mutation was logged
        $this->assertDatabaseHas('trx_mutasi_lokasi', [
            'produk_id' => $this->product1->id,
            'qty' => 2,
            'tipe_mutasi' => 'OPNAME_ADJUST',
            'referensi_id' => $this->session->id,
        ]);

        $this->assertDatabaseHas('trx_mutasi_lokasi', [
            'produk_id' => $this->product2->id,
            'qty' => 5,
            'tipe_mutasi' => 'OPNAME_ADJUST',
            'referensi_id' => $this->session->id,
        ]);
    }

    public function test_can_reopen_completed_session_back_to_draft()
    {
        $this->session->update(['status' => 'COMPLETED']);

        Livewire::actingAs($this->adminUser)
            ->test(Index::class)
            ->call('loadSession', $this->session->id)
            ->call('reopenSession');

        $this->assertEquals('DRAFT', $this->session->fresh()->status);
    }

    public function test_can_cancel_completed_session()
    {
        $this->session->update(['status' => 'COMPLETED']);

        Livewire::actingAs($this->adminUser)
            ->test(Index::class)
            ->call('loadSession', $this->session->id)
            ->call('cancelSession');

        $this->assertEquals('CANCELLED', $this->session->fresh()->status);
    }

    public function test_import_excel_staging_preview_and_apply()
    {
        // 1. Staging only parse
        $rows = collect([
            collect([
                'kode_sku' => 'ARON-36',
                'hasil_fisik' => 12,
                'catatan' => 'Preview staging test',
            ]),
        ]);

        $importer = new \App\Imports\OpnameCountSheetImport($this->session->id, isStagingOnly: true);
        $importer->collection($rows);

        $this->assertCount(1, $importer->previewList);
        $this->assertEquals(12, $importer->previewList[0]['qty_fisik_baru']);
        $this->assertTrue($importer->previewList[0]['is_changed']);

        // Verify DB detail has NOT changed yet (still 20)
        $detail = OpnameDetail::where('opname_header_id', $this->session->id)->where('produk_id', $this->product1->id)->first();
        $this->assertEquals(20, $detail->qty_fisik);

        // 2. Apply staging items via Livewire component
        $component = Livewire::actingAs($this->adminUser)
            ->test(Index::class)
            ->call('loadSession', $this->session->id)
            ->set('importPreviewList', $importer->previewList)
            ->call('applyImportPreview');

        $this->assertFalse($component->get('isImportPreviewOpen'));

        // Now detail in DB is updated to 12
        $detailFresh = $detail->fresh();
        $this->assertEquals(12, $detailFresh->qty_fisik);
        $this->assertEquals(-8, $detailFresh->selisih);
        $this->assertEquals('Preview staging test', $detailFresh->catatan);
    }
}
