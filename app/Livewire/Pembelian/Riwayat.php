<?php

namespace App\Livewire\Pembelian;

use App\Traits\HasAlerts;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\BarangMasukDetail;
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
        $query = BarangMasukDetail::with(['barangMasukHeader.supplier', 'barangMasukHeader.poHeader', 'produk']);

        // Filter by product
        if ($this->selectedProductId) {
            $query->whereRaw('produk_id = ?', [$this->selectedProductId]);
        }

        // Filter by general search (supplier name, nomor_nota, keterangan_tambahan, PO)
        if (!empty($this->search)) {
            $query->whereHas('barangMasukHeader', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->whereRaw('nomor_nota LIKE ? OR keterangan_tambahan LIKE ? OR data_dimasukan_oleh LIKE ?', [$term, $term, $term])
                          ->orWhereHas('supplier', function ($s) use ($term) {
                              $s->whereRaw('nama_supplier LIKE ?', [$term]);
                          })
                          ->orWhereHas('poHeader', function ($poQ) use ($term) {
                              $poQ->whereRaw('nomor_po LIKE ? OR artikel LIKE ?', [$term, $term]);
                          });
                });
            });
        }

        // Filter by tipe nota
        if (!empty($this->tipeFilter)) {
            $query->whereHas('barangMasukHeader', function ($q) {
                $q->whereRaw('tipe_nota = ?', [$this->tipeFilter]);
            });
        }

        // Filter by date range
        if (!empty($this->tanggalStart)) {
            $query->whereHas('barangMasukHeader', function ($q) {
                $q->whereDate('tanggal', '>=', $this->tanggalStart);
            });
        }
        if (!empty($this->tanggalEnd)) {
            $query->whereHas('barangMasukHeader', function ($q) {
                $q->whereDate('tanggal', '<=', $this->tanggalEnd);
            });
        }

        // Filter by lokasi simpan
        if (!empty($this->lokasiFilter)) {
            $query->whereRaw('lokasi_simpan = ?', [$this->lokasiFilter]);
        }

        // Fetch detail records joined with header to sort by date
        $history = $query->join('trx_barang_masuk_header', function ($join) {
                $join->on('trx_barang_masuk_detail.barang_masuk_header_id', '=', 'trx_barang_masuk_header.id');
            })
            ->orderByRaw('trx_barang_masuk_header.tanggal desc')
            ->orderByRaw('trx_barang_masuk_detail.id desc')
            ->select('trx_barang_masuk_detail.*')
            ->paginate(15);

        $selectedProduct = $this->selectedProductId ? Produk::find($this->selectedProductId) : null;

        return view('livewire.pembelian.riwayat', [
            'history' => $history,
            'selectedProduct' => $selectedProduct,
        ]);
    }
}

