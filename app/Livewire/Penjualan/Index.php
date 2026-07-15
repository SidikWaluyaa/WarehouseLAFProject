<?php

namespace App\Livewire\Penjualan;

use App\Traits\HasAlerts;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BarangKeluar;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use HasAlerts;
    use WithPagination;

    public function mount()
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input keluar') && !$user->can('view master'))) {
            abort(403, 'Unauthorized action.');
        }

        if (session()->has('success_message')) {
            $this->toastSuccess(session('success_message'));
        }
    }

    // Search and Filter states
    public $search = '';
    public $tipeFilter = '';
    public $tanggalStart = '';
    public $tanggalEnd = '';

    // Alerts
    
    

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipeFilter()
    {
        $this->resetPage();
    }

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
        $this->reset(['search', 'tipeFilter', 'tanggalStart', 'tanggalEnd']);
        $this->resetPage();
    }

    public function delete(int $id)
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $transaction = BarangKeluar::findOrFail($id);

            // Guard: check if transaction has already been packed (is_qc_passed = true)
            if ($transaction->is_qc_passed) {
                $this->alertError("Transaksi tidak dapat dihapus karena sudah di-QC dan dikirim.");
                return;
            }

            \App\Services\StockEngineService::hapusBarangKeluar($id);
            $this->toastSuccess("Transaksi barang keluar berhasil dihapus dan stok dikembalikan.");
        } catch (\Exception $e) {
            $this->alertError("Terjadi kesalahan: " . $e->getMessage());
        }
    }

    public function render()
    {
        if (session()->has('success_message')) {
            $this->toastSuccess(session('success_message'));
        }

        $query = BarangKeluar::with(['details.produk', 'pelanggan']);

        // Apply search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nomor_nota', 'like', '%' . $this->search . '%')
                  ->orWhere('keterangan_tambahan', 'like', '%' . $this->search . '%')
                  ->orWhereHas('pelanggan', function ($cQuery) {
                      $cQuery->where('nama_pelanggan', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('details.produk', function ($pQuery) {
                      $pQuery->where('nama_barang', 'like', '%' . $this->search . '%')
                             ->orWhere('kode_barang', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Apply tipe filter
        if (!empty($this->tipeFilter)) {
            $column = 'tipe_nota';
            $query->where($column, $this->tipeFilter);
        }

        // Apply date range filter
        if (!empty($this->tanggalStart)) {
            $column = 'tanggal';
            $query->whereDate($column, '>=', $this->tanggalStart);
        }
        if (!empty($this->tanggalEnd)) {
            $column = 'tanggal';
            $query->whereDate($column, '<=', $this->tanggalEnd);
        }

        $sortCol1 = 'tanggal';
        $sortCol2 = 'id';
        $outgoingTransactions = $query->orderByDesc($sortCol1)->orderByDesc($sortCol2)->paginate(10);

        return view('livewire.penjualan.index', [
            'transactions' => $outgoingTransactions,
        ]);
    }
}

