<?php

namespace App\Livewire\Pelanggan;

use App\Traits\HasAlerts;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\BarangKeluarDetail;
use App\Models\Pelanggan;
use App\Models\Lokasi;

#[Layout('layouts.app')]
class Riwayat extends Component
{
    use HasAlerts;
    use WithPagination;

    // Filter states
    public $search = '';
    public ?int $selectedPelangganId = null;
    public $pelangganSearch = '';
    public $tipeFilter = '';
    public $tanggalStart = '';
    public $tanggalEnd = '';
    public $lokasiFilter = '';

    // Alert messages
    
    

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedPelangganId' => ['except' => null],
        'tipeFilter' => ['except' => ''],
        'tanggalStart' => ['except' => ''],
        'tanggalEnd' => ['except' => ''],
        'lokasiFilter' => ['except' => ''],
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSelectedPelangganId() { $this->resetPage(); }
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
        $this->reset(['search', 'selectedPelangganId', 'pelangganSearch', 'tipeFilter', 'tanggalStart', 'tanggalEnd', 'lokasiFilter']);
        $this->resetPage();
    }

    #[Computed]
    public function pelanggans()
    {
        if (strlen(trim($this->pelangganSearch)) < 2) {
            return [];
        }

        $term = '%' . trim($this->pelangganSearch) . '%';
        $column = 'nama_pelanggan';
        return Pelanggan::query()
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

    public function selectPelanggan(int $pelangganId): void
    {
        $this->selectedPelangganId = $pelangganId;
        $this->pelangganSearch = '';
        $this->resetPage();
    }

    public function deselectPelanggan(): void
    {
        $this->selectedPelangganId = null;
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

        // Filter by customer (pelanggan)
        if ($this->selectedPelangganId) {
            $colPelangganId = 'pelanggan_id';
            $query->whereHas('barangKeluarHeader', function ($q) use ($colPelangganId) {
                $q->where($colPelangganId, $this->selectedPelangganId);
            });
        }

        // Filter by general search (nomor_nota, keterangan_tambahan, dikeluarkan_oleh, product name/sku)
        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(function ($innerQuery) use ($term) {
                $innerQuery->whereHas('barangKeluarHeader', function ($q) use ($term) {
                    $q->where('nomor_nota', 'LIKE', $term)
                      ->orWhere('keterangan_tambahan', 'LIKE', $term)
                      ->orWhere('dikeluarkan_oleh', 'LIKE', $term);
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

        $selectedPelanggan = $this->selectedPelangganId ? Pelanggan::find($this->selectedPelangganId) : null;

        return view('livewire.pelanggan.riwayat', [
            'history' => $history,
            'selectedPelanggan' => $selectedPelanggan,
        ]);
    }
}
