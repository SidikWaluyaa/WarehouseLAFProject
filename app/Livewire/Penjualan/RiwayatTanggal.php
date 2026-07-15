<?php

namespace App\Livewire\Penjualan;

use App\Traits\HasAlerts;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\BarangKeluarDetail;
use App\Models\Lokasi;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class RiwayatTanggal extends Component
{
    use HasAlerts;
    use WithPagination;

    // Filter states
    public $search = '';
    public $tipeFilter = '';
    public $tanggalStart = '';
    public $tanggalEnd = '';
    public $lokasiFilter = '';

    // Alert states
    
    

    protected $queryString = [
        'search' => ['except' => ''],
        'tipeFilter' => ['except' => ''],
        'tanggalStart' => ['except' => ''],
        'tanggalEnd' => ['except' => ''],
        'lokasiFilter' => ['except' => ''],
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingTipeFilter() { $this->resetPage(); }
    public function updatingTanggalStart() { $this->resetPage(); }
    public function updatingTanggalEnd() { $this->resetPage(); }
    public function updatingLokasiFilter() { $this->resetPage(); }

    /**
     * Called from Alpine/Flatpickr when a date range is selected.
     */
    public function setDateRange(?string $start, ?string $end): void
    {
        $this->tanggalStart = $start ?? '';
        $this->tanggalEnd = $end ?? '';
        $this->resetPage();
    }

    /**
     * Reset all filters at once.
     */
    public function clearAllFilters(): void
    {
        $this->reset(['search', 'tipeFilter', 'tanggalStart', 'tanggalEnd', 'lokasiFilter']);
        $this->resetPage();
    }

    /**
     * Dynamic lokasi options from the database.
     */
    #[Computed]
    public function lokasiOptions(): array
    {
        $column = 'kode_lokasi';
        return Lokasi::all()
            ->pluck($column)
            ->sort()
            ->values()
            ->toArray();
    }

    public function clearAlerts(): void
    {
        $this->toastSuccess('');
        $this->alertError('');
    }

    public function render()
    {
        $query = BarangKeluarDetail::with(['barangKeluarHeader.pelanggan', 'produk']);

        // Filter by general search (pelanggan name, nomor_nota, keterangan_tambahan, product name/sku)
        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
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

        // Filter by tipe nota
        if (!empty($this->tipeFilter)) {
            $colTipe = 'tipe_nota';
            $query->whereHas('barangKeluarHeader', function ($q) use ($colTipe) {
                $q->where($colTipe, $this->tipeFilter);
            });
        }

        // Filter by date range
        if (!empty($this->tanggalStart)) {
            $colDate = 'tanggal';
            $query->whereHas('barangKeluarHeader', function ($q) use ($colDate) {
                $q->whereDate($colDate, '>=', $this->tanggalStart);
            });
        }
        if (!empty($this->tanggalEnd)) {
            $colDate = 'tanggal';
            $query->whereHas('barangKeluarHeader', function ($q) use ($colDate) {
                $q->whereDate($colDate, '<=', $this->tanggalEnd);
            });
        }

        // Filter by lokasi pengambilan
        if (!empty($this->lokasiFilter)) {
            $colLokasi = 'lokasi_pengambilan';
            $query->where($colLokasi, $this->lokasiFilter);
        }

        // Calculate Grand Total of matching records (before pagination)
        $totalQuery = clone $query;
        $grandTotal = (float) $totalQuery->sum(DB::raw('qty_keluar * trx_barang_keluar_detail.hpp'));

        // Fetch detail records joined with header to sort by date
        $sortCol1 = 'trx_barang_keluar_header.tanggal';
        $sortCol2 = 'trx_barang_keluar_detail.id';
        $history = $query->join('trx_barang_keluar_header', function ($join) {
                $join->on('trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id');
            })
            ->orderBy($sortCol1, 'desc')
            ->orderBy($sortCol2, 'desc')
            ->select('trx_barang_keluar_detail.*')
            ->paginate(15);

        return view('livewire.penjualan.riwayat-tanggal', [
            'history' => $history,
            'grandTotal' => $grandTotal,
        ]);
    }
}
