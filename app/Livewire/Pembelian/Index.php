<?php

namespace App\Livewire\Pembelian;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BarangMasuk;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use HasAlerts;

    public function mount()
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('view master'))) {
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

    

    public function render()
    {
        if (session()->has('success_message')) {
            $this->toastSuccess(session('success_message'));
        }

        $query = BarangMasuk::with(['details.produk', 'supplier', 'poHeader']);

        // Apply search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nomor_nota', 'like', '%' . $this->search . '%')
                  ->orWhere('keterangan_tambahan', 'like', '%' . $this->search . '%')
                  ->orWhereHas('supplier', function ($sQuery) {
                      $sQuery->where('nama_supplier', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('details.produk', function ($pQuery) {
                      $pQuery->where('nama_barang', 'like', '%' . $this->search . '%')
                             ->orWhere('kode_barang', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('poHeader', function ($poQ) {
                      $poQ->where('nomor_po', 'like', '%' . $this->search . '%')
                          ->orWhere('artikel', 'like', '%' . $this->search . '%');
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
        $incomingTransactions = $query->latest($sortCol1)->latest($sortCol2)->paginate(10);

        return view('livewire.pembelian.index', [
            'transactions' => $incomingTransactions,
        ]);
    }

    /**
     * Satu sumber kebenaran untuk otorisasi hapus transaksi barang masuk.
     * Dipanggil oleh destroy() DAN confirmedDelete(), karena event
     * 'confirmed-delete-barang-masuk' bisa dikirim langsung dari browser.
     */
    private function authorizeDeleteBarangMasuk(): void
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function destroy(int $id): void
    {
        $this->authorizeDeleteBarangMasuk();

        $transaction = BarangMasuk::findOrFail($id);

        $this->alertConfirm(
            action: 'confirmed-delete-barang-masuk',
            params: ['id' => $id],
            title: 'Hapus Transaksi?',
            text: "Apakah Anda yakin ingin menghapus transaksi '{$transaction->nomor_nota}'? Stok produk dan HPP terkait akan dibalikkan secara otomatis.",
            destructive: true
        );
    }

    #[On('confirmed-delete-barang-masuk')]
    public function confirmedDelete(array|int|null $params = null, ?int $id = null): void
    {
        $this->authorizeDeleteBarangMasuk();

        if (is_array($params)) {
            $id = $params['id'] ?? $id;
        } elseif (is_numeric($params)) {
            $id = (int)$params;
        }

        try {
            if (!$id) {
                throw new \Exception("ID transaksi tidak valid.");
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
                \App\Services\StockEngineService::hapusBarangMasuk($id);
            });

            $this->toastSuccess("Transaksi berhasil dihapus dan stok telah dibalikkan.");
            $this->resetPage();
        } catch (\Exception $e) {
            $this->alertError($e->getMessage());
        }
    }
}

