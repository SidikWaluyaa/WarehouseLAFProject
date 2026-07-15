<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Produk;
use App\Models\MutasiLokasi;
use App\Models\BarangMasukDetail;
use App\Models\BarangKeluarDetail;
use App\Models\TransferHeader;
use App\Models\OpnameDetail;
use App\Models\PickingList;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    public $reportType = 'stock-card';

    // Stock Card filters & data
    public $sc_produk_id = '';
    public $sc_date_from = '';
    public $sc_date_to = '';
    public $stockCardData = [];

    // Stock Movement filters & data
    public $sm_date_from = '';
    public $sm_date_to = '';
    public $sm_tipe = '';
    public $movementData = [];

    // Aging Stock filters & data
    public $aging_kategori1 = '';
    public $aging_kategori2 = '';
    public $aging_status = ''; // Fresh Stock, Slow Moving, Dead Stock
    public $agingData = [];

    // Sales Support filters & data
    public $sales_date_from = '';
    public $sales_date_to = '';
    public $avail_status = ''; // Critical, Warning, Optimal, Overstock
    public $bestSellers = [];
    public $topCategories = [];
    public $stockAvailability = [];
    public $transferFillRate = 0;
    public $poFillRate = 0;

    // Warehouse Performance data
    public $receivingStats = [];
    public $pickingStats = [];
    public $inventoryAccuracy = [];

    public function mount()
    {
        $this->sc_date_from = now()->startOfMonth()->format('Y-m-d');
        $this->sc_date_to = now()->format('Y-m-d');
        $this->sm_date_from = now()->startOfMonth()->format('Y-m-d');
        $this->sm_date_to = now()->format('Y-m-d');
        
        $this->sales_date_from = now()->subDays(30)->format('Y-m-d');
        $this->sales_date_to = now()->format('Y-m-d');

        // Automatically trigger loads
        $this->generateAgingStock();
        $this->generateSalesSupport();
        $this->generateWarehousePerformance();
    }

    public function generateStockCard()
    {
        if (empty($this->sc_produk_id)) {
            $this->stockCardData = [];
            return;
        }

        $produk = Produk::findOrFail($this->sc_produk_id);

        $mutations = MutasiLokasi::where('produk_id', $this->sc_produk_id)
            ->when($this->sc_date_from, fn($q) => $q->whereDate('created_at', '>=', $this->sc_date_from))
            ->when($this->sc_date_to, fn($q) => $q->whereDate('created_at', '<=', $this->sc_date_to))
            ->orderBy('created_at', 'asc')
            ->with(['lokasiAsal', 'lokasiTujuan'])
            ->get();

        // Calculate running balance
        $runningBalance = 0;
        // Get initial balance before date range
        $initialMasuk = MutasiLokasi::where('produk_id', $this->sc_produk_id)
            ->whereIn('tipe_mutasi', ['MASUK', 'OPNAME_ADJUST'])
            ->whereNotNull('lokasi_tujuan_id')
            ->when($this->sc_date_from, fn($q) => $q->whereDate('created_at', '<', $this->sc_date_from))
            ->sum('qty');
        $initialKeluar = MutasiLokasi::where('produk_id', $this->sc_produk_id)
            ->whereIn('tipe_mutasi', ['KELUAR', 'OPNAME_ADJUST'])
            ->whereNotNull('lokasi_asal_id')
            ->when($this->sc_date_from, fn($q) => $q->whereDate('created_at', '<', $this->sc_date_from))
            ->sum('qty');

        $runningBalance = $initialMasuk - $initialKeluar;

        $rows = [];
        foreach ($mutations as $m) {
            $qtyIn = 0;
            $qtyOut = 0;

            if (in_array($m->tipe_mutasi, ['MASUK']) || ($m->tipe_mutasi === 'OPNAME_ADJUST' && $m->lokasi_tujuan_id && !$m->lokasi_asal_id)) {
                $qtyIn = $m->qty;
                $runningBalance += $qtyIn;
            } elseif (in_array($m->tipe_mutasi, ['KELUAR']) || ($m->tipe_mutasi === 'OPNAME_ADJUST' && $m->lokasi_asal_id && !$m->lokasi_tujuan_id)) {
                $qtyOut = $m->qty;
                $runningBalance -= $qtyOut;
            } elseif ($m->tipe_mutasi === 'TRANSFER') {
                $qtyOut = $m->qty;
                $runningBalance -= $qtyOut;
            }

            $rows[] = [
                'tanggal' => $m->created_at->format('d/m/Y H:i'),
                'tipe' => $m->tipe_mutasi,
                'keterangan' => $m->keterangan,
                'lokasi_asal' => $m->lokasiAsal ? $m->lokasiAsal->kode_lokasi : '-',
                'lokasi_tujuan' => $m->lokasiTujuan ? $m->lokasiTujuan->kode_lokasi : '-',
                'qty_masuk' => $qtyIn,
                'qty_keluar' => $qtyOut,
                'saldo' => $runningBalance,
                'operator' => $m->data_dimasukan_oleh,
            ];
        }

        $this->stockCardData = [
            'produk' => $produk,
            'saldo_awal' => $initialMasuk - $initialKeluar,
            'rows' => $rows,
            'saldo_akhir' => $runningBalance,
        ];
    }

    public function generateMovement()
    {
        $query = MutasiLokasi::with(['produk', 'lokasiAsal', 'lokasiTujuan'])
            ->when($this->sm_date_from, fn($q) => $q->whereDate('created_at', '>=', $this->sm_date_from))
            ->when($this->sm_date_to, fn($q) => $q->whereDate('created_at', '<=', $this->sm_date_to))
            ->when($this->sm_tipe, fn($q) => $q->where('tipe_mutasi', $this->sm_tipe))
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        $this->movementData = $query->toArray();
    }

    public function generateAgingStock()
    {
        $products = Produk::active()
            ->withSum('stokLokasi as total_qty', 'qty')
            ->when($this->aging_kategori1, fn($q) => $q->where('kategori', $this->aging_kategori1))
            ->when($this->aging_kategori2, fn($q) => $q->where('kategori_2', $this->aging_kategori2))
            ->get();

        $tempData = [];
        foreach ($products as $p) {
            $lastIncoming = DB::table('trx_barang_masuk_detail')
                ->join('trx_barang_masuk_header', 'trx_barang_masuk_detail.barang_masuk_header_id', '=', 'trx_barang_masuk_header.id')
                ->where('trx_barang_masuk_detail.produk_id', $p->id)
                ->orderBy('trx_barang_masuk_header.tanggal', 'desc')
                ->select('trx_barang_masuk_header.tanggal')
                ->first();

            $lastDate = $lastIncoming ? \Carbon\Carbon::parse($lastIncoming->tanggal) : $p->created_at;
            $age = $lastDate->diffInDays(now());

            $kategoriUsia = 'Fresh Stock';
            if ($age > 90) {
                $kategoriUsia = 'Dead Stock';
            } elseif ($age > 30) {
                $kategoriUsia = 'Slow Moving';
            }

            $tempData[] = [
                'id' => $p->id,
                'kode_barang' => $p->kode_barang,
                'nama_barang' => $p->nama_barang,
                'kategori' => $p->kategori,
                'kategori_2' => $p->kategori_2 ?: 'Produk Berjalan',
                'total_qty' => $p->total_qty ?: 0,
                'hpp' => $p->hpp,
                'total_nilai' => ($p->total_qty ?: 0) * $p->hpp,
                'tanggal_terakhir' => $lastDate->format('d/m/Y'),
                'usia_hari' => $age,
                'kategori_usia' => $kategoriUsia,
            ];
        }

        // Apply PHP filtering for age status
        if ($this->aging_status) {
            $tempData = array_filter($tempData, fn($item) => $item['kategori_usia'] === $this->aging_status);
        }

        $this->agingData = array_values($tempData);
    }

    public function generateSalesSupport()
    {
        // 1. Best Sellers (Top 10)
        $this->bestSellers = DB::table('trx_barang_keluar_detail')
            ->join('trx_barang_keluar_header', 'trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id')
            ->join('mst_produk', 'trx_barang_keluar_detail.produk_id', '=', 'mst_produk.id')
            ->where('trx_barang_keluar_header.tipe_nota', 'Penjualan')
            ->when($this->sales_date_from, fn($q) => $q->whereDate('trx_barang_keluar_header.tanggal', '>=', $this->sales_date_from))
            ->when($this->sales_date_to, fn($q) => $q->whereDate('trx_barang_keluar_header.tanggal', '<=', $this->sales_date_to))
            ->select(
                'mst_produk.kode_barang',
                'mst_produk.nama_barang',
                'mst_produk.kategori',
                DB::raw('SUM(trx_barang_keluar_detail.qty_keluar) as total_qty'),
                DB::raw('SUM(trx_barang_keluar_detail.qty_keluar * mst_produk.harga_jual) as total_sales_value')
            )
            ->groupBy('mst_produk.id', 'mst_produk.kode_barang', 'mst_produk.nama_barang', 'mst_produk.kategori')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        // 2. Top Kategori
        $this->topCategories = DB::table('trx_barang_keluar_detail')
            ->join('trx_barang_keluar_header', 'trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id')
            ->join('mst_produk', 'trx_barang_keluar_detail.produk_id', '=', 'mst_produk.id')
            ->where('trx_barang_keluar_header.tipe_nota', 'Penjualan')
            ->when($this->sales_date_from, fn($q) => $q->whereDate('trx_barang_keluar_header.tanggal', '>=', $this->sales_date_from))
            ->when($this->sales_date_to, fn($q) => $q->whereDate('trx_barang_keluar_header.tanggal', '<=', $this->sales_date_to))
            ->select(
                'mst_produk.kategori',
                DB::raw('SUM(trx_barang_keluar_detail.qty_keluar) as total_qty'),
                DB::raw('SUM(trx_barang_keluar_detail.qty_keluar * mst_produk.harga_jual) as total_sales_value')
            )
            ->groupBy('mst_produk.kategori')
            ->orderBy('total_qty', 'desc')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();

        // 3. Stock Availability Levels
        $avail = Produk::active()
            ->withSum('stokLokasi as total_qty', 'qty')
            ->with('analyticsProduk')
            ->get();

        $tempAvail = [];
        foreach ($avail as $p) {
            $qty = $p->total_qty ?: 0;
            $min = $p->analyticsProduk->minimal_stock ?? 0;
            $safety = $p->analyticsProduk->safety_stock ?? 0;
            $max = $p->analyticsProduk->maximum_stock ?? 0;

            $status = 'Optimal';
            if ($qty < $min) {
                $status = 'Critical';
            } elseif ($qty < $safety) {
                $status = 'Warning';
            } elseif ($qty > $max) {
                $status = 'Overstock';
            }

            $recommQty = 0;
            if ($qty < $safety) {
                $recommQty = max(0, $max - $qty);
            }

            $tempAvail[] = [
                'kode_barang' => $p->kode_barang,
                'nama_barang' => $p->nama_barang,
                'kategori' => $p->kategori,
                'total_qty' => $qty,
                'min_stock' => $min,
                'safety_stock' => $safety,
                'max_stock' => $max,
                'status' => $status,
                'recomm_qty' => $recommQty,
            ];
        }

        if ($this->avail_status) {
            $tempAvail = array_filter($tempAvail, fn($item) => $item['status'] === $this->avail_status);
        }
        $this->stockAvailability = array_values($tempAvail);

        // 4. Fill Rates
        $transferFill = DB::table('trx_transfer_detail')
            ->select(
                DB::raw('SUM(qty_requested) as total_req'),
                DB::raw('SUM(qty_sent) as total_sent'),
                DB::raw('SUM(qty_received) as total_received')
            )
            ->first();

        $poFill = DB::table('po_detail')
            ->select(
                DB::raw('SUM(jumlah) as total_po'),
                DB::raw('SUM(barang_masuk) as total_received')
            )
            ->first();

        $this->transferFillRate = $transferFill && $transferFill->total_req > 0
            ? round(($transferFill->total_sent / $transferFill->total_req) * 100, 1)
            : 100.0;

        $this->poFillRate = $poFill && $poFill->total_po > 0
            ? round(($poFill->total_received / $poFill->total_po) * 100, 1)
            : 100.0;
    }

    public function generateWarehousePerformance()
    {
        // 1. Receiving Performance
        $this->receivingStats = [
            'total_grn' => DB::table('trx_barang_masuk_header')->count(),
            'total_qty' => DB::table('trx_barang_masuk_detail')->sum('qty_masuk') ?: 0,
            'grn_this_month' => DB::table('trx_barang_masuk_header')->whereMonth('tanggal', now()->month)->count(),
            'qty_this_month' => DB::table('trx_barang_masuk_detail')
                ->join('trx_barang_masuk_header', 'trx_barang_masuk_detail.barang_masuk_header_id', '=', 'trx_barang_masuk_header.id')
                ->whereMonth('trx_barang_masuk_header.tanggal', now()->month)
                ->sum('qty_masuk') ?: 0,
        ];

        // 2. Picking & Packing
        $totalPicking = DB::table('trx_picking_list')->count();
        $completedPicking = DB::table('trx_picking_list')->where('status', 'COMPLETED')->count();

        $completedLists = DB::table('trx_picking_list')
            ->where('status', 'COMPLETED')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get();

        $durations = [];
        foreach ($completedLists as $list) {
            $start = \Carbon\Carbon::parse($list->started_at);
            $end = \Carbon\Carbon::parse($list->completed_at);
            $durations[] = $end->diffInMinutes($start);
        }
        $avgPickingDuration = count($durations) > 0 ? round(array_sum($durations) / count($durations), 1) : 0;

        $totalOutgoingDetails = DB::table('trx_barang_keluar_detail')->count();
        $qcPassedDetails = DB::table('trx_barang_keluar_detail')
            ->join('trx_barang_keluar_header', 'trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id')
            ->where('trx_barang_keluar_header.is_qc_passed', true)
            ->count();
        $packingQcRate = $totalOutgoingDetails > 0 ? round(($qcPassedDetails / $totalOutgoingDetails) * 100, 1) : 100.0;

        $this->pickingStats = [
            'total_picking' => $totalPicking,
            'completed_picking' => $completedPicking,
            'avg_picking_duration' => $avgPickingDuration,
            'total_packed' => DB::table('trx_barang_keluar_detail')
                ->join('trx_barang_keluar_header', 'trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id')
                ->whereNotNull('trx_barang_keluar_header.resi_ekspedisi')
                ->count(),
            'qc_passed_rate' => $packingQcRate,
        ];

        // 3. Inventory Accuracy from Opname
        $opnameStats = DB::table('trx_opname_detail')
            ->join('trx_opname_header', 'trx_opname_detail.opname_header_id', '=', 'trx_opname_header.id')
            ->where('trx_opname_header.status', 'ADJUSTED')
            ->select(
                DB::raw('SUM(qty_sistem) as total_sistem'),
                DB::raw('SUM(qty_fisik) as total_fisik'),
                DB::raw('SUM(ABS(selisih)) as total_variance_qty')
            )
            ->first();

        $totalSistem = $opnameStats->total_sistem ?? 0;
        $totalFisik = $opnameStats->total_fisik ?? 0;
        $totalVariance = $opnameStats->total_variance_qty ?? 0;

        $accuracyRate = $totalSistem > 0
            ? max(0, round((1 - ($totalVariance / $totalSistem)) * 100, 2))
            : 100.0;

        $this->inventoryAccuracy = [
            'total_sistem' => $totalSistem,
            'total_fisik' => $totalFisik,
            'total_variance' => $totalVariance,
            'accuracy_rate' => $accuracyRate,
        ];
    }

    public function exportCsv(string $type)
    {
        $headers = [];
        $rows = [];
        $filename = 'export.csv';

        if ($type === 'stock-card') {
            $this->generateStockCard();
            if (empty($this->stockCardData)) {
                return;
            }
            $filename = 'stock_card_' . $this->stockCardData['produk']->kode_barang . '.csv';
            $headers = ['Tanggal', 'Tipe', 'Keterangan', 'Asal', 'Tujuan', 'Masuk', 'Keluar', 'Saldo', 'Operator'];
            
            $rows[] = ['Saldo Awal', '', '', '', '', '', '', $this->stockCardData['saldo_awal'], ''];
            foreach ($this->stockCardData['rows'] as $r) {
                $rows[] = [
                    $r['tanggal'],
                    $r['tipe'],
                    $r['keterangan'],
                    $r['lokasi_asal'],
                    $r['lokasi_tujuan'],
                    $r['qty_masuk'],
                    $r['qty_keluar'],
                    $r['saldo'],
                    $r['operator']
                ];
            }
            $rows[] = ['Saldo Akhir', '', '', '', '', '', '', $this->stockCardData['saldo_akhir'], ''];

        } elseif ($type === 'stock-movement') {
            $this->generateMovement();
            $filename = 'stock_movement_' . now()->format('Ymd') . '.csv';
            $headers = ['Tanggal', 'SKU', 'Nama Barang', 'Tipe Mutasi', 'Qty', 'Lokasi Asal', 'Lokasi Tujuan', 'Keterangan', 'Operator'];
            foreach ($this->movementData as $m) {
                $rows[] = [
                    \Carbon\Carbon::parse($m['created_at'])->format('d/m/Y H:i'),
                    $m['produk']['kode_barang'] ?? '-',
                    $m['produk']['nama_barang'] ?? '-',
                    $m['tipe_mutasi'],
                    $m['qty'],
                    $m['lokasi_asal']['kode_lokasi'] ?? '-',
                    $m['lokasi_tujuan']['kode_lokasi'] ?? '-',
                    $m['keterangan'],
                    $m['data_dimasukan_oleh']
                ];
            }

        } elseif ($type === 'aging-stock') {
            $this->generateAgingStock();
            $filename = 'aging_stock_' . now()->format('Ymd') . '.csv';
            $headers = ['SKU', 'Nama Barang', 'Kategori 1', 'Kategori 2', 'Total Stok', 'HPP', 'Total Nilai HPP', 'Penerimaan Terakhir', 'Usia Stok (Hari)', 'Status Usia'];
            foreach ($this->agingData as $a) {
                $rows[] = [
                    $a['kode_barang'],
                    $a['nama_barang'],
                    $a['kategori'],
                    $a['kategori_2'],
                    $a['total_qty'],
                    $a['hpp'],
                    $a['total_nilai'],
                    $a['tanggal_terakhir'],
                    $a['usia_hari'],
                    $a['kategori_usia']
                ];
            }

        } elseif ($type === 'sales-support') {
            $this->generateSalesSupport();
            $filename = 'sales_support_' . now()->format('Ymd') . '.csv';
            $headers = ['SKU', 'Nama Barang', 'Kategori', 'Qty Terjual', 'Estimasi Omset'];
            foreach ($this->bestSellers as $b) {
                $rows[] = [
                    $b['kode_barang'],
                    $b['nama_barang'],
                    $b['kategori'],
                    $b['total_qty'],
                    $b['total_sales_value']
                ];
            }

        } elseif ($type === 'stock-availability') {
            $this->generateSalesSupport();
            $filename = 'stock_availability_' . now()->format('Ymd') . '.csv';
            $headers = ['SKU', 'Nama Barang', 'Kategori', 'Stok Saat Ini', 'Min Stock', 'Safety Stock', 'Max Stock', 'Status', 'Rekomendasi Replenish'];
            foreach ($this->stockAvailability as $s) {
                $rows[] = [
                    $s['kode_barang'],
                    $s['nama_barang'],
                    $s['kategori'],
                    $s['total_qty'],
                    $s['min_stock'],
                    $s['safety_stock'],
                    $s['max_stock'],
                    $s['status'],
                    $s['recomm_qty']
                ];
            }
        }

        return response()->streamDownload(function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename);
    }

    public function render()
    {
        $productsList = Produk::active()->orderBy('nama_barang')->get();

        return view('livewire.reports.index', [
            'productsList' => $productsList,
        ]);
    }
}

