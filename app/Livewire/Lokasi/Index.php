<?php

namespace App\Livewire\Lokasi;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Lokasi;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Traits\HasAlerts;
use Exception;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use HasAlerts;

    public $search = '';
    public $sortField = 'kode_lokasi';
    public $sortDirection = 'asc';

    // Form Modal state
    public bool $isOpen = false;
    public bool $isEdit = false;
    public ?int $lokasiId = null;

    // Barcode Modal state
    public bool $isBarcodeOpen = false;
    public string $barcodeLocationCode = '';
    public string $barcodeLocationName = '';

    // Fields
    public $kode_lokasi = '';
    public $nama_lokasi = '';
    public $tipe_lokasi = 'GUDANG';
    public $is_active = true;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected $rules = [
        'kode_lokasi' => 'required|string|max:100',
        'nama_lokasi' => 'required|string|max:200',
        'tipe_lokasi' => 'required|in:GUDANG,TOKO',
        'is_active' => 'required|boolean',
    ];

    public function updatingSearch()
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

    public function openModal()
    {
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->lokasiId = null;
        $this->isEdit = false;
        $this->kode_lokasi = '';
        $this->nama_lokasi = '';
        $this->tipe_lokasi = 'GUDANG';
        $this->is_active = true;
    }

    public function create()
    {
        Gate::authorize('manage master');
        $this->resetForm();
        $this->isEdit = false;
        $this->openModal();
    }

    public function store()
    {
        Gate::authorize('manage master');
        $rules = $this->rules;
        $this->kode_lokasi = strtoupper(trim($this->kode_lokasi));
        
        if ($this->isEdit) {
            $rules['kode_lokasi'] = 'required|string|max:100|unique:mst_lokasi,kode_lokasi,' . $this->lokasiId;
        } else {
            $rules['kode_lokasi'] = 'required|string|max:100|unique:mst_lokasi,kode_lokasi';
        }

        $validatedData = $this->validate($rules);

        if ($this->isEdit) {
            $lokasi = Lokasi::findOrFail($this->lokasiId);
            
            // Prevent changing code of seeded default locations to prevent breaking tests/imports
            if (in_array($lokasi->kode_lokasi, ['GDG-A01', 'TKO-R01']) && $validatedData['kode_lokasi'] !== $lokasi->kode_lokasi) {
                $this->alertError("Kode lokasi default '{$lokasi->kode_lokasi}' tidak boleh diubah.");
                return;
            }

            $lokasi->update($validatedData);
            $this->toastSuccess("Lokasi '{$lokasi->kode_lokasi}' berhasil diperbarui.");
        } else {
            $lokasi = Lokasi::create($validatedData);
            $this->toastSuccess("Lokasi '{$lokasi->kode_lokasi}' berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage master');
        $this->resetForm();
        $lokasi = Lokasi::findOrFail($id);
        $this->lokasiId = $id;
        $this->isEdit = true;
        
        $this->kode_lokasi = $lokasi->kode_lokasi;
        $this->nama_lokasi = $lokasi->nama_lokasi;
        $this->tipe_lokasi = $lokasi->tipe_lokasi;
        $this->is_active = (bool)$lokasi->is_active;

        $this->openModal();
    }

    public function destroy(int $id): void
    {
        Gate::authorize('manage master');
        $lokasi = Lokasi::findOrFail($id);

        // Prevent deletion of seeded default locations
        if (in_array($lokasi->kode_lokasi, ['GDG-A01', 'TKO-R01'])) {
            $this->alertError("Lokasi default '{$lokasi->kode_lokasi}' tidak dapat dihapus.");
            return;
        }

        // Check if there is active stock (qty > 0)
        $hasStock = $lokasi->stokLokasi()->where([['qty', '>', 0]])->exists();
        // Check if there has been any mutation record
        $hasMutation = $lokasi->mutasiAsal()->exists() || $lokasi->mutasiTujuan()->exists();

        if ($hasStock || $hasMutation) {
            $this->alertError("Lokasi '{$lokasi->kode_lokasi}' tidak dapat dihapus karena memiliki riwayat mutasi atau masih berisi stok barang.");
            return;
        }

        $this->alertConfirm(
            action: 'confirmed-delete',
            params: ['id' => $id],
            title: 'Hapus Lokasi?',
            text: "Apakah Anda yakin ingin menghapus lokasi '{$lokasi->kode_lokasi}'? Tindakan ini tidak dapat dibatalkan."
        );
    }

    #[On('confirmed-delete')]
    public function confirmedDelete(array|int|null $params = null, ?int $id = null): void
    {
        Gate::authorize('manage master');
        if (is_array($params)) {
            $id = $params['id'] ?? $id;
        } elseif (is_numeric($params)) {
            $id = (int)$params;
        }

        try {
            if (!$id) {
                throw new Exception("ID lokasi tidak valid.");
            }

            $lokasi = Lokasi::findOrFail($id);

            if (in_array($lokasi->kode_lokasi, ['GDG-A01', 'TKO-R01'])) {
                throw new Exception("Lokasi default '{$lokasi->kode_lokasi}' tidak dapat dihapus.");
            }

            $hasStock = $lokasi->stokLokasi()->where([['qty', '>', 0]])->exists();
            $hasMutation = $lokasi->mutasiAsal()->exists() || $lokasi->mutasiTujuan()->exists();

            if ($hasStock || $hasMutation) {
                throw new Exception("Lokasi '{$lokasi->kode_lokasi}' tidak dapat dihapus karena memiliki riwayat mutasi atau masih berisi stok barang.");
            }

            $lokasi->delete();
            $this->toastSuccess("Lokasi '{$lokasi->kode_lokasi}' berhasil dihapus.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function toggleStatus(int $id): void
    {
        Gate::authorize('manage master');
        $lokasi = Lokasi::findOrFail($id);
        
        // Prevent disabling default locations
        if (in_array($lokasi->kode_lokasi, ['GDG-A01', 'TKO-R01']) && $lokasi->is_active) {
            $this->alertError("Lokasi default '{$lokasi->kode_lokasi}' tidak dapat dinonaktifkan.");
            return;
        }

        $lokasi->is_active = !$lokasi->is_active;
        $lokasi->save();

        $statusStr = $lokasi->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->toastSuccess("Lokasi '{$lokasi->kode_lokasi}' berhasil {$statusStr}.");
    }

    public function showBarcode(int $id): void
    {
        $lokasi = Lokasi::findOrFail($id);
        $this->barcodeLocationCode = $lokasi->kode_lokasi;
        $this->barcodeLocationName = $lokasi->nama_lokasi;
        $this->isBarcodeOpen = true;
    }

    public function closeBarcodeModal(): void
    {
        $this->isBarcodeOpen = false;
        $this->barcodeLocationCode = '';
        $this->barcodeLocationName = '';
    }

    public function downloadBarcode(string $code)
    {
        $lokasi = Lokasi::where('kode_lokasi', $code)->firstOrFail();
        $svg = \App\Helpers\Barcode128::getSvg($code, 80, 2);
        
        return response()->streamDownload(function () use ($svg) {
            echo $svg;
        }, 'barcode-lokasi-' . strtolower($code) . '.svg', [
            'Content-Type' => 'image/svg+xml',
        ]);
    }

    public function render()
    {
        $query = Lokasi::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('kode_lokasi', 'like', '%' . $this->search . '%')
                  ->orWhere('nama_lokasi', 'like', '%' . $this->search . '%')
                  ->orWhere('tipe_lokasi', 'like', '%' . $this->search . '%');
            });
        }

        $locations = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.lokasi.index', [
            'locations' => $locations,
        ]);
    }
}
