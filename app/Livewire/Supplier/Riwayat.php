<?php

namespace App\Livewire\Supplier;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\BarangMasukDetail;
use App\Models\Supplier;
use App\Models\Lokasi;

use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class Riwayat extends Component
{
    use WithPagination;
    use HasAlerts;

    // Filter states
    public $search = '';
    public ?int $selectedSupplierId = null;
    public $supplierSearch = '';
    public $tipeFilter = '';
    public $tanggalStart = '';
    public $tanggalEnd = '';
    public $lokasiFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedSupplierId' => ['except' => null],
        'tipeFilter' => ['except' => ''],
        'tanggalStart' => ['except' => ''],
        'tanggalEnd' => ['except' => ''],
        'lokasiFilter' => ['except' => ''],
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSelectedSupplierId() { $this->resetPage(); }
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
        $this->reset(['search', 'selectedSupplierId', 'supplierSearch', 'tipeFilter', 'tanggalStart', 'tanggalEnd', 'lokasiFilter']);
        $this->resetPage();
    }

    #[Computed]
    public function suppliers()
    {
        if (strlen(trim($this->supplierSearch)) < 2) {
            return [];
        }

        $term = '%' . trim($this->supplierSearch) . '%';
        $column = 'nama_supplier';
        return Supplier::query()
            ->where($column, 'LIKE', $term)
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

    public function selectSupplier(int $supplierId): void
    {
        $this->selectedSupplierId = $supplierId;
        $this->supplierSearch = '';
        $this->resetPage();
    }

    public function deselectSupplier(): void
    {
        $this->selectedSupplierId = null;
        $this->resetPage();
    }

    public function clearAlerts(): void
    {
        $this->toastSuccess('');
        $this->alertError('');
    }

    public function render()
    {
        $query = BarangMasukDetail::with(['barangMasukHeader.supplier', 'produk']);

        // Filter by supplier
        if ($this->selectedSupplierId) {
            $colSupplierId = 'supplier_id';
            $query->whereHas('barangMasukHeader', function ($q) use ($colSupplierId) {
                $q->where($colSupplierId, $this->selectedSupplierId);
            });
        }

        // Filter by general search (nomor_nota, keterangan_tambahan, data_dimasukan_oleh, product name/sku)
        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(function ($innerQuery) use ($term) {
                $innerQuery->whereHas('barangMasukHeader', function ($q) use ($term) {
                    $q->where('nomor_nota', 'LIKE', $term)
                      ->orWhere('keterangan_tambahan', 'LIKE', $term)
                      ->orWhere('data_dimasukan_oleh', 'LIKE', $term);
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
            $query->whereHas('barangMasukHeader', function ($q) use ($colTipe) {
                $q->where($colTipe, $this->tipeFilter);
            });
        }

        // Filter by date range
        if (!empty($this->tanggalStart)) {
            $colDate = 'tanggal';
            $query->whereHas('barangMasukHeader', function ($q) use ($colDate) {
                $q->whereDate($colDate, '>=', $this->tanggalStart);
            });
        }
        if (!empty($this->tanggalEnd)) {
            $colDate = 'tanggal';
            $query->whereHas('barangMasukHeader', function ($q) use ($colDate) {
                $q->whereDate($colDate, '<=', $this->tanggalEnd);
            });
        }

        // Filter by lokasi simpan
        if (!empty($this->lokasiFilter)) {
            $colLokasi = 'lokasi_simpan';
            $query->where($colLokasi, $this->lokasiFilter);
        }

        // Fetch detail records joined with header to sort by date
        $sortCol1 = 'trx_barang_masuk_header.tanggal';
        $sortCol2 = 'trx_barang_masuk_detail.id';
        $history = $query->join('trx_barang_masuk_header', function ($join) {
                $join->on('trx_barang_masuk_detail.barang_masuk_header_id', '=', 'trx_barang_masuk_header.id');
            })
            ->orderBy($sortCol1, 'desc')
            ->orderBy($sortCol2, 'desc')
            ->select('trx_barang_masuk_detail.*')
            ->paginate(15);

        $selectedSupplier = $this->selectedSupplierId ? Supplier::find($this->selectedSupplierId) : null;

        return view('livewire.supplier.riwayat', [
            'history' => $history,
            'selectedSupplier' => $selectedSupplier,
        ]);
    }
}
