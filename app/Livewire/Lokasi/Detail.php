<?php

namespace App\Livewire\Lokasi;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Lokasi;
use App\Models\StokLokasi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class Detail extends Component
{
    use WithPagination;

    public Lokasi $lokasi;

    #[Url(history: true)]
    public string $search = '';

    public string $sortField = 'mst_produk.kode_barang';
    public string $sortDirection = 'asc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        // Calculate totals for the summary cards (only active stock where qty > 0)
        $totalSkus = $this->lokasi->stokLokasi()->where([['qty', '>', 0]])->count();
        $totalQty = $this->lokasi->stokLokasi()->sum('qty');
        
        // Sum of qty * hpp for products in this location
        $totalVal = $this->lokasi->stokLokasi()
            ->join('mst_produk', function ($join) {
                $join->on('trx_stok_lokasi.produk_id', '=', 'mst_produk.id');
            })
            ->selectRaw('SUM(trx_stok_lokasi.qty * mst_produk.hpp) as total_value')
            ->first()
            ->total_value ?? 0;

        $totalValueFormatted = 'Rp' . number_format($totalVal, 0, ',', '.') . ',-';

        // Query products details inside the location
        $query = StokLokasi::query()
            ->join('mst_produk', function ($join) {
                $join->on('trx_stok_lokasi.produk_id', '=', 'mst_produk.id');
            })
            ->where(['trx_stok_lokasi.lokasi_id' => $this->lokasi->id])
            ->select(
                'trx_stok_lokasi.*',
                'mst_produk.kode_barang',
                'mst_produk.nama_barang',
                'mst_produk.nama_produk',
                'mst_produk.kategori',
                'mst_produk.variasi_barang',
                'mst_produk.satuan',
                'mst_produk.hpp'
            );

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('mst_produk.kode_barang', 'like', '%' . $this->search . '%')
                  ->orWhere('mst_produk.nama_barang', 'like', '%' . $this->search . '%')
                  ->orWhere('mst_produk.kategori', 'like', '%' . $this->search . '%');
            });
        }

        $items = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.lokasi.detail', [
            'items' => $items,
            'totalSkus' => $totalSkus,
            'totalQty' => $totalQty,
            'totalValueFormatted' => $totalValueFormatted,
        ]);
    }
}
