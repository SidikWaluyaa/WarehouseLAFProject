<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\Produk;
use App\Models\MutasiLokasi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrintController extends Controller
{
    /**
     * Print Barang Masuk (Goods Receipt) invoice.
     */
    public function barangMasuk(int $id)
    {
        $transaction = BarangMasuk::with(['supplier', 'details.produk'])->findOrFail($id);
        
        return view('print.barang-masuk', compact('transaction'));
    }

    /**
     * Print Barang Keluar (Goods Issue / Delivery Note) invoice.
     */
    public function barangKeluar(int $id)
    {
        $transaction = BarangKeluar::with(['pelanggan', 'details.produk'])->findOrFail($id);
        
        return view('print.barang-keluar', compact('transaction'));
    }

    /**
     * Print a specific report based on filters.
     */
    public function report(Request $request)
    {
        $reportType = $request->query('reportType', 'stock-card');
        $data = [];

        if ($reportType === 'stock-card') {
            $data = $this->getStockCardData($request);
        } elseif ($reportType === 'stock-movement') {
            $data = $this->getStockMovementData($request);
        } elseif ($reportType === 'aging-stock') {
            $data = $this->getAgingStockData($request);
        } elseif ($reportType === 'sales-support') {
            $data = $this->getSalesSupportData($request);
        } elseif ($reportType === 'warehouse-performance') {
            $data = $this->getWarehousePerformanceData();
        }

        return view('print.report', compact('reportType', 'data'));
    }

    private function getStockCardData(Request $request): array
    {
        $productId = $request->query('sc_produk_id');
        $dateFrom = $request->query('sc_date_from');
        $dateTo = $request->query('sc_date_to');

        if (!$productId) {
            return [];
        }

        $produk = Produk::findOrFail($productId);

        $mutations = MutasiLokasi::where('produk_id', $productId)
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderBy('created_at', 'asc')
            ->with(['lokasiAsal', 'lokasiTujuan'])
            ->get();

        $runningBalance = 0;
        $initialMasuk = MutasiLokasi::where('produk_id', $productId)
            ->whereIn('tipe_mutasi', ['MASUK', 'OPNAME_ADJUST'])
            ->whereNotNull('lokasi_tujuan_id')
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '<', $dateFrom))
            ->sum('qty');
        $initialKeluar = MutasiLokasi::where('produk_id', $productId)
            ->whereIn('tipe_mutasi', ['KELUAR', 'OPNAME_ADJUST'])
            ->whereNotNull('lokasi_asal_id')
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '<', $dateFrom))
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

        return [
            'produk' => $produk,
            'saldo_awal' => $initialMasuk - $initialKeluar,
            'rows' => $rows,
            'saldo_akhir' => $runningBalance,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    private function getStockMovementData(Request $request): array
    {
        $dateFrom = $request->query('sm_date_from');
        $dateTo = $request->query('sm_date_to');
        $tipe = $request->query('sm_tipe');

        $mutations = MutasiLokasi::with(['produk', 'lokasiAsal', 'lokasiTujuan'])
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($tipe, fn($q) => $q->where('tipe_mutasi', $tipe))
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        return [
            'rows' => $mutations,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'tipe' => $tipe,
        ];
    }

    private function getAgingStockData(Request $request): array
    {
        $kategori1 = $request->query('aging_kategori1');
        $kategori2 = $request->query('aging_kategori2');
        $status = $request->query('aging_status');

        $products = Produk::active()
            ->withSum('stokLokasi as total_qty', 'qty')
            ->when($kategori1, fn($q) => $q->where('kategori', $kategori1))
            ->when($kategori2, fn($q) => $q->where('kategori_2', $kategori2))
            ->get();

        $tempData = [];
        foreach ($products as $p) {
            $lastIncoming = DB::table('trx_barang_masuk_detail')
                ->join('trx_barang_masuk_header', 'trx_barang_masuk_detail.barang_masuk_header_id', '=', 'trx_barang_masuk_header.id')
                ->where('trx_barang_masuk_detail.produk_id', $p->id)
                ->orderBy('trx_barang_masuk_header.tanggal', 'desc')
                ->select('trx_barang_masuk_header.tanggal')
                ->first();

            $lastDate = $lastIncoming ? Carbon::parse($lastIncoming->tanggal) : $p->created_at;
            $age = $lastDate->diffInDays(now());

            $kategoriUsia = 'Fresh Stock';
            if ($age > 90) {
                $kategoriUsia = 'Dead Stock';
            } elseif ($age > 30) {
                $kategoriUsia = 'Slow Moving';
            }

            $tempData[] = [
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

        if ($status) {
            $tempData = array_filter($tempData, fn($item) => $item['kategori_usia'] === $status);
        }

        return [
            'rows' => array_values($tempData),
            'kategori1' => $kategori1,
            'kategori2' => $kategori2,
            'status' => $status,
        ];
    }

    private function getSalesSupportData(Request $request): array
    {
        $dateFrom = $request->query('sales_date_from');
        $dateTo = $request->query('sales_date_to');
        $availStatus = $request->query('avail_status');

        $bestSellers = DB::table('trx_barang_keluar_detail')
            ->join('trx_barang_keluar_header', 'trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id')
            ->join('mst_produk', 'trx_barang_keluar_detail.produk_id', '=', 'mst_produk.id')
            ->where('trx_barang_keluar_header.tipe_nota', 'Penjualan')
            ->when($dateFrom, fn($q) => $q->whereDate('trx_barang_keluar_header.tanggal', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('trx_barang_keluar_header.tanggal', '<=', $dateTo))
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

        $topCategories = DB::table('trx_barang_keluar_detail')
            ->join('trx_barang_keluar_header', 'trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id')
            ->join('mst_produk', 'trx_barang_keluar_detail.produk_id', '=', 'mst_produk.id')
            ->where('trx_barang_keluar_header.tipe_nota', 'Penjualan')
            ->when($dateFrom, fn($q) => $q->whereDate('trx_barang_keluar_header.tanggal', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('trx_barang_keluar_header.tanggal', '<=', $dateTo))
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

        if ($availStatus) {
            $tempAvail = array_filter($tempAvail, fn($item) => $item['status'] === $availStatus);
        }

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

        $transferFillRate = $transferFill && $transferFill->total_req > 0
            ? round(($transferFill->total_sent / $transferFill->total_req) * 100, 1)
            : 100.0;

        $poFillRate = $poFill && $poFill->total_po > 0
            ? round(($poFill->total_received / $poFill->total_po) * 100, 1)
            : 100.0;

        return [
            'bestSellers' => $bestSellers,
            'topCategories' => $topCategories,
            'stockAvailability' => array_values($tempAvail),
            'transferFillRate' => $transferFillRate,
            'poFillRate' => $poFillRate,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'availStatus' => $availStatus,
        ];
    }

    private function getWarehousePerformanceData(): array
    {
        $receivingStats = [
            'total_grn' => DB::table('trx_barang_masuk_header')->count(),
            'total_qty' => DB::table('trx_barang_masuk_detail')->sum('qty_masuk') ?: 0,
            'grn_this_month' => DB::table('trx_barang_masuk_header')->whereMonth('tanggal', now()->month)->count(),
            'qty_this_month' => DB::table('trx_barang_masuk_detail')
                ->join('trx_barang_masuk_header', 'trx_barang_masuk_detail.barang_masuk_header_id', '=', 'trx_barang_masuk_header.id')
                ->whereMonth('trx_barang_masuk_header.tanggal', now()->month)
                ->sum('qty_masuk') ?: 0,
        ];

        $totalPicking = DB::table('trx_picking_list')->count();
        $completedPicking = DB::table('trx_picking_list')->where('status', 'COMPLETED')->count();

        $completedLists = DB::table('trx_picking_list')
            ->where('status', 'COMPLETED')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get();

        $durations = [];
        foreach ($completedLists as $list) {
            $start = Carbon::parse($list->started_at);
            $end = Carbon::parse($list->completed_at);
            $durations[] = $end->diffInMinutes($start);
        }
        $avgPickingDuration = count($durations) > 0 ? round(array_sum($durations) / count($durations), 1) : 0;

        $totalOutgoingDetails = DB::table('trx_barang_keluar_detail')->count();
        $qcPassedDetails = DB::table('trx_barang_keluar_detail')
            ->join('trx_barang_keluar_header', 'trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id')
            ->where('trx_barang_keluar_header.is_qc_passed', true)
            ->count();
        $packingQcRate = $totalOutgoingDetails > 0 ? round(($qcPassedDetails / $totalOutgoingDetails) * 100, 1) : 100.0;

        $pickingStats = [
            'total_picking' => $totalPicking,
            'completed_picking' => $completedPicking,
            'avg_picking_duration' => $avgPickingDuration,
            'total_packed' => DB::table('trx_barang_keluar_detail')
                ->join('trx_barang_keluar_header', 'trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id')
                ->whereNotNull('trx_barang_keluar_header.resi_ekspedisi')
                ->count(),
            'qc_passed_rate' => $packingQcRate,
        ];

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

        return [
            'receivingStats' => $receivingStats,
            'pickingStats' => $pickingStats,
            'inventoryAccuracy' => [
                'total_sistem' => $totalSistem,
                'total_fisik' => $totalFisik,
                'total_variance' => $totalVariance,
                'accuracy_rate' => $accuracyRate,
            ]
        ];
    }

    /**
     * Print Barang Masuk Itemized History (Riwayat Detail Barang)
     */
    public function barangMasukRiwayat(Request $request)
    {
        $search = $request->query('search', '');
        $selectedProductId = $request->query('selectedProductId');
        $tipeFilter = $request->query('tipeFilter', '');
        $tanggalStart = $request->query('tanggalStart', '');
        $tanggalEnd = $request->query('tanggalEnd', '');
        $lokasiFilter = $request->query('lokasiFilter', '');

        $query = \App\Models\BarangMasukDetail::with(['barangMasukHeader.supplier', 'produk']);

        if ($selectedProductId) {
            $query->whereRaw('produk_id = ?', [$selectedProductId]);
        }

        if (!empty($search)) {
            $query->whereHas('barangMasukHeader', function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->whereRaw('nomor_nota LIKE ? OR keterangan_tambahan LIKE ? OR data_dimasukan_oleh LIKE ?', [$term, $term, $term])
                          ->orWhereHas('supplier', function ($s) use ($term) {
                              $s->whereRaw('nama_supplier LIKE ?', [$term]);
                          });
                });
            });
        }

        if (!empty($tipeFilter)) {
            $query->whereHas('barangMasukHeader', function ($q) use ($tipeFilter) {
                $q->whereRaw('tipe_nota = ?', [$tipeFilter]);
            });
        }

        if (!empty($tanggalStart)) {
            $query->whereHas('barangMasukHeader', function ($q) use ($tanggalStart) {
                $q->whereDate('tanggal', '>=', $tanggalStart);
            });
        }
        if (!empty($tanggalEnd)) {
            $query->whereHas('barangMasukHeader', function ($q) use ($tanggalEnd) {
                $q->whereDate('tanggal', '<=', $tanggalEnd);
            });
        }

        if (!empty($lokasiFilter)) {
            $query->whereRaw('lokasi_simpan = ?', [$lokasiFilter]);
        }

        $history = $query->join('trx_barang_masuk_header', function ($join) {
                $join->on('trx_barang_masuk_detail.barang_masuk_header_id', '=', 'trx_barang_masuk_header.id');
            })
            ->orderByRaw('trx_barang_masuk_header.tanggal desc')
            ->orderByRaw('trx_barang_masuk_detail.id desc')
            ->select('trx_barang_masuk_detail.*')
            ->get();

        $selectedProduct = $selectedProductId ? Produk::find($selectedProductId) : null;

        return view('print.barang-masuk-riwayat', compact(
            'history',
            'selectedProduct',
            'search',
            'tipeFilter',
            'tanggalStart',
            'tanggalEnd',
            'lokasiFilter'
        ));
    }

    /**
     * Print Barang Masuk Date-Grouped History (Riwayat Masuk / Tanggal)
     */
    public function barangMasukRiwayatTanggal(Request $request)
    {
        $search = $request->query('search', '');
        $tipeFilter = $request->query('tipeFilter', '');
        $tanggalStart = $request->query('tanggalStart', '');
        $tanggalEnd = $request->query('tanggalEnd', '');
        $lokasiFilter = $request->query('lokasiFilter', '');

        $query = \App\Models\BarangMasukDetail::with(['barangMasukHeader.supplier', 'produk']);

        if (!empty($search)) {
            $term = '%' . $search . '%';
            $query->where(function ($innerQuery) use ($term) {
                $innerQuery->whereHas('barangMasukHeader', function ($q) use ($term) {
                    $q->where('nomor_nota', 'LIKE', $term)
                      ->orWhere('keterangan_tambahan', 'LIKE', $term)
                      ->orWhere('data_dimasukan_oleh', 'LIKE', $term)
                      ->orWhereHas('supplier', function ($s) use ($term) {
                          $s->where('nama_supplier', 'LIKE', $term);
                      });
                })
                ->orWhereHas('produk', function ($q) use ($term) {
                    $q->where('nama_barang', 'LIKE', $term)
                      ->orWhere('kode_barang', 'LIKE', $term);
                });
            });
        }

        if (!empty($tipeFilter)) {
            $colTipe = 'tipe_nota';
            $query->whereHas('barangMasukHeader', function ($q) use ($colTipe, $tipeFilter) {
                $q->where($colTipe, $tipeFilter);
            });
        }

        if (!empty($tanggalStart)) {
            $colDate = 'tanggal';
            $query->whereHas('barangMasukHeader', function ($q) use ($colDate, $tanggalStart) {
                $q->whereDate($colDate, '>=', $tanggalStart);
            });
        }
        if (!empty($tanggalEnd)) {
            $colDate = 'tanggal';
            $query->whereHas('barangMasukHeader', function ($q) use ($colDate, $tanggalEnd) {
                $q->whereDate($colDate, '<=', $tanggalEnd);
            });
        }

        if (!empty($lokasiFilter)) {
            $colLokasi = 'lokasi_simpan';
            $query->where($colLokasi, $lokasiFilter);
        }

        $totalQuery = clone $query;
        $grandTotal = (float) $totalQuery->sum(DB::raw('qty_masuk * trx_barang_masuk_detail.hpp'));

        $sortCol1 = 'trx_barang_masuk_header.tanggal';
        $sortCol2 = 'trx_barang_masuk_detail.id';
        $history = $query->join('trx_barang_masuk_header', function ($join) {
                $join->on('trx_barang_masuk_detail.barang_masuk_header_id', '=', 'trx_barang_masuk_header.id');
            })
            ->orderBy($sortCol1, 'desc')
            ->orderBy($sortCol2, 'desc')
            ->select('trx_barang_masuk_detail.*')
            ->get();

        return view('print.barang-masuk-riwayat-tanggal', compact(
            'history',
            'grandTotal',
            'search',
            'tipeFilter',
            'tanggalStart',
            'tanggalEnd',
            'lokasiFilter'
        ));
    }

    /**
     * Print Barang Keluar Itemized History (Riwayat Detail Barang)
     */
    public function barangKeluarRiwayat(Request $request)
    {
        $search = $request->query('search', '');
        $selectedProductId = $request->query('selectedProductId');
        $tipeFilter = $request->query('tipeFilter', '');
        $tanggalStart = $request->query('tanggalStart', '');
        $tanggalEnd = $request->query('tanggalEnd', '');
        $lokasiFilter = $request->query('lokasiFilter', '');

        $query = \App\Models\BarangKeluarDetail::with(['barangKeluarHeader.pelanggan', 'produk']);

        if ($selectedProductId) {
            $query->whereRaw('produk_id = ?', [$selectedProductId]);
        }

        if (!empty($search)) {
            $query->whereHas('barangKeluarHeader', function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->whereRaw('nomor_nota LIKE ? OR keterangan_tambahan LIKE ? OR dikeluarkan_oleh LIKE ?', [$term, $term, $term])
                          ->orWhereHas('pelanggan', function ($c) use ($term) {
                              $c->whereRaw('nama_pelanggan LIKE ?', [$term]);
                          });
                });
            });
        }

        if (!empty($tipeFilter)) {
            $query->whereHas('barangKeluarHeader', function ($q) use ($tipeFilter) {
                $q->whereRaw('tipe_nota = ?', [$tipeFilter]);
            });
        }

        if (!empty($tanggalStart)) {
            $query->whereHas('barangKeluarHeader', function ($q) use ($tanggalStart) {
                $q->whereDate('tanggal', '>=', $tanggalStart);
            });
        }
        if (!empty($tanggalEnd)) {
            $query->whereHas('barangKeluarHeader', function ($q) use ($tanggalEnd) {
                $q->whereDate('tanggal', '<=', $tanggalEnd);
            });
        }

        if (!empty($lokasiFilter)) {
            $query->whereRaw('lokasi_pengambilan = ?', [$lokasiFilter]);
        }

        $history = $query->join('trx_barang_keluar_header', function ($join) {
                $join->on('trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id');
            })
            ->orderByRaw('trx_barang_keluar_header.tanggal desc')
            ->orderByRaw('trx_barang_keluar_detail.id desc')
            ->select('trx_barang_keluar_detail.*')
            ->get();

        $selectedProduct = $selectedProductId ? Produk::find($selectedProductId) : null;

        return view('print.barang-keluar-riwayat', compact(
            'history',
            'selectedProduct',
            'search',
            'tipeFilter',
            'tanggalStart',
            'tanggalEnd',
            'lokasiFilter'
        ));
    }

    /**
     * Print Barang Keluar Date-Grouped History (Riwayat Keluar / Tanggal)
     */
    public function barangKeluarRiwayatTanggal(Request $request)
    {
        $search = $request->query('search', '');
        $tipeFilter = $request->query('tipeFilter', '');
        $tanggalStart = $request->query('tanggalStart', '');
        $tanggalEnd = $request->query('tanggalEnd', '');
        $lokasiFilter = $request->query('lokasiFilter', '');

        $query = \App\Models\BarangKeluarDetail::with(['barangKeluarHeader.pelanggan', 'produk']);

        if (!empty($search)) {
            $term = '%' . $search . '%';
            $query->where(function ($innerQuery) use ($term) {
                $innerQuery->whereHas('barangKeluarHeader', function ($q) use ($term) {
                    $q->where('nomor_nota', 'LIKE', $term)
                      ->orWhere('keterangan_tambahan', 'LIKE', $term)
                      ->orWhere('dikeluarkan_oleh', 'LIKE', $term)
                      ->orWhereHas('pelanggan', function ($c) use ($term) {
                          $c->where('nama_pelanggan', 'LIKE', $term);
                      });
                })
                ->orWhereHas('produk', function ($q) use ($term) {
                    $q->where('nama_barang', 'LIKE', $term)
                      ->orWhere('kode_barang', 'LIKE', $term);
                });
            });
        }

        if (!empty($tipeFilter)) {
            $colTipe = 'tipe_nota';
            $query->whereHas('barangKeluarHeader', function ($q) use ($colTipe, $tipeFilter) {
                $q->where($colTipe, $tipeFilter);
            });
        }

        if (!empty($tanggalStart)) {
            $colDate = 'tanggal';
            $query->whereHas('barangKeluarHeader', function ($q) use ($colDate, $tanggalStart) {
                $q->whereDate($colDate, '>=', $tanggalStart);
            });
        }
        if (!empty($tanggalEnd)) {
            $colDate = 'tanggal';
            $query->whereHas('barangKeluarHeader', function ($q) use ($colDate, $tanggalEnd) {
                $q->whereDate($colDate, '<=', $tanggalEnd);
            });
        }

        if (!empty($lokasiFilter)) {
            $colLokasi = 'lokasi_pengambilan';
            $query->where($colLokasi, $lokasiFilter);
        }

        $totalQuery = clone $query;
        $grandTotal = (float) $totalQuery->sum(DB::raw('qty_keluar * trx_barang_keluar_detail.hpp'));

        $sortCol1 = 'trx_barang_keluar_header.tanggal';
        $sortCol2 = 'trx_barang_keluar_detail.id';
        $history = $query->join('trx_barang_keluar_header', function ($join) {
                $join->on('trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id');
            })
            ->orderBy($sortCol1, 'desc')
            ->orderBy($sortCol2, 'desc')
            ->select('trx_barang_keluar_detail.*')
            ->get();

        return view('print.barang-keluar-riwayat-tanggal', compact(
            'history',
            'grandTotal',
            'search',
            'tipeFilter',
            'tanggalStart',
            'tanggalEnd',
            'lokasiFilter'
        ));
    }
}
