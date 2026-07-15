<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\Pelanggan;
use App\Models\PoHeader;
use App\Models\PoDetail;
use App\Models\AnalyticsProduk;
use App\Services\StockEngineService;
use App\Services\AnalyticsService;
use Exception;

class StockEngineTest extends TestCase
{
    use RefreshDatabase;

    private $supplier;
    private $pelanggan;
    private $produk;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup basic models
        $this->supplier = Supplier::create([
            'nama_supplier' => 'PT Test Supplier',
            'kontak' => '0812345',
            'alamat' => 'Alamat Test',
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama_pelanggan' => 'Toko Test Customer',
            'kontak' => '0823456',
            'alamat' => 'Alamat Customer',
        ]);

        $this->produk = Produk::create([
            'nama_produk' => 'Sandal Jepit',
            'kategori' => 'SANDAL',
            'kode_barang' => 'SDL-JEP',
            'nama_barang' => 'Sandal Jepit Keren',
            'variasi_barang' => 'Biru / 40',
            'satuan' => 'pasang',
            'hpp' => 10000.00,
            'stok_masuk' => 10,
            'stok_keluar' => 0,
            'total_stok' => 10,
            'lokasi' => 'GUDANG',
        ]);

        // Create 1-to-1 Analytics
        AnalyticsProduk::create([
            'produk_id' => $this->produk->id,
            'kategori' => $this->produk->kategori,
            'penjualan_jan' => 0,
            'penjualan_apr' => 0,
            'penjualan_mei' => 0,
            'rata_rata_harian' => 0.00,
            'leadtime_produksi' => 10,
            'minimal_stock' => 0,
            'safety_stock' => 0,
            'maximum_stock' => 0,
        ]);
    }

    /**
     * Test Weighted Average HPP Calculation on incoming transaction.
     */
    public function test_weighted_average_hpp_calculation(): void
    {
        // Initial stock: 10, HPP: 10.000
        // New incoming transaction: qty: 10, price: 20.000
        // Formula: ((10 * 10000) + (10 * 20000)) / (10 + 10) = 15.000
        
        $data = [
            'tanggal' => '2026-06-16',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'data_dimasukan_oleh' => 'Test Operator',
            'items' => [
                [
                    'produk_id' => $this->produk->id,
                    'qty_masuk' => 10,
                    'hpp' => 20000.00,
                    'lokasi_simpan' => 'GUDANG',
                ]
            ]
        ];

        $transaction = StockEngineService::tambahBarangMasuk($data);

        $this->produk->refresh();

        $this->assertEquals(20, $this->produk->total_stok);
        $this->assertEquals(20, $this->produk->stok_masuk);
        $this->assertEquals(15000.00, (float)$this->produk->hpp);
        $this->assertEquals(20000.00, (float)$transaction->details->first()->hpp);
    }

    /**
     * Test exception is thrown when outgoing stock exceeds total stock.
     */
    public function test_prevent_negative_stock_on_outgoing_transaction(): void
    {
        // Stock: 10, requested: 11
        $data = [
            'tanggal' => '2026-06-16',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'dikeluarkan_oleh' => 'Test Sales',
            'items' => [
                [
                    'produk_id' => $this->produk->id,
                    'qty_keluar' => 11,
                    'lokasi_pengambilan' => 'GUDANG',
                ]
            ]
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Stok produk 'Sandal Jepit Keren' tidak mencukupi!");

        StockEngineService::tambahBarangKeluar($data);
    }

    /**
     * Test stock decreases and transaction HPP matches current product HPP.
     */
    public function test_successful_outgoing_transaction(): void
    {
        $data = [
            'tanggal' => '2026-06-16',
            'tipe_nota' => 'Penjualan',
            'pelanggan_id' => $this->pelanggan->id,
            'dikeluarkan_oleh' => 'Test Sales',
            'items' => [
                [
                    'produk_id' => $this->produk->id,
                    'qty_keluar' => 4,
                    'lokasi_pengambilan' => 'GUDANG',
                ]
            ]
        ];

        $transaction = StockEngineService::tambahBarangKeluar($data);

        $this->produk->refresh();

        $this->assertEquals(6, $this->produk->total_stok);
        $this->assertEquals(4, $this->produk->stok_keluar);
        $this->assertEquals(10000.00, (float)$transaction->details->first()->hpp); // HPP of transaction snapshot matches current product HPP
    }

    /**
     * Test PO details tracking update on incoming goods.
     */
    public function test_po_tracking_updates_on_incoming_goods(): void
    {
        // 1. Create PO Header
        $poHeader = PoHeader::create([
            'tanggal' => '2026-06-16',
            'supplier_id' => $this->supplier->id,
            'artikel' => 'ART-1',
            'jumlah' => 15,
        ]);

        // 2. Create PO Detail
        $poDetail = PoDetail::create([
            'po_header_id' => $poHeader->id,
            'produk_id' => $this->produk->id,
            'jumlah' => 15,
            'barang_masuk' => 0,
            'sisa' => 15,
            'hpp' => 12000.00,
            'total_hpp' => 15 * 12000.00,
        ]);

        // 3. Add incoming goods referencing this PO
        $data = [
            'tanggal' => '2026-06-16',
            'tipe_nota' => 'Pembelian',
            'supplier_id' => $this->supplier->id,
            'data_dimasukan_oleh' => 'Test Operator',
            'items' => [
                [
                    'produk_id' => $this->produk->id,
                    'qty_masuk' => 10,
                    'hpp' => 12000.00,
                    'lokasi_simpan' => 'GUDANG',
                    'po_detail_id' => $poDetail->id,
                ]
            ]
        ];

        StockEngineService::tambahBarangMasuk($data);

        $poDetail->refresh();

        // 4. Verify sisa outstanding PO decreased
        $this->assertEquals(10, $poDetail->barang_masuk);
        $this->assertEquals(5, $poDetail->sisa);
        $this->assertEquals(5 * 12000.00, (float)$poDetail->total_hpp);
    }
}
