<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\AnalyticsProduk;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Menghitung ulang rata-rata harian, minimal stock, safety stock, dan maximum stock
     * berdasarkan nilai penjualan 12 bulan yang tersimpan di record database.
     */
    public static function recalculateProductAnalytics(int $produkId): AnalyticsProduk
    {
        return DB::transaction(function () use ($produkId) {
            $analytics = AnalyticsProduk::where(['produk_id' => $produkId])->lockForUpdate()->first();
            
            if (!$analytics) {
                $produk = Produk::findOrFail($produkId);
                $analytics = new AnalyticsProduk();
                $analytics->produk_id = $produkId;
                $analytics->kategori = $produk->kategori;
            }

            // 1. Jumlahkan total penjualan 12 bulan
            $totalSales = (int)$analytics->penjualan_jan +
                          (int)$analytics->penjualan_feb +
                          (int)$analytics->penjualan_mar +
                          (int)$analytics->penjualan_apr +
                          (int)$analytics->penjualan_mei +
                          (int)$analytics->penjualan_jun +
                          (int)$analytics->penjualan_jul +
                          (int)$analytics->penjualan_ags +
                          (int)$analytics->penjualan_sep +
                          (int)$analytics->penjualan_okt +
                          (int)$analytics->penjualan_nov +
                          (int)$analytics->penjualan_des;

            // 2. Bagi dengan 365 hari untuk mendapatkan rata-rata harian
            $rataRataHarian = $totalSales / 365;

            // Update rata_rata_harian
            $analytics->rata_rata_harian = $rataRataHarian;

            if (!$analytics->is_manual_stock) {
                // Ambil leadtime produksi dari database (default: 14 hari)
                $leadtime = $analytics->leadtime_produksi ?? 14;

                // 3. Hitung minimal stock, safety stock, dan maximum stock sesuai rumus PRD
                // MINIMAL STOCK = RATA_RATA_HARIAN x LEADTIME
                $minimalStock = (int)ceil($rataRataHarian * $leadtime);

                // SAFETY STOCK = MINIMAL_STOCK x 1.5 (buffer 50%)
                $safetyStock = (int)ceil($minimalStock * 1.5);

                // MAXIMUM STOCK = SAFETY_STOCK x 1.5
                $maximumStock = (int)ceil($safetyStock * 1.5);

                $analytics->minimal_stock = $minimalStock;
                $analytics->safety_stock = $safetyStock;
                $analytics->maximum_stock = $maximumStock;
            }

            // 4. Simpan record analytics
            $analytics->save();

            return $analytics;
        });
    }

    /**
     * Mengambil data penjualan aktual produk dari database riil per bulan (1 - 12)
     */
    public static function getActualSalesByMonth(int $produkId): array
    {
        $monthExpr = DB::getDriverName() === 'sqlite'
            ? 'CAST(strftime(\'%m\', trx_barang_keluar_header.tanggal) AS INTEGER)'
            : 'MONTH(trx_barang_keluar_header.tanggal)';

        $salesData = DB::table('trx_barang_keluar_detail')
            ->join('trx_barang_keluar_header', 'trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id')
            ->where('trx_barang_keluar_detail.produk_id', $produkId)
            ->where('trx_barang_keluar_header.tipe_nota', 'Penjualan')
            ->select(
                DB::raw("$monthExpr as bulan"),
                DB::raw('SUM(trx_barang_keluar_detail.qty_keluar) as total_qty')
            )
            ->groupBy(DB::raw($monthExpr))
            ->get()
            ->pluck('total_qty', 'bulan')
            ->toArray();

        $months = [
            1 => 'jan', 2 => 'feb', 3 => 'mar', 4 => 'apr', 5 => 'mei', 6 => 'jun',
            7 => 'jul', 8 => 'ags', 9 => 'sep', 10 => 'okt', 11 => 'nov', 12 => 'des'
        ];

        $sales = [];
        foreach ($months as $mNum => $mName) {
            $sales[$mName] = (int)($salesData[$mNum] ?? 0);
        }

        return $sales;
    }

    /**
     * Menghitung ulang seluruh data produk analytics dengan mensinkronisasikan
     * data transaksi riil per bulan terlebih dahulu.
     */
    public static function recalculateAll(): void
    {
        // 1. Fetch all monthly sales for all products in one query to prevent N+1 queries
        $monthExpr = DB::getDriverName() === 'sqlite'
            ? 'CAST(strftime(\'%m\', trx_barang_keluar_header.tanggal) AS INTEGER)'
            : 'MONTH(trx_barang_keluar_header.tanggal)';

        $salesData = DB::table('trx_barang_keluar_detail')
            ->join('trx_barang_keluar_header', 'trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id')
            ->where('trx_barang_keluar_header.tipe_nota', 'Penjualan')
            ->select(
                'trx_barang_keluar_detail.produk_id',
                DB::raw("$monthExpr as bulan"),
                DB::raw('SUM(trx_barang_keluar_detail.qty_keluar) as total_qty')
            )
            ->groupBy('trx_barang_keluar_detail.produk_id', DB::raw($monthExpr))
            ->get();

        $groupedSales = [];
        foreach ($salesData as $row) {
            $groupedSales[$row->produk_id][$row->bulan] = (int)$row->total_qty;
        }

        $products = Produk::all();

        DB::transaction(function () use ($products, $groupedSales) {
            foreach ($products as $produk) {
                $id = $produk->id;
                $analytics = AnalyticsProduk::where(['produk_id' => $id])->lockForUpdate()->first();
                
                if (!$analytics) {
                    $analytics = new AnalyticsProduk();
                    $analytics->produk_id = $id;
                    $analytics->kategori = $produk->kategori;
                }

                $productSales = $groupedSales[$id] ?? [];
                
                $analytics->penjualan_jan = $productSales[1] ?? 0;
                $analytics->penjualan_feb = $productSales[2] ?? 0;
                $analytics->penjualan_mar = $productSales[3] ?? 0;
                $analytics->penjualan_apr = $productSales[4] ?? 0;
                $analytics->penjualan_mei = $productSales[5] ?? 0;
                $analytics->penjualan_jun = $productSales[6] ?? 0;
                $analytics->penjualan_jul = $productSales[7] ?? 0;
                $analytics->penjualan_ags = $productSales[8] ?? 0;
                $analytics->penjualan_sep = $productSales[9] ?? 0;
                $analytics->penjualan_okt = $productSales[10] ?? 0;
                $analytics->penjualan_nov = $productSales[11] ?? 0;
                $analytics->penjualan_des = $productSales[12] ?? 0;

                // Recalculate stats in memory
                $totalSales = (int)$analytics->penjualan_jan +
                              (int)$analytics->penjualan_feb +
                              (int)$analytics->penjualan_mar +
                              (int)$analytics->penjualan_apr +
                              (int)$analytics->penjualan_mei +
                              (int)$analytics->penjualan_jun +
                              (int)$analytics->penjualan_jul +
                              (int)$analytics->penjualan_ags +
                              (int)$analytics->penjualan_sep +
                              (int)$analytics->penjualan_okt +
                              (int)$analytics->penjualan_nov +
                              (int)$analytics->penjualan_des;

                $rataRataHarian = $totalSales / 365;
                $analytics->rata_rata_harian = $rataRataHarian;

                if (!$analytics->is_manual_stock) {
                    $leadtime = $analytics->leadtime_produksi ?? 14;
                    $minimalStock = (int)ceil($rataRataHarian * $leadtime);
                    $safetyStock = (int)ceil($minimalStock * 1.5);
                    $maximumStock = (int)ceil($safetyStock * 1.5);

                    $analytics->minimal_stock = $minimalStock;
                    $analytics->safety_stock = $safetyStock;
                    $analytics->maximum_stock = $maximumStock;
                }

                $analytics->save();
            }
        });

        // Recalculate ABC classifications for all products
        self::recalculateAbcClassifications();
    }

    /**
     * Sinkronisasi data transaksi riil dan hitung ulang data analytics untuk satu produk.
     */
    public static function syncAndRecalculateProduct(int $produkId): void
    {
        self::syncAndRecalculateProducts([$produkId]);
    }

    /**
     * Sinkronisasi data transaksi riil dan hitung ulang data analytics untuk banyak produk secara batch.
     */
    public static function syncAndRecalculateProducts(array $productIds): void
    {
        $uniqueIds = array_unique(array_filter($productIds));
        if (empty($uniqueIds)) {
            return;
        }

        foreach ($uniqueIds as $produkId) {
            $actualSales = self::getActualSalesByMonth($produkId);
            
            DB::transaction(function () use ($produkId, $actualSales) {
                $analytics = AnalyticsProduk::where(['produk_id' => $produkId])->lockForUpdate()->first();
                
                if (!$analytics) {
                    $produk = Produk::find($produkId);
                    if (!$produk) {
                        return;
                    }
                    $analytics = new AnalyticsProduk();
                    $analytics->produk_id = $produkId;
                    $analytics->kategori = $produk->kategori;
                }
                
                $analytics->penjualan_jan = $actualSales['jan'];
                $analytics->penjualan_feb = $actualSales['feb'];
                $analytics->penjualan_mar = $actualSales['mar'];
                $analytics->penjualan_apr = $actualSales['apr'];
                $analytics->penjualan_mei = $actualSales['mei'];
                $analytics->penjualan_jun = $actualSales['jun'];
                $analytics->penjualan_jul = $actualSales['jul'];
                $analytics->penjualan_ags = $actualSales['ags'];
                $analytics->penjualan_sep = $actualSales['sep'];
                $analytics->penjualan_okt = $actualSales['okt'];
                $analytics->penjualan_nov = $actualSales['nov'];
                $analytics->penjualan_des = $actualSales['des'];
                $analytics->save();
            });

            self::recalculateProductAnalytics($produkId);
        }

        // Recalculate ABC classifications ONCE for the entire batch
        self::recalculateAbcClassifications();
    }

    /**
     * Recalculate ABC classification for all products based on annual sales.
     */
    public static function recalculateAbcClassifications(): void
    {
        $records = AnalyticsProduk::select('id', 'produk_id')
            ->selectRaw('(COALESCE(penjualan_jan, 0) + COALESCE(penjualan_feb, 0) + COALESCE(penjualan_mar, 0) + COALESCE(penjualan_apr, 0) + COALESCE(penjualan_mei, 0) + COALESCE(penjualan_jun, 0) + COALESCE(penjualan_jul, 0) + COALESCE(penjualan_ags, 0) + COALESCE(penjualan_sep, 0) + COALESCE(penjualan_okt, 0) + COALESCE(penjualan_nov, 0) + COALESCE(penjualan_des, 0)) as annual_sales')
            ->orderByRaw('annual_sales DESC')
            ->get();

        $totalSales = $records->sum('annual_sales');
        $runningSum = 0;

        DB::transaction(function() use ($records, $totalSales, &$runningSum) {
            foreach ($records as $record) {
                $annualSales = (int)$record->annual_sales;
                if ($totalSales > 0) {
                    $runningSum += $annualSales;
                    $cumulativePercent = ($runningSum / $totalSales) * 100;
                } else {
                    $cumulativePercent = 100;
                }

                if ($annualSales == 0) {
                    $class = 'C';
                } elseif ($cumulativePercent <= 80) {
                    $class = 'A';
                } elseif ($cumulativePercent <= 95) {
                    $class = 'B';
                } else {
                    $class = 'C';
                }

                DB::table('analytics_produk')
                    ->where('id', $record->id)
                    ->update(['abc_class' => $class]);
            }
        });
    }
}
