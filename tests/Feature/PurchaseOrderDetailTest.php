<?php

namespace Tests\Feature;

use App\Livewire\Pembelian\Create as PembelianCreate;
use App\Livewire\PurchaseOrder\Show as PoShow;
use App\Models\PoDetail;
use App\Models\PoHeader;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseOrderDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private PoHeader $po;
    private PoDetail $detailA;
    private PoDetail $detailB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);

        $this->user = User::factory()->create(['name' => 'Admin PO']);
        $this->user->assignRole('admin');

        $supplier = Supplier::create(['nama_supplier' => 'CV Uji', 'kontak' => '0812', 'alamat' => 'Bdg']);
        $mk = fn($kode, $nama) => Produk::create([
            'nama_produk' => $nama, 'kategori' => 'SANDAL', 'kode_barang' => $kode, 'nama_barang' => $nama, 'variasi_barang' => 'HITAM',
            'satuan' => 'psg', 'hpp' => 10000, 'stok_masuk' => 0, 'stok_keluar' => 0, 'total_stok' => 0, 'lokasi' => 'GUDANG',
        ]);
        $a = $mk('ARON-36', 'Aron 36');
        $b = $mk('ARON-37', 'Aron 37');

        $this->po = PoHeader::create(['nomor_po' => 'PO/20261003/0009', 'tanggal' => '2026-10-03', 'supplier_id' => $supplier->id, 'jumlah' => 30]);
        $this->detailA = PoDetail::create(['po_header_id' => $this->po->id, 'produk_id' => $a->id, 'jumlah' => 10, 'barang_masuk' => 0, 'sisa' => 10, 'hpp' => 10000]);
        $this->detailB = PoDetail::create(['po_header_id' => $this->po->id, 'produk_id' => $b->id, 'jumlah' => 20, 'barang_masuk' => 0, 'sisa' => 20, 'hpp' => 10000]);
    }

    public function test_detail_page_renders(): void
    {
        $this->actingAs($this->user)
            ->get(route('purchase-order.show', $this->po->id))
            ->assertOk()
            ->assertSee('PO/20261003/0009')
            ->assertSee('Aron 36')
            ->assertSee('Terima Barang Terpilih');
    }

    public function test_receive_selected_redirects_with_only_chosen_items(): void
    {
        Livewire::actingAs($this->user)
            ->test(PoShow::class, ['poHeader' => $this->po])
            ->set("selected.{$this->detailA->id}", true)
            ->set("qtyTerima.{$this->detailA->id}", 7)
            ->call('receiveSelected')
            ->assertRedirect(route('barang-masuk.create', ['po_id' => $this->po->id, 'terima' => [$this->detailA->id => 7]]));
    }

    public function test_barang_masuk_prefills_only_selected_items_with_qty(): void
    {
        Livewire::actingAs($this->user)
            ->withQueryParams(['po_id' => $this->po->id, 'terima' => [$this->detailB->id => 25]])
            ->test(PembelianCreate::class)
            ->assertCount('items', 1)
            ->assertSet('items.0.po_detail_id', $this->detailB->id)
            ->assertSet('items.0.qty_masuk', 25)
            ->assertSet('items.0.sisa_po', 20);
    }

    public function test_over_receipt_requires_reason_and_is_recorded(): void
    {
        Livewire::actingAs($this->user)
            ->withQueryParams(['po_id' => $this->po->id, 'terima' => [$this->detailB->id => 25]])
            ->test(PembelianCreate::class)
            ->call('store')
            ->assertHasErrors(['items.0.alasan_selisih'])
            ->set('items.0.alasan_selisih', 'Bonus supplier')
            ->call('store')
            ->assertHasNoErrors();

        $this->detailB->refresh();
        $this->assertEquals(25, $this->detailB->barang_masuk);
        $this->assertEquals(0, $this->detailB->sisa);
        $this->assertEquals('lebih', $this->detailB->statusPenerimaan());
        $this->assertDatabaseHas('trx_barang_masuk_detail', [
            'po_detail_id' => $this->detailB->id, 'selisih_po' => 5, 'alasan_selisih' => 'Bonus supplier',
        ]);
    }

    public function test_close_remaining_item_requires_reason_and_zeroes_sisa(): void
    {
        $this->detailA->update(['barang_masuk' => 6, 'sisa' => 4]);

        Livewire::actingAs($this->user)
            ->test(PoShow::class, ['poHeader' => $this->po])
            ->call('openCloseModal', $this->detailA->id)
            ->call('closeRemaining')
            ->assertHasErrors(['alasanTutup'])
            ->set('alasanTutup', 'Supplier stok habis')
            ->call('closeRemaining')
            ->assertHasNoErrors();

        $this->detailA->refresh();
        $this->assertEquals(0, $this->detailA->sisa);
        $this->assertEquals(4, $this->detailA->qty_ditutup);
        $this->assertEquals('ditutup', $this->detailA->statusPenerimaan());
        $this->assertEquals(20, $this->detailB->fresh()->sisa, 'Item lain tidak ikut ditutup');
    }

    public function test_close_all_remaining(): void
    {
        Livewire::actingAs($this->user)
            ->test(PoShow::class, ['poHeader' => $this->po])
            ->call('openCloseModal')
            ->set('alasanTutup', 'Pesanan dibatalkan')
            ->call('closeRemaining');

        $this->assertEquals(0, $this->po->fresh()->totalSisa());
    }
}
