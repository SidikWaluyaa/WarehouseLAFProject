<?php

namespace App\Livewire\Penjualan;

use App\Traits\HasAlerts;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\BarangKeluarDetail;
use App\Models\Produk;
use App\Models\Lokasi;

#[Layout('layouts.app')]
class Riwayat extends Component
{
    use HasAlerts;
    use WithPagination;

    // Filter states
    public $search = '';
    public ?int $selectedProductId = null;
    public $productSearch = '';
    public $tipeFilter = '';
    public $tanggalStart = '';
    public $tanggalEnd = '';
    public $lokasiFilter = '';

    // Alert messages
    
    

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedProductId' => ['except' => null],
        'tipeFilter' => ['except' => ''],
        'tanggalStart' => ['except' => ''],
        'tanggalEnd' => ['except' => ''],
        'lokasiFilter' => ['except' => ''],
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSelectedProductId() { $this->resetPage(); }
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
        $this->reset(['search', 'selectedProductId', 'productSearch', 'tipeFilter', 'tanggalStart', 'tanggalEnd', 'lokasiFilter']);
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        if (strlen(trim($this->productSearch)) < 2) {
            return [];
        }

        $term = '%' . trim($this->productSearch) . '%';
        return Produk::query()
            ->whereRaw('nama_barang LIKE ? OR kode_barang LIKE ?', [$term, $term])
            ->limit(10)
            ->get();
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

    public function selectProduct(int $productId): void
    {
        $this->selectedProductId = $productId;
        $this->productSearch = '';
        $this->resetPage();
    }

    public function deselectProduct(): void
    {
        $this->selectedProductId = null;
        $this->resetPage();
    }

    public function clearAlerts(): void
    {
        $this->toastSuccess('');
        $this->alertError('');
    }

    public function render()
    {
        $query = BarangKeluarDetail::with(['barangKeluarHeader.pelanggan', 'produk']);

        // Filter by product
        if ($this->selectedProductId) {
            $query->whereRaw('produk_id = ?', [$this->selectedProductId]);
        }

        // Filter by general search (customer name, nomor_nota, keterangan_tambahan)
        if (!empty($this->search)) {
            $query->whereHas('barangKeluarHeader', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->whereRaw('nomor_nota LIKE ? OR keterangan_tambahan LIKE ? OR dikeluarkan_oleh LIKE ?', [$term, $term, $term])
                          ->orWhereHas('pelanggan', function ($c) use ($term) {
                              $c->whereRaw('nama_pelanggan LIKE ?', [$term]);
                          });
                });
            });
        }

        // Filter by tipe nota
        if (!empty($this->tipeFilter)) {
            $query->whereHas('barangKeluarHeader', function ($q) {
                $q->whereRaw('tipe_nota = ?', [$this->tipeFilter]);
            });
        }

        // Filter by date range
        if (!empty($this->tanggalStart)) {
            $query->whereHas('barangKeluarHeader', function ($q) {
                $q->whereDate('tanggal', '>=', $this->tanggalStart);
            });
        }
        if (!empty($this->tanggalEnd)) {
            $query->whereHas('barangKeluarHeader', function ($q) {
                $q->whereDate('tanggal', '<=', $this->tanggalEnd);
            });
        }

        // Filter by lokasi pengambilan
        if (!empty($this->lokasiFilter)) {
            $query->whereRaw('lokasi_pengambilan = ?', [$this->lokasiFilter]);
        }

        // Fetch detail records joined with header to sort by date
        $history = $query->join('trx_barang_keluar_header', function ($join) {
                $join->on('trx_barang_keluar_detail.barang_keluar_header_id', '=', 'trx_barang_keluar_header.id');
            })
            ->orderByRaw('trx_barang_keluar_header.tanggal desc')
            ->orderByRaw('trx_barang_keluar_detail.id desc')
            ->select('trx_barang_keluar_detail.*')
            ->paginate(15);

        $selectedProduct = $this->selectedProductId ? Produk::find($this->selectedProductId) : null;

        return view('livewire.penjualan.riwayat', [
            'history' => $history,
            'selectedProduct' => $selectedProduct,
        ]);
    }
}

