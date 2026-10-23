<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\StokLokasi;
use App\Models\Lokasi;
use App\Models\AnalyticsProduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ReplenishmentService
{
    /**
     * Mendapatkan daftar produk yang memerlukan replenishment di lokasi toko tertentu.
     * Trigger: stok toko < minimal_stock dari analytics_produk.
     *
     * @return Collection Koleksi objek rekomendasi replenishment
     */
    public static function getReplenishmentRecommendations(?int $lokasiTokoId = null): Collection
    {
        // Ambil semua lokasi toko
        $tokoQuery = Lokasi::where('tipe_lokasi', 'TOKO')->where('is_active', true);
        if ($lokasiTokoId) {
            $tokoQuery->where('id', $lokasiTokoId);
        }
        $tokoLocations = $tokoQuery->get();

        // Ambil ID lokasi bertipe GUDANG sekali per eksekusi
        $gudangIds = Lokasi::where('tipe_lokasi', 'GUDANG')
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        // Ambil total stok gudang untuk semua produk dalam SATU query agregat
        $gudangStockMap = empty($gudangIds)
            ? []
            : StokLokasi::whereIn('lokasi_id', $gudangIds)
                ->groupBy('produk_id')
                ->select('produk_id', DB::raw('SUM(qty) as total_gudang_qty'))
                ->pluck('total_gudang_qty', 'produk_id')
                ->map(fn ($qty) => (int) $qty)
                ->all();

        $recommendations = collect();

        foreach ($tokoLocations as $toko) {
            // Produk yang ada stoknya di toko ini
            $stokTokoRecords = StokLokasi::where('lokasi_id', $toko->id)
                ->with(['produk', 'produk.analyticsProduk'])
                ->get();

            // Juga cek produk yang TIDAK punya record stok di toko ini tapi punya minimal_stock > 0
            $existingProdukIds = $stokTokoRecords->pluck('produk_id')->toArray();
            $missingProdukAnalytics = AnalyticsProduk::whereNotIn('produk_id', $existingProdukIds)
                ->where('minimal_stock', '>', 0)
                ->with('produk')
                ->get();

            // Proses record yang sudah ada
            foreach ($stokTokoRecords as $stokRecord) {
                $analytics = $stokRecord->produk->analyticsProduk ?? null;
                if (!$analytics) continue;

                $currentStock = $stokRecord->qty;
                $minStock = $analytics->minimal_stock;
                $maxStock = $analytics->maximum_stock;

                if ($minStock > 0 && $currentStock < $minStock) {
                    $replenishmentQty = max(0, $maxStock - $currentStock);
                    if ($replenishmentQty <= 0) continue;

                    // Cek stok gudang tersedia via lookup array (O(1))
                    $gudangStock = (int) ($gudangStockMap[$stokRecord->produk_id] ?? 0);

                    $recommendations->push((object)[
                        'produk_id' => $stokRecord->produk_id,
                        'produk' => $stokRecord->produk,
                        'lokasi_toko_id' => $toko->id,
                        'lokasi_toko' => $toko,
                        'current_stock' => $currentStock,
                        'minimal_stock' => $minStock,
                        'maximum_stock' => $maxStock,
                        'replenishment_qty' => $replenishmentQty,
                        'gudang_stock_available' => $gudangStock,
                        'feasible_qty' => min($replenishmentQty, $gudangStock),
                        'urgency' => $currentStock <= 0 ? 'KRITIS' : ($currentStock < ($minStock * 0.5) ? 'TINGGI' : 'SEDANG'),
                    ]);
                }
            }

            // Proses produk yang tidak punya record stok di toko (stok = 0)
            foreach ($missingProdukAnalytics as $analytics) {
                $maxStock = $analytics->maximum_stock;
                $minStock = $analytics->minimal_stock;
                $replenishmentQty = $maxStock;

                if ($replenishmentQty <= 0) continue;

                // Cek stok gudang tersedia via lookup array (O(1))
                $gudangStock = (int) ($gudangStockMap[$analytics->produk_id] ?? 0);

                $recommendations->push((object)[
                    'produk_id' => $analytics->produk_id,
                    'produk' => $analytics->produk,
                    'lokasi_toko_id' => $toko->id,
                    'lokasi_toko' => $toko,
                    'current_stock' => 0,
                    'minimal_stock' => $minStock,
                    'maximum_stock' => $maxStock,
                    'replenishment_qty' => $replenishmentQty,
                    'gudang_stock_available' => $gudangStock,
                    'feasible_qty' => min($replenishmentQty, $gudangStock),
                    'urgency' => 'KRITIS',
                ]);
            }
        }

        // Sort by urgency: KRITIS first, then TINGGI, then SEDANG
        $urgencyOrder = ['KRITIS' => 0, 'TINGGI' => 1, 'SEDANG' => 2];
        return $recommendations->sortBy(fn ($r) => $urgencyOrder[$r->urgency] ?? 3)->values();
    }

    /**
     * Hitung total stok yang tersedia di semua lokasi bertipe GUDANG untuk produk tertentu.
     * @param int $produkId
     * @param array|null $gudangIds Opsional jika caller sudah memiliki cache ID lokasi gudang
     */
    public static function getGudangStockForProduct(int $produkId, ?array $gudangIds = null): int
    {
        $gudangIds ??= Lokasi::where('tipe_lokasi', 'GUDANG')->where('is_active', true)->pluck('id')->all();
        return (int) StokLokasi::where('produk_id', $produkId)
            ->whereIn('lokasi_id', $gudangIds)
            ->sum('qty');
    }

    /**
     * Buat transfer order otomatis berdasarkan rekomendasi yang dipilih.
     */
    public static function createReplenishmentTransfer(array $selectedItems, string $createdBy): \App\Models\TransferHeader
    {
        try {
            // Tentukan gudang asal (ambil gudang utama pertama)
            $gudangAsal = Lokasi::where('tipe_lokasi', 'GUDANG')
                ->where('is_active', true)
                ->orderBy('id')
                ->firstOrFail();

            // Semua item harus menuju toko yang sama
            $lokasiTujuanId = $selectedItems[0]['lokasi_toko_id'];

            $items = [];
            foreach ($selectedItems as $item) {
                $items[] = [
                    'produk_id' => $item['produk_id'],
                    'qty_requested' => $item['feasible_qty'],
                ];
            }

            return TransferService::createTransferOrder([
                'tanggal' => now()->format('Y-m-d'),
                'lokasi_asal_id' => $gudangAsal->id,
                'lokasi_tujuan_id' => $lokasiTujuanId,
                'keterangan' => 'Auto-generated dari Replenishment Toko',
                'created_by' => $createdBy,
                'items' => $items,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in ReplenishmentService::createReplenishmentTransfer: " . $e->getMessage(), [
                'selected_items' => $selectedItems,
                'created_by' => $createdBy,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
