<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Produk;
use App\Models\PoDetail;
use App\Models\BarangMasukDetail;
use App\Models\BarangKeluarDetail;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        // Cache heavy inventory and analytics stats for 5 minutes
        $stats = cache()->remember('dashboard_kpi_stats', 300, function () {
            $totalProduk = Produk::count();
            $totalStok = Produk::sum('total_stok') ?: 0;
            $inventoryValue = Produk::query()->select(DB::raw('SUM(total_stok * hpp) as total'))->value('total') ?? 0;
            
            $lowStockCount = Produk::leftJoin('analytics_produk', function ($join) {
                $join->on('mst_produk.id', '=', 'analytics_produk.produk_id');
            })
                ->whereColumn('mst_produk.total_stok', '<', 'analytics_produk.minimal_stock')
                ->count();

            $deadStockProducts = Produk::leftJoin('analytics_produk', function ($join) {
                $join->on('mst_produk.id', '=', 'analytics_produk.produk_id');
            })
                ->where([
                    ['mst_produk.total_stok', '>', 0],
                    ['analytics_produk.rata_rata_harian', '=', 0]
                ])
                ->select('mst_produk.total_stok', 'mst_produk.hpp')
                ->get();
            
            $deadStockCount = $deadStockProducts->count();
            $deadStockValue = $deadStockProducts->sum(function ($p) {
                return $p->total_stok * $p->hpp;
            });

            // Single Group By query for ABC counts
            $abcCounts = \App\Models\AnalyticsProduk::select('abc_class', DB::raw('count(*) as total'))
                ->groupBy('abc_class')
                ->pluck('total', 'abc_class')
                ->toArray();
            
            $classACount = $abcCounts['A'] ?? 0;
            $classBCount = $abcCounts['B'] ?? 0;
            $classCCount = $abcCounts['C'] ?? 0;

            // Single aggregate query for seasonal demand SUM
            $sums = \App\Models\AnalyticsProduk::selectRaw('
                SUM(penjualan_jan) as jan,
                SUM(penjualan_feb) as feb,
                SUM(penjualan_mar) as mar,
                SUM(penjualan_apr) as apr,
                SUM(penjualan_mei) as mei,
                SUM(penjualan_jun) as jun,
                SUM(penjualan_jul) as jul,
                SUM(penjualan_ags) as ags,
                SUM(penjualan_sep) as sep,
                SUM(penjualan_okt) as okt,
                SUM(penjualan_nov) as nov,
                SUM(penjualan_des) as des
            ')->first();

            $monthlySales = [
                'Jan' => (int)($sums->jan ?? 0),
                'Feb' => (int)($sums->feb ?? 0),
                'Mar' => (int)($sums->mar ?? 0),
                'Apr' => (int)($sums->apr ?? 0),
                'Mei' => (int)($sums->mei ?? 0),
                'Jun' => (int)($sums->jun ?? 0),
                'Jul' => (int)($sums->jul ?? 0),
                'Ags' => (int)($sums->ags ?? 0),
                'Sep' => (int)($sums->sep ?? 0),
                'Okt' => (int)($sums->okt ?? 0),
                'Nov' => (int)($sums->nov ?? 0),
                'Des' => (int)($sums->des ?? 0),
            ];

            $tempSales = array_filter($monthlySales, fn($sales) => $sales > 0);
            arsort($tempSales);
            $peakMonths = array_slice(array_keys($tempSales), 0, 3);

            return compact(
                'totalProduk',
                'totalStok',
                'inventoryValue',
                'lowStockCount',
                'deadStockCount',
                'deadStockValue',
                'classACount',
                'classBCount',
                'classCCount',
                'monthlySales',
                'peakMonths'
            );
        });

        // Real-time PoDetail sum
        $sisaPo = PoDetail::sum('sisa');
        
        // Warehouse Operations KPIs (PRD Section 7)
        $todayStr = now()->format('Y-m-d');
        $receivingToday = \App\Models\BarangMasuk::whereDate('tanggal', $todayStr)->count();
        $pickingToday = \App\Models\PickingList::whereDate('created_at', $todayStr)->count();
        $shipmentToday = \App\Models\BarangKeluar::whereDate('tanggal', $todayStr)->count();
        $pendingTransfer = \App\Models\TransferHeader::whereIn('status', ['DRAFT', 'PICKING', 'IN_TRANSIT'])->count();
        $replenishmentRequest = count(\App\Services\ReplenishmentService::getReplenishmentRecommendations());

        // Reorder Recommendations (Not cached to allow dynamic stock edits reflecting immediately)
        $reorderProducts = Produk::leftJoin('analytics_produk', function ($join) {
            $join->on('mst_produk.id', '=', 'analytics_produk.produk_id');
        })
            ->whereColumn('mst_produk.total_stok', '<', 'analytics_produk.minimal_stock')
            ->select('mst_produk.*', 'analytics_produk.minimal_stock', 'analytics_produk.safety_stock', 'analytics_produk.maximum_stock', 'analytics_produk.rata_rata_harian', 'analytics_produk.leadtime_produksi')
            ->limit(10)
            ->get();

        foreach ($reorderProducts as $p) {
            $dailySales = (float)($p->rata_rata_harian ?? 0);
            $p->doi = $dailySales > 0 ? (int)floor($p->total_stok / $dailySales) : 999;
            $p->rop = (int)ceil(($dailySales * ($p->leadtime_produksi ?? 14)) + ($p->safety_stock ?? 0));
            $p->reorder_qty = max(0, ($p->maximum_stock ?? 0) - $p->total_stok);
        }

        // Recent transactions
        $recentMasuk = BarangMasukDetail::with(['produk', 'barangMasukHeader.supplier'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $recentKeluar = BarangKeluarDetail::with(['produk', 'barangKeluarHeader.pelanggan'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.dashboard', [
            'totalProduk' => $stats['totalProduk'],
            'totalStok' => $stats['totalStok'],
            'sisaPo' => $sisaPo,
            'lowStockCount' => $stats['lowStockCount'],
            'deadStockValue' => $stats['deadStockValue'],
            'deadStockCount' => $stats['deadStockCount'],
            'reorderProducts' => $reorderProducts,
            'classACount' => $stats['classACount'],
            'classBCount' => $stats['classBCount'],
            'classCCount' => $stats['classCCount'],
            'monthlySales' => $stats['monthlySales'],
            'peakMonths' => $stats['peakMonths'],
            'recentMasuk' => $recentMasuk,
            'recentKeluar' => $recentKeluar,
            'receivingToday' => $receivingToday,
            'pickingToday' => $pickingToday,
            'shipmentToday' => $shipmentToday,
            'pendingTransfer' => $pendingTransfer,
            'replenishmentRequest' => $replenishmentRequest,
            'inventoryValue' => $stats['inventoryValue'],
        ]);
    }
}
