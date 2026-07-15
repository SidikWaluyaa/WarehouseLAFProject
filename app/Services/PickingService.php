<?php

namespace App\Services;

use App\Models\PickingList;
use App\Models\PickingDetail;
use App\Models\Produk;
use App\Models\StokLokasi;
use App\Models\Lokasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class PickingService
{
    /**
     * Get FIFO location recommendations for picking a product.
     */
    public static function getFifoLocationsForProduct(int $produkId, int $qtyToPick): array
    {
        // 1. Get all locations that currently have stock for this product
        $stokLokasis = StokLokasi::where('produk_id', $produkId)
            ->where('qty', '>', 0)
            ->with('lokasi')
            ->get();

        if ($stokLokasis->isEmpty()) {
            return [];
        }

        // 2. Find the oldest receiving dates for this product at each location
        $receivedAt = DB::table('trx_barang_masuk_detail')
            ->join('trx_barang_masuk_header', 'trx_barang_masuk_detail.barang_masuk_header_id', '=', 'trx_barang_masuk_header.id')
            ->where('trx_barang_masuk_detail.produk_id', $produkId)
            ->select('trx_barang_masuk_detail.lokasi_simpan', DB::raw('MIN(trx_barang_masuk_header.tanggal) as oldest_date'))
            ->groupBy('trx_barang_masuk_detail.lokasi_simpan')
            ->get()
            ->pluck('oldest_date', 'lokasi_simpan')
            ->toArray();

        // 3. Map and sort the stock locations by the oldest receiving date (FIFO)
        $mapped = $stokLokasis->map(function ($stok) use ($receivedAt) {
            $kodeLokasi = $stok->lokasi->kode_lokasi;
            $date = $receivedAt[$kodeLokasi] ?? $receivedAt[strtoupper($kodeLokasi)] ?? '9999-12-31';
            return [
                'stok_lokasi' => $stok,
                'oldest_date' => $date,
            ];
        })->sortBy('oldest_date')->values();

        // 4. Distribute the qty_to_pick across these locations FIFO-style
        $results = [];
        $remainingQty = $qtyToPick;

        foreach ($mapped as $item) {
            if ($remainingQty <= 0) break;

            $stok = $item['stok_lokasi'];
            $available = $stok->qty;
            $pickQty = min($available, $remainingQty);

            if ($pickQty > 0) {
                $results[] = [
                    'lokasi_id' => $stok->lokasi_id,
                    'kode_lokasi' => $stok->lokasi->kode_lokasi,
                    'qty' => $pickQty,
                ];
                $remainingQty -= $pickQty;
            }
        }

        return $results;
    }

    /**
     * Generate Picking List for a given reference (BarangKeluar or TransferHeader).
     */
    public static function generatePickingList(string $referensiTipe, int $referensiId, ?string $pickerName = null): PickingList
    {
        try {
            return DB::transaction(function () use ($referensiTipe, $referensiId, $pickerName) {
                // Check if picking list already exists
                $existing = PickingList::where('referensi_tipe', $referensiTipe)
                    ->where('referensi_id', $referensiId)
                    ->first();
                if ($existing) {
                    return $existing;
                }

                // Get source items based on reference type
                $items = [];
                $lokasiId = null;

                if ($referensiTipe === 'BarangKeluar' || $referensiTipe === 'App\Models\BarangKeluar') {
                    $header = \App\Models\BarangKeluar::with('details')->findOrFail($referensiId);
                    
                    // Get the location of the first detail item in the invoice
                    $firstDetail = $header->details->first();
                    $lokasi = null;
                    if ($firstDetail) {
                        $lokasiCode = $firstDetail->lokasi_pengambilan;
                        $lokasi = Lokasi::where('kode_lokasi', $lokasiCode)->first();
                    }
                    $lokasiId = $lokasi ? $lokasi->id : (Lokasi::where('tipe_lokasi', 'GUDANG')->first()->id ?? 1);
                    
                    foreach ($header->details as $detail) {
                        $items[] = [
                            'produk_id' => $detail->produk_id,
                            'qty' => $detail->qty_keluar,
                        ];
                    }
                } elseif ($referensiTipe === 'TransferHeader' || $referensiTipe === 'App\Models\TransferHeader') {
                    $header = \App\Models\TransferHeader::findOrFail($referensiId);
                    $lokasiId = $header->lokasi_asal_id;

                    foreach ($header->details as $detail) {
                        $items[] = [
                            'produk_id' => $detail->produk_id,
                            'qty' => $detail->qty_requested,
                        ];
                    }
                } else {
                    throw new Exception("Tipe referensi tidak valid.");
                }

                // Generate nomor picking (PCK/YYYYMMDD/XXXX)
                $prefix = 'PCK/' . date('Ymd') . '/';
                $latest = PickingList::where('nomor_picking', 'like', $prefix . '%')
                    ->orderByRaw('nomor_picking desc')
                    ->first();
                $nextNum = $latest ? ((int)substr($latest->nomor_picking, -4)) + 1 : 1;
                $nomorPicking = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                $pickingList = PickingList::create([
                    'nomor_picking' => $nomorPicking,
                    'referensi_tipe' => $referensiTipe,
                    'referensi_id' => $referensiId,
                    'lokasi_id' => $lokasiId,
                    'status' => 'PENDING',
                    'picker_name' => $pickerName,
                ]);

                // Add picking details using FIFO recommendation
                foreach ($items as $item) {
                    $fifoLocations = self::getFifoLocationsForProduct($item['produk_id'], $item['qty']);

                    if (empty($fifoLocations)) {
                        PickingDetail::create([
                            'picking_list_id' => $pickingList->id,
                            'produk_id' => $item['produk_id'],
                            'lokasi_rak_id' => $lokasiId,
                            'qty_to_pick' => $item['qty'],
                            'qty_picked' => 0,
                            'is_verified' => false,
                        ]);
                    } else {
                        foreach ($fifoLocations as $fifoLoc) {
                            PickingDetail::create([
                                'picking_list_id' => $pickingList->id,
                                'produk_id' => $item['produk_id'],
                                'lokasi_rak_id' => $fifoLoc['lokasi_id'],
                                'qty_to_pick' => $fifoLoc['qty'],
                                'qty_picked' => 0,
                                'is_verified' => false,
                            ]);
                        }
                    }
                }

                // Update status on TransferHeader if picking is triggered
                if ($referensiTipe === 'TransferHeader' || $referensiTipe === 'App\Models\TransferHeader') {
                    $header = \App\Models\TransferHeader::findOrFail($referensiId);
                    $header->status = 'PICKING';
                    $header->save();
                }

                // Audit Logging (Big 4 standard)
                $newValues = [
                    'picking_list' => $pickingList->toArray(),
                    'details' => PickingDetail::where('picking_list_id', $pickingList->id)->get()->toArray()
                ];

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : ($pickerName ?? 'System'),
                    'action' => 'CREATE',
                    'auditable_type' => get_class($pickingList),
                    'auditable_id' => $pickingList->id,
                    'old_values' => null,
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return $pickingList;
            });
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in PickingService::generatePickingList: " . $e->getMessage(), [
                'referensi_tipe' => $referensiTipe,
                'referensi_id' => $referensiId,
                'picker_name' => $pickerName,
            ]);
            throw $e;
        }
    }

    /**
     * Start the picking process.
     */
    public static function startPicking(int $pickingListId, string $pickerName): PickingList
    {
        try {
            return DB::transaction(function () use ($pickingListId, $pickerName) {
                $pickingList = PickingList::lockForUpdate()->findOrFail($pickingListId);
                if ($pickingList->status !== 'PENDING') {
                    throw new Exception("Hanya picking list berstatus PENDING yang dapat dimulai.");
                }

                $oldValues = [
                    'picking_list' => $pickingList->toArray(),
                    'details' => PickingDetail::where('picking_list_id', $pickingList->id)->get()->toArray()
                ];

                $pickingList->status = 'PICKING';
                $pickingList->picker_name = $pickerName;
                $pickingList->started_at = now();
                $pickingList->save();

                $newValues = [
                    'picking_list' => $pickingList->fresh()->toArray(),
                    'details' => PickingDetail::where('picking_list_id', $pickingList->id)->get()->toArray()
                ];

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : $pickerName,
                    'action' => 'UPDATE',
                    'auditable_type' => get_class($pickingList),
                    'auditable_id' => $pickingList->id,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return $pickingList;
            });
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in PickingService::startPicking: " . $e->getMessage(), [
                'picking_list_id' => $pickingListId,
                'picker_name' => $pickerName,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Verify picked item quantity.
     */
    public static function verifyPickItem(int $detailId, int $qtyPicked): PickingDetail
    {
        $detail = PickingDetail::findOrFail($detailId);
        $detail->qty_picked = $qtyPicked;
        $detail->is_verified = ($qtyPicked >= $detail->qty_to_pick);
        $detail->save();

        return $detail;
    }

    /**
     * Complete the picking process.
     */
    public static function completePicking(int $pickingListId): PickingList
    {
        try {
            return DB::transaction(function () use ($pickingListId) {
                $pickingList = PickingList::lockForUpdate()->findOrFail($pickingListId);
                if ($pickingList->status !== 'PICKING') {
                    throw new Exception("Hanya picking list berstatus PICKING yang dapat diselesaikan.");
                }

                $oldValues = [
                    'picking_list' => $pickingList->toArray(),
                    'details' => PickingDetail::where('picking_list_id', $pickingList->id)->get()->toArray()
                ];

                $pickingList->status = 'COMPLETED';
                $pickingList->completed_at = now();
                $pickingList->save();

                $newValues = [
                    'picking_list' => $pickingList->fresh()->toArray(),
                    'details' => PickingDetail::where('picking_list_id', $pickingList->id)->get()->toArray()
                ];

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : 'System',
                    'action' => 'UPDATE',
                    'auditable_type' => get_class($pickingList),
                    'auditable_id' => $pickingList->id,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return $pickingList;
            });
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in PickingService::completePicking: " . $e->getMessage(), [
                'picking_list_id' => $pickingListId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
