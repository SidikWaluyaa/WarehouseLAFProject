<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Produk;
use App\Models\BarangMasukDetail;
use App\Models\BarangKeluarDetail;

class Topbar extends Component
{
    protected $listeners = ['refreshTopbar' => '$refresh'];

    public function render()
    {
        // 1. Fetch low stock alerts dynamically
        $colTotalStok = 'mst_produk.total_stok';
        $colMinimalStock = 'analytics_produk.minimal_stock';

        $lowStockProducts = Produk::with('analyticsProduk')
            ->whereHas('analyticsProduk', function ($query) use ($colTotalStok, $colMinimalStock) {
                $query->whereColumn($colTotalStok, '<=', $colMinimalStock)
                      ->where($colMinimalStock, '>', 0);
            })
            ->take(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => 'low-stock-' . $product->id,
                    'type' => 'low_stock',
                    'title' => 'Stok Menipis',
                    'message' => "Produk {$product->nama_produk} ({$product->kode_barang}) tersisa {$product->total_stok} {$product->satuan} (min: " . ($product->analyticsProduk->minimal_stock ?? 0) . ").",
                    'time' => 'Batas Minimum',
                    'timestamp' => now()->timestamp,
                    'link' => route('stock-master', ['search' => $product->kode_barang]),
                ];
            });

        // 2. Fetch latest 5 incoming goods details
        $recentIn = BarangMasukDetail::with(['barangMasukHeader', 'produk'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($detail) {
                $tanggal = $detail->barangMasukHeader?->tanggal ? $detail->barangMasukHeader->tanggal->format('d M Y') : 'Baru-baru ini';
                return [
                    'id' => 'trx-in-' . $detail->id,
                    'type' => 'transaction_in',
                    'title' => 'Barang Masuk',
                    'message' => "Penerimaan {$detail->qty_masuk} {$detail->satuan} untuk {$detail->produk?->nama_produk} (Nota: {$detail->barangMasukHeader?->nomor_nota}).",
                    'time' => $tanggal,
                    'timestamp' => $detail->created_at?->timestamp ?? now()->timestamp,
                    'link' => route('barang-masuk.riwayat'),
                ];
            });

        // 3. Fetch latest 5 outgoing goods details
        $recentOut = BarangKeluarDetail::with(['barangKeluarHeader', 'produk'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($detail) {
                $tanggal = $detail->barangKeluarHeader?->tanggal ? $detail->barangKeluarHeader->tanggal->format('d M Y') : 'Baru-baru ini';
                return [
                    'id' => 'trx-out-' . $detail->id,
                    'type' => 'transaction_out',
                    'title' => 'Barang Keluar',
                    'message' => "Pengeluaran {$detail->qty_keluar} {$detail->satuan} untuk {$detail->produk?->nama_produk} (Nota: {$detail->barangKeluarHeader?->nomor_nota}).",
                    'time' => $tanggal,
                    'timestamp' => $detail->created_at?->timestamp ?? now()->timestamp,
                    'link' => route('barang-keluar.riwayat'),
                ];
            });

        // 4. Combine and sort
        $notifications = collect()
            ->concat($lowStockProducts)
            ->concat($recentIn)
            ->concat($recentOut)
            ->sortByDesc(fn($item) => $item['timestamp'])
            ->values()
            ->all();

        return view('livewire.topbar', [
            'notifications' => $notifications,
            'lowStockCount' => $lowStockProducts->count(),
        ]);
    }
}
